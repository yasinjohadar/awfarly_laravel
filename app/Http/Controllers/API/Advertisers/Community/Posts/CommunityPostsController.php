<?php

namespace App\Http\Controllers\API\Advertisers\Community\Posts;

use App\Helpers\Files;
use App\Helpers\Filter;
use App\Helpers\Categories\CategoriesFilter;
use App\Helpers\Geography\Geography;
use App\Helpers\Notifications;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Advertisers\Community\Posts\CommunityPostsResource;
use App\Http\Resources\Advertisers\Community\Posts\Reports\ReportedPostsResource;
use App\Models\Posts\Post;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\Categories\AdvertiserCategories;
use App\Models\Users\Customers\CustomerUser;
use FFMpeg\Coordinate\AspectRatio;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\FFProbe;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\Audio\Aac;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Exception;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\Image\Image;

class CommunityPostsController extends Controller
{
    /**
     * @return Application|ResponseFactory|Response
     */
    public function getAllPosts(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('posts.pagination.limit', 10);

        //get hidden advertisers
        $hidden_advertisers = Auth::guard('advertiser-api')->user()
            ->hiddenAdvertisers()
            ->pluck('advertiser_id')
            ->toArray();

        $blocked_advertisers = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $blocks = array_unique([...$blockers, ...$blocked_advertisers, ...$hidden_advertisers]);
        //get followed advertisers
        $followed_advertisers = Auth::guard('advertiser-api')->user()
            ->followed()
            ->where('followed_type', AdvertiserUser::class)
            ->where('status', 'approved')
            ->pluck('followed_id');

        $data = $request->only([
            'countryCode',
            'governorateId',
            'cityId',
            'categoryId',
            'isGetAllCategories'
        ]);

        $this->apiValidate($data, [
            'countryCode' => 'nullable|string|exists:countries,code',
            'governorateId' => 'nullable|string|exists:governorates,id',
            'cityId' => 'nullable|string|exists:cities,id',
            'categoryId' => 'nullable|string|exists:categories,id',
            'isGetAllCategories' => ['nullable'],
        ]);

        //get posts and paginate it
        $posts = Post::select('posts.*')
            ->join('advertisers_users', function ($q) use ($followed_advertisers, $blocks) {
                return $q->on('advertisers_users.id', 'posts.user_id')
                    ->where(function ($query) use ($followed_advertisers) {
                        return $query->where('advertisers_users.profile_privacy', 'public')
                            ->orWhere(function ($q) use ($followed_advertisers) {
                                return $q->where('advertisers_users.profile_privacy', 'followers')
                                    ->whereIn('advertisers_users.id', $followed_advertisers);
                            })
                            ->orWhere('advertisers_users.id', Auth::guard('advertiser-api')->id());
                    })
                    ->where('advertisers_users.status', 'active')
                    ->where('posts.user_type', AdvertiserUser::class)
                    ->where('posts.status','approved')
                    ->whereNotIn('advertisers_users.id', $blocks)
                    ->whereNull('posts.advertisement_id');
            })
            ->leftJoin('categories', 'categories.id', 'posts.category_id')
            ->whereNull('posts.advertisement_id');

        //Filter country code
        if (isset($data['countryCode']) && $data['countryCode']) {
            $posts = $posts->where(function ($q) use ($data) {
                return $q->where('advertisers_users.country_code', $data['countryCode']);
            });
        }

        $posts = Geography::applyPostLocationFilter($posts, $data);

        if (!Geography::hasExplicitLocationFilter($data)) {
            $posts = Geography::applyPreferredPostLocationFilter(
                $posts,
                Auth::guard('advertiser-api')->user()
            );
        }


        // Filter categories (expand parents to children; apply interests by default)
        $posts = CategoriesFilter::applyFeedCategoryFilter(
            $posts,
            $data,
            Auth::guard('advertiser-api')->user(),
            'posts.category_id'
        );

        $posts = $posts
            ->orderBy('posts.id', 'desc')
            ->groupBy('posts.id')
            ->paginate($limit);

        //add post view or update it
        foreach ($posts as $post) {
            $view_added = Auth::guard('advertiser-api')->user()
                ->viewedPosts()
                ->where('post_id', $post->id)
                ->first();

            if (!$view_added) {
                $post->views_count += 1;
                $post->save();
                Auth::guard('advertiser-api')->user()
                    ->viewedPosts()
                    ->create([
                        'post_id' => $post->id
                    ]);
            }
        }

        return $this->apiPaginateResponse(CommunityPostsResource::collection($posts));
    }

    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function getPostById($id)
    {
        //get post by id
        $post = Post::where('id', $id)
            ->whereNull('posts.advertisement_id')
            ->first();

        //return error if post wasn't found
        if (!$post) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/posts/posts.wrong-id'));
        }

        if ($post->user->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned1'));
        }

        //add post view or update it
        $view_added = Auth::guard('advertiser-api')->user()
            ->viewedPosts()
            ->where('post_id', $post->id)
            ->first();

        if (!$view_added) {
            $post->views_count += 1;
            $post->save();
            Auth::guard('advertiser-api')->user()
                ->viewedPosts()
                ->create([
                    'post_id' => $post->id
                ]);
        }

        return $this->apiResponse(CommunityPostsResource::make($post));
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function search(Request $request)
    {
        $data = $request->only([
            'page',
            'keyword',
            'countryCode',
            'governorateId',
            'cityId',
            'categoryId',
            'isGetAllCategories'
        ]);

        $this->apiValidate($data, [
            'keyword' => 'nullable|string|min:3',
            'countryCode' => 'nullable|string|exists:countries,code',
            'governorateId' => 'nullable|string|exists:governorates,id',
            'cityId' => 'nullable|string|exists:cities,id',
            'categoryId' => 'nullable|string|exists:categories,id',
            'isGetAllCategories' => ['nullable'],
        ]);

        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('posts.pagination.limit', 10);

        //get hidden advertisers
        $hidden_advertisers = Auth::guard('advertiser-api')->user()
            ->hiddenAdvertisers()
            ->pluck('advertiser_id')
            ->toArray();

        $blocked_advertisers = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $blocks = array_unique([...$blockers, ...$blocked_advertisers, ...$hidden_advertisers]);

        //get followed advertisers
        $followed_advertisers = Auth::guard('advertiser-api')->user()
            ->followed()
            ->where('followed_type', AdvertiserUser::class)
            ->where('status', 'approved')
            ->pluck('followed_id');

        //get posts
        $posts = Post::select([
            'posts.*'
        ])
            ->join('advertisers_users', function ($q) use ($blocks, $followed_advertisers) {
                return $q->on('advertisers_users.id', 'posts.user_id')
                    ->where(function ($query) use ($followed_advertisers) {
                        return $query->where('advertisers_users.profile_privacy', 'public')
                            ->orWhere(function ($q) use ($followed_advertisers) {
                                return $q->where('advertisers_users.profile_privacy', 'followers')
                                    ->whereIn('advertisers_users.id', $followed_advertisers);
                            })
                            ->orWhere('advertisers_users.id', Auth::guard('advertiser-api')->id());
                    })
                    ->where('advertisers_users.status', 'active')
                    ->where('posts.user_type', AdvertiserUser::class)
                    ->whereNotIn('advertisers_users.id', $blocks);
            })
            ->where('posts.status','approved')
            ->whereNull('posts.advertisement_id')
            ->leftJoin('categories', 'categories.id', 'posts.category_id');

        //filter keyword
        if (isset($data['keyword']) && !!trim($data['keyword'])) {
            $data['keyword'] = trim($data['keyword']);
            $posts = $posts->where(function ($q) use ($data) {
                return $q->where('posts.content', 'like', "%{$data['keyword']}%")
                    ->orWhere('advertisers_users.username', 'like', "%{$data['keyword']}%")
                    ->orWhere('advertisers_users.name', 'like', "%{$data['keyword']}%");
            });
        }

        //Filter country code
        if (isset($data['countryCode']) && $data['countryCode']) {
            $posts = $posts->where(function ($q) use ($data) {
                return $q->where('advertisers_users.country_code', $data['countryCode']);
            });
        }

        $posts = Geography::applyPostLocationFilter($posts, $data);

        if (!Geography::hasExplicitLocationFilter($data)) {
            $posts = Geography::applyPreferredPostLocationFilter(
                $posts,
                Auth::guard('advertiser-api')->user()
            );
        }


        // Filter categories (expand parents to children; apply interests by default)
        $posts = CategoriesFilter::applyFeedCategoryFilter(
            $posts,
            $data,
            Auth::guard('advertiser-api')->user(),
            'posts.category_id'
        );

        //get the posts
        $posts = $posts
            ->orderBy('posts.created_at')
            ->groupBy('posts.id')
            ->paginate($limit);

        //add post view or update it
        foreach ($posts as $post) {
            $view_added = Auth::guard('advertiser-api')->user()
                ->viewedPosts()
                ->where('post_id', $post->id)
                ->first();

            if (!$view_added) {
                $post->views_count += 1;
                $post->save();
                Auth::guard('advertiser-api')->user()
                    ->viewedPosts()
                    ->create([
                        'post_id' => $post->id
                    ]);
            }
        }


        return $this->apiPaginateResponse(CommunityPostsResource::collection($posts));
    }

    /**
     * get user posts
     * @return Application|ResponseFactory|Response
     */
    public function getUserPosts(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('posts.pagination.limit', 10);

        //get user posts
        $posts = Auth::guard('advertiser-api')->user()
            ->posts()
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        //add post view or update it
        foreach ($posts as $post) {
            $view_added = Auth::guard('advertiser-api')->user()
                ->viewedPosts()
                ->where('post_id', $post->id)
                ->first();

            if (!$view_added) {
                $post->views_count += 1;
                $post->save();
                Auth::guard('advertiser-api')->user()
                    ->viewedPosts()
                    ->create([
                        'post_id' => $post->id
                    ]);
            }
        }

        return $this->apiPaginateResponse(CommunityPostsResource::collection($posts));
    }

    /**
     * get user posts by username
     * @param $user_id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getPostsByUserId($user_id, Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('posts.pagination.limit', 10);

        //get advertiser that has this username
        $user = AdvertiserUser::where('id', $user_id)
            ->where('status', 'active')
            ->first();

        /*//get customer with this username
        $customer = CustomerUser::where('id', $user_id)
            ->where('status', 'active')
            ->first();*/


        //return error if user wasn't found
        if (!$user) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/posts/posts.wrong-userid'));
        }

        $block = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->where('blocked_id', $user_id)
            ->exists();

        $blocked = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->where('blocker_id', $user_id)
            ->exists();

        if ($block || $blocked) {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.unavailable'));
        }
        //get followed advertisers
        $followed_advertisers = Auth::guard('advertiser-api')->user()
            ->followed()
            ->where('followed_type', AdvertiserUser::class)
            ->where('status', 'approved')
            ->pluck('followed_id');

        //get posts
        $posts = $user->posts()
            ->select('posts.*')
            ->join('advertisers_users', function ($q) use ($followed_advertisers) {
                return $q->on('advertisers_users.id', 'posts.user_id')
                    ->where(function ($query) use ($followed_advertisers) {
                        return $query->where('advertisers_users.profile_privacy', 'public')
                            ->orWhere(function ($q) use ($followed_advertisers) {
                                return $q->where('advertisers_users.profile_privacy', 'followers')
                                    ->whereIn('advertisers_users.id', $followed_advertisers);
                            })
                            ->orWhere('advertisers_users.id', Auth::guard('advertiser-api')->id());
                    })
                    ->where('advertisers_users.status', 'active');
            })
            ->whereNull('posts.advertisement_id')
            ->orderBy('posts.created_at', 'desc')
            ->paginate($limit);

        //add post view or update it
        foreach ($posts as $post) {
            if (($post->user_id != Auth::guard('advertiser-api')->id()) && ($post->user_type !== AdvertiserUser::class)) {

                $view_added = Auth::guard('advertiser-api')->user()
                    ->viewedPosts()
                    ->where('post_id', $post->id)
                    ->first();

                if (!$view_added) {
                    $post->views_count += 1;
                    $post->save();
                    Auth::guard('advertiser-api')->user()
                        ->viewedPosts()
                        ->create([
                            'post_id' => $post->id
                        ]);
                }
            }
        }

        return $this->apiPaginateResponse(CommunityPostsResource::collection($posts));
    }

    /**
     * get user posts by username
     * @param $user_id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getPostsInteractedByCustomer($user_id, Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('posts.pagination.limit', 10);

        //get customer with this user id
        $customer = CustomerUser::where('id', $user_id)
            ->where('status', 'active')
            ->first();

        //return error if user wasn't found
        if (!$customer) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/posts/posts.wrong-userid'));
        }

        if ($customer->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        $blocked_customers = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', CustomerUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers_customers = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', CustomerUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $customers_blocks = array_unique([...$blockers_customers, ...$blocked_customers]);
        $customers_followed = Auth::guard('advertiser-api')->user()
            ->followed()
            ->where('followed_type', CustomerUser::class)
            ->where('status', 'approved')
            ->pluck('followed_id');

        $liked_posts = $customer->postsLikes()
            ->join('customers_users', function ($q) use ($customers_followed, $customers_blocks) {
                return $q->on('customers_users.id', 'posts_likes.user_id')
                    ->where(function ($query) use ($customers_followed) {
                        return $query->where('customers_users.profile_privacy', 'public')
                            ->orWhere(function ($q) use ($customers_followed) {
                                return $q->where('customers_users.profile_privacy', 'followers')
                                    ->whereIn('customers_users.id', $customers_followed);
                            });
                    })
                    ->whereNotIn('customers_users.id', $customers_blocks)
                    ->where('user_type', CustomerUser::class);
            })
            ->pluck('post_id')
            ->toArray();


        $commented_posts = $customer->postsComments()
            ->join('customers_users', function ($q) use ($customers_followed, $customers_blocks) {
                return $q->on('customers_users.id', 'posts_comments.user_id')
                    ->where(function ($query) use ($customers_followed) {
                        return $query->where('customers_users.profile_privacy', 'public')
                            ->orWhere(function ($q) use ($customers_followed) {
                                return $q->where('customers_users.profile_privacy', 'followers')
                                    ->whereIn('customers_users.id', $customers_followed);
                            });
                    })
                    ->whereNotIn('customers_users.id', $customers_blocks)
                    ->where('user_type', CustomerUser::class);
            })
            ->pluck('post_id')
            ->toArray();

        $posts_ids = array_unique(array_merge($liked_posts, $commented_posts));

        //get hidden advertisers
        $hidden_advertisers = Auth::guard('advertiser-api')->user()
            ->hiddenAdvertisers()
            ->pluck('advertiser_id')
            ->toArray();
        $blocked_advertisers = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers_advertisers = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $advertisers_blocks = array_unique([...$blockers_advertisers, ...$blocked_advertisers, ...$hidden_advertisers]);
        //get followed advertisers
        $followed_advertisers = Auth::guard('advertiser-api')->user()
            ->followed()
            ->where('followed_type', AdvertiserUser::class)
            ->where('status', 'approved')
            ->pluck('followed_id');

        //get posts
        $posts = Post::select([
            'posts.*'
        ])
            ->join('advertisers_users', function ($q) use ($advertisers_blocks, $followed_advertisers) {
                return $q->on('advertisers_users.id', 'posts.user_id')
                    ->where(function ($query) use ($followed_advertisers) {
                        return $query->where('advertisers_users.profile_privacy', 'public')
                            ->orWhere(function ($q) use ($followed_advertisers) {
                                return $q->where('advertisers_users.profile_privacy', 'followers')
                                    ->whereIn('advertisers_users.id', $followed_advertisers);
                            })
                            ->orWhere('advertisers_users.id', Auth::guard('advertiser-api')->id());
                    })
                    ->where('user_type', AdvertiserUser::class)
                    ->whereNotIn('user_id', $advertisers_blocks);
            })
            ->whereNull('posts.advertisement_id')
            ->whereIn('posts.id', $posts_ids)

            ->orderBy('posts.created_at', 'desc')
            ->paginate($limit);

        return $this->apiPaginateResponse(CommunityPostsResource::collection($posts));
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function addPost(Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        if (!Auth::guard('advertiser-api')->user()->is_profile_completed) {
            return $this->apiBadRequestResponse(__('api/auth/auth.profile-uncompleted'));
        }
        //Set data
        $data = $request->only([
            'categoryId',
            'content',
            'media',
            'governorateId',
            'cityId',
        ]);

        //Validate course id
        $this->apiValidate($data, array_merge([
            'categoryId' => ['nullable', 'exists:categories,id'],
            'content' => ['nullable', 'string'],
            'media' => ['nullable', 'max:5'],
            'media.*.file' => ['nullable', 'mimes:jpg,jpeg,png,bmp,gif,mp4,mov,ogg,qt,avi,wmv,flv,ts,3gp', 'max:100000'],
            'media.*.startAt' => ['nullable', 'integer'],
            'media.*.endAt' => ['nullable', 'integer', 'gt:media.*.startAt'],
        ], Geography::optionalLocationRules()));

        $advertiser = Auth::guard('advertiser-api')->user();
        $data['governorateId'] = $data['governorateId'] ?? $advertiser->governorate_id;
        $data['cityId'] = $data['cityId'] ?? $advertiser->city_id;

        if (empty($data['governorateId']) || empty($data['cityId'])) {
            return $this->apiBadRequestResponse(__('api/geography/geography.location-required'));
        }

        $locationError = Geography::validateCityBelongsToGovernorate($data);
        if ($locationError) {
            return $this->apiBadRequestResponse($locationError);
        }

        $allowed_posts_count = $advertiser->allowed_posts_count;

        //check whether user is elite or not
        if (Auth::guard('advertiser-api')->user()->is_elite) {
            //get user package
            $package = Auth::guard('advertiser-api')->user()
                ->packages()
                ->where('is_current', true)
                ->where('is_active', true)
                ->where('is_ended', false)
                ->where('ends_at', '>', now())
                ->first();

            //return maximum posts quantity
            $allowed_posts = ($allowed_posts_count >= 0) ? $allowed_posts_count : ($package->package->maximum_posts ?? Settings::Get('user.allowed.posts', 10));
        } else {
            //return maximum posts quantity
            $allowed_posts = ($allowed_posts_count >= 0) ? $allowed_posts_count : Settings::Get('user.allowed.posts', 20);
        }
        //return error of posts equals or more than allowed posts
        if ($allowed_posts == 0) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/posts/posts.exceeded-limit'));
        }

        if (isset($data['categoryId']) && $data['categoryId']) {
            $category_id = $data['categoryId'];
        } else {
            $user_categories = AdvertiserCategories::where('advertiser_id', Auth::guard('advertiser-api')->id())
                ->first();

            $category_id = $user_categories->category_id ?? null;
        }

        DB::beginTransaction();
        try {
            //create post
            $post = Auth::guard('advertiser-api')->user()
                ->posts()
                ->create([
                    'content' => Filter::RemoveHtml($data['content']),
                    'category_id' => $category_id ?? null,
                    'governorate_id' => $data['governorateId'],
                    'city_id' => $data['cityId'],
                    'status'    =>  'pending'
                ]);

            //upload media
            if ($request->hasFile('media.*.file')) {
                foreach ($request->media as $index => $media) {
                    $mime_type = $media['file']->getMimeType();
                    $file = $media['file'];
                    if (strstr($mime_type, "video/")) {
                        $filename = pathinfo($file->hashName(), PATHINFO_FILENAME) . ".mp4";
                        $ffmpeg = FFMpeg::create();
                        $video = $ffmpeg->open($file);
                        $video_stream = $video->getStreams()
                            ->videos()
                            ->first()
                            ->getDimensions();
                        $file_width = $video_stream->getWidth();
                        $file_height = $video_stream->getHeight();
                        if ((isset($media['startAt']) && $media['startAt'] != null) && (isset($media['endAt']) && $media['endAt'] != null)) {
                            $start_at = $media['startAt'];
                            $duration = $media['endAt'] - $start_at;
                            $video->filters()->clip(TimeCode::fromSeconds($start_at), TimeCode::fromSeconds($duration));
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $file = storage_path("app/uploads/$filename");
                        } elseif ($file_height > 480 && $file_height < $file_width) {
                            $video->filters()->resize(new Dimension(640, 480), ResizeFilter::RESIZEMODE_SCALE_WIDTH);
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $video_dimensions = $video->getFFProbe()
                                ->streams(storage_path("app/uploads/$filename"))
                                ->videos()
                                ->first()
                                ->getDimensions();
                            $file_width = $video_dimensions->getWidth();
                            $file_height = $video_dimensions->getHeight();
                            $file = storage_path("app/uploads/$filename");
                        } elseif ($file_width > 480 && $file_height > $file_width) {
                            $video->filters()->resize(new Dimension(480, 640), ResizeFilter::RESIZEMODE_SCALE_HEIGHT);
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $video_dimensions = $video->getFFProbe()
                                ->streams(storage_path("app/uploads/$filename"))
                                ->videos()
                                ->first()
                                ->getDimensions();
                            $file_width = $video_dimensions->getWidth();
                            $file_height = $video_dimensions->getHeight();
                            $file = storage_path("app/uploads/$filename");
                        } else {
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $video_dimensions = $video->getFFProbe()
                                ->streams(storage_path("app/uploads/$filename"))
                                ->videos()
                                ->first()
                                ->getDimensions();
                            $file_width = $video_dimensions->getWidth();
                            $file_height = $video_dimensions->getHeight();
                            $file = storage_path("app/uploads/$filename");
                        }
                    } else if (strstr($mime_type, 'image/')) {
                        $file_width = Image::load($file)->getWidth();
                        $file_height = Image::load($file)->getHeight();
                        $temp_image = Files::uploadTempImage($request, 'uploads/media', "media.{$index}.file");
                        $file = storage_path("app/$temp_image");
                    } else {
                        $file_width = null;
                        $file_height = null;
                    }
                    $post->addMedia($file)
                        ->withCustomProperties(['width' => $file_width, 'height' => $file_height,])
                        ->toMediaCollection('posts');
                }
            }

            Auth::guard('advertiser-api')->user()
                ->update([
                    'allowed_posts_count' => $allowed_posts - 1,
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/posts/posts.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'type' => 'created',
            'message' => __('api/advertisers/community/posts/posts.post-added'),
            'data' => CommunityPostsResource::make($post),
        ]);
    }

    /**
     * delete post
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function deletePost($id)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //get post
        $post = Auth::guard('advertiser-api')->user()
            ->posts()
            ->where('id', $id)
            ->first();

        //return error if the user doesn't have permission to delete this post
        if (!$post) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/posts/posts.no-permission'));
        }

        //check user packages
        $package = Auth::guard('advertiser-api')->user()
            ->packages()
            ->whereHas('package')
            ->where('is_current', true)
            ->where('is_active', true)
            ->where('is_ended', false)
            ->where('ends_at', '>', now())
            ->first();

        $max_posts_count = (int)($package ? $package->package->maximum_posts : Settings::Get('user.allowed.posts', 10));
        $current_posts_count = Auth::guard('advertiser-api')->user()->allowed_posts_count;
        DB::beginTransaction();
        try {
            DB::table('notifications')
                ->whereJsonContains('data->customProperties->postId', $post->id)
                ->delete();

            //soft-delete the post
            $post->delete();

            Auth::guard('advertiser-api')->user()
                ->update([
                    'allowed_posts_count' => ((int)$current_posts_count + 1 <= $max_posts_count) ? $current_posts_count + 1 : $max_posts_count,
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/posts/posts.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/community/posts/posts.post-deleted'),
        ]);
    }

    /**
     * edit post
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function editPost(Request $request, $id)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $post = Auth::guard('advertiser-api')->user()
            ->posts()
            ->where('id', $id)
            ->first();

        //return error if the user doesn't have permission to delete this post
        if (!$post) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/posts/posts.no-permission'));
        }
        if ($request->has('isDeleteAllOldMedia') && $request->get('isDeleteAllOldMedia')) {
            $add_max_count = 5;
        } //check whether there is files to be deleted or not
        else if ($request->has('deleteMedia') && $request->get('deleteMedia') != null) {
            //get media will be deleted
            $media_count = $post->getMedia('posts')
                ->whereIn('id', $request->get('deleteMedia'))
                ->count();

            //check if the media to be deleted count belongs to this post and increase max count
            $add_max_count = (sizeof($request->get('deleteMedia')) == $media_count) ? $media_count : 0;
        } else {
            $add_max_count = 0;
        }

        //check whether the edit have new photos or not to set the maximum allowed images.
        if ($request->has('media')) {
            $max_count = $add_max_count + 5 - $post->getMedia('posts')->count();
        } else {
            $max_count = 0;
        }

        //Set data
        $data = $request->only([
            'content',
            'media',
            'deleteMedia',
            'categoryId',
            'isDeleteAllOldMedia',
            'governorateId',
            'cityId',
        ]);

        //Validate course id
        $this->apiValidate($data, array_merge([
            'content' => ['nullable', 'string'],
            'media' => ['nullable', "max:$max_count"],
            'media.*.file' => ['nullable', 'mimes:jpg,jpeg,png,bmp,gif,mp4,mov,ogg,qt,avi,wmv,flv,ts,3gp', 'max:100000'],
            'media.*.startAt' => ['nullable', 'integer'],
            'media.*.endAt' => ['nullable', 'integer', 'gt:media.*.startAt'],
            'deleteMedia' => ['nullable', 'array'],
            'deleteMedia.*' => ['nullable', 'exists:media,id'],
            'categoryId' => ['nullable', 'exists:categories,id'],
            'isDeleteAllOldMedia' => ['nullable', 'boolean'],
        ], Geography::optionalLocationRules()));

        if (!empty($data['governorateId']) || !empty($data['cityId'])) {
            $data['governorateId'] = $data['governorateId'] ?? $post->governorate_id;
            $data['cityId'] = $data['cityId'] ?? $post->city_id;

            if (empty($data['governorateId']) || empty($data['cityId'])) {
                return $this->apiBadRequestResponse(__('api/geography/geography.location-required'));
            }

            $locationError = Geography::validateCityBelongsToGovernorate($data);
            if ($locationError) {
                return $this->apiBadRequestResponse($locationError);
            }
        }

        if (isset($data['categoryId']) && $data['categoryId']) {
            $category_id = $data['categoryId'];
        } else {
            $user_categories = AdvertiserCategories::where('advertiser_id', Auth::guard('advertiser-api')->id())
                ->first();

            $category_id = $user_categories->category_id ?? null;
        }

        DB::beginTransaction();
        try {
            if ($request->has('isDeleteAllOldMedia') && $request->get('isDeleteAllOldMedia') != null) {
                $post->clearMediaCollection('posts');
            }
            //delete old media
            if ($request->has('deleteMedia') && $request->get('deleteMedia') != null) {
                foreach ($request->get('deleteMedia') as $media) {
                    $post->getMedia('posts')
                        ->where('id', $media)
                        ->first()
                        ->delete();
                }
            }
            //upload media
            if ($request->hasFile('media.*.file')) {
                foreach ($request->media as $index => $media) {
                    $mime_type = $media['file']->getMimeType();
                    $file = $media['file'];
                    if (strstr($mime_type, "video/")) {
                        $filename = pathinfo($file->hashName(), PATHINFO_FILENAME) . ".mp4";
                        $ffmpeg = FFMpeg::create();
                        $video = $ffmpeg->open($file);
                        $video_stream = $video->getStreams()
                            ->videos()
                            ->first()
                            ->getDimensions();
                        $file_width = $video_stream->getWidth();
                        $file_height = $video_stream->getHeight();
                        if ((isset($media['startAt']) && $media['startAt'] != null) && (isset($media['endAt']) && $media['endAt'] != null)) {
                            $start_at = $media['startAt'];
                            $duration = $media['endAt'] - $start_at;
                            $video->filters()->clip(TimeCode::fromSeconds($start_at), TimeCode::fromSeconds($duration));
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $file = storage_path("app/uploads/$filename");
                        } elseif ($file_height > 480 && $file_height < $file_width) {
                            $video->filters()->resize(new Dimension(640, 480), ResizeFilter::RESIZEMODE_SCALE_WIDTH);
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $video_dimensions = $video->getFFProbe()
                                ->streams(storage_path("app/uploads/$filename"))
                                ->videos()
                                ->first()
                                ->getDimensions();
                            $file_width = $video_dimensions->getWidth();
                            $file_height = $video_dimensions->getHeight();
                            $file = storage_path("app/uploads/$filename");
                        } elseif ($file_width > 480 && $file_height > $file_width) {
                            $video->filters()->resize(new Dimension(480, 640), ResizeFilter::RESIZEMODE_SCALE_HEIGHT);
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $video_dimensions = $video->getFFProbe()
                                ->streams(storage_path("app/uploads/$filename"))
                                ->videos()
                                ->first()
                                ->getDimensions();
                            $file_width = $video_dimensions->getWidth();
                            $file_height = $video_dimensions->getHeight();
                            $file = storage_path("app/uploads/$filename");
                        } else {
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $video_dimensions = $video->getFFProbe()
                                ->streams(storage_path("app/uploads/$filename"))
                                ->videos()
                                ->first()
                                ->getDimensions();
                            $file_width = $video_dimensions->getWidth();
                            $file_height = $video_dimensions->getHeight();
                            $file = storage_path("app/uploads/$filename");
                        }
                    } else if (strstr($mime_type, 'image/')) {
                        $file_width = Image::load($file)->getWidth();
                        $file_height = Image::load($file)->getHeight();
                        $temp_image = Files::uploadTempImage($request, 'uploads/media', "media.{$index}.file");
                        $file = storage_path("app/$temp_image");
                    } else {
                        $file_width = null;
                        $file_height = null;
                    }
                    $post->addMedia($file)
                        ->withCustomProperties(['width' => $file_width, 'height' => $file_height])
                        ->toMediaCollection('posts');
                }
            }

            //update the post
            $updatePayload = [
                'content' => $data['content'] ? Filter::RemoveHtml($data['content']) : $post->content,
                'category_id' => $category_id,
                'status' => 'pending',
            ];

            if (!empty($data['governorateId']) && !empty($data['cityId'])) {
                $updatePayload['governorate_id'] = $data['governorateId'];
                $updatePayload['city_id'] = $data['cityId'];
            }

            $post->update($updatePayload);
        } catch (Exception $e) {
            //roll back
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/posts/posts.something-wrong'));
        }
        //commit
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/community/posts/posts.post-edited'),
            'data' => CommunityPostsResource::make($post),
        ]);
    }

    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function toggleLikePost($id, Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //get post by id
        $post = Post::where('id', $id)
            ->first();

        //return error if post wasn't found
        if (!$post) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/posts/posts.wrong-id'));
        }
        if (!$post->advertisement_id) {
            if ($post->user->status === 'banned') {
                return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
            }
        }
        DB::beginTransaction();
        try {
            $like = Auth::guard('advertiser-api')->user()
                ->postsLikes()
                ->where('post_id', $id)
                ->first();

            if ($request->has('isLiked')) {
                if ($request->get('isLiked')) {
                    if (!$like) {
                        $like = Auth::guard('advertiser-api')->user()
                            ->postsLikes()
                            ->create([
                                'post_id' => $id
                            ]);
                        $post->likes_count += 1;
                    }
                    $isLiked = true;
                    $type = __('api/advertisers/community/posts/posts.liked');
                } else {
                    if ($like) {
                        $like->delete();
                        $post->likes_count -= 1;
                    }

                    $isLiked = false;
                    $type = __('api/advertisers/community/posts/posts.disliked');
                }
            } else {
                if ($like) {
                    $like->delete();
                    $post->likes_count -= 1;
                    $isLiked = false;
                    $type = __('api/advertisers/community/posts/posts.disliked');
                } else {
                    $like = Auth::guard('advertiser-api')->user()
                        ->postsLikes()
                        ->create([
                            'post_id' => $id
                        ]);
                    $post->likes_count += 1;

                    $isLiked = true;
                    $type = __('api/advertisers/community/posts/posts.liked');
                }
            }
            $post->save();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/posts/posts.something-wrong'));
        }
        DB::commit();

        //send notification
        if ($isLiked && !$post->advertisement_id && !($post->user->user_type === 'advertiser' && $post->user_id == Auth::guard('advertiser-api')->id())) {
            $customProperties = [
                'postId' => $post->id,
                'userId' => Auth::guard('advertiser-api')->id(),
                'userType' => 'advertiser',
            ];

            $post_user = $post->user;

            $post_user->notifications()
                ->whereJsonContains('data->customProperties->postId', $post->id)
                ->whereJsonContains('data->action', 'like')
                ->delete();

            Notifications::sendForCommunity($post->user, 'posts', 'posts.like', 'like', $customProperties);
        }
        return $this->apiResponse([
            'message' => __('api/advertisers/community/posts/posts.like-toggle', ['toggle' => $type]),
            'data' => [
                'isLiked' => $isLiked,
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function reportPost(Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $user = Auth::guard('advertiser-api')->user();
        //set data
        $data = $request->only([
            'postId',
            'type',
            'reason',
        ]);

        //validate data
        $this->apiValidate($data, [
            'postId' => ['required', 'exists:posts,id'],
            'type' => ['nullable', 'in:Sexually Inappropriate,Abusive Content,Misleading or Scam,Offensive,Violence,Prohibited Content,Spam,False News,Other'],
            'reason' => ['nullable'],
        ]);

        //get post
        $post = Post::where('id', $data['postId'])
            ->first();

        //return error if post wasn't found
        if (!$post) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/posts/posts.wrong-id'));
        }

        if ($post->user->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }
        //check if user already reported
        $report = $post->reports()
            ->where('user_type', AdvertiserUser::class)
            ->where('user_id', $user->id)
            ->first();

        try {
            //create report
            if (!$report) {
                $report = $post->reports()
                    ->create([
                        'user_type' => AdvertiserUser::class,
                        'user_id' => $user->id,
                        'reason' => $data['reason'] ? Filter::RemoveHtml($data['reason']) : null,
                        'type' => $data['type'] ?? null,
                    ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/posts/posts.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/community/posts/posts.reports.report-added'),
            'data' => ReportedPostsResource::make($report),
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function editPostReport(Request $request, $id)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $user = Auth::guard('advertiser-api')->user();

        //set data
        $data = $request->only([
            'reason',
        ]);

        //validate data
        $this->apiValidate($data, [
            'reason' => ['nullable'],
        ]);

        //check if user already reported
        $report = $user->reports()
            ->where('id', $id)
            ->first();

        //return error if post wasn't found
        if (!$report) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/posts/posts.reports.no-report'));
        }

        try {
            //create report
            $report->update([
                'reason' => $data['reason'] ? Filter::RemoveHtml($data['reason']) : null,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/posts/posts.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/community/posts/posts.reports.report-edited'),
            'data' => ReportedPostsResource::make($report),
        ]);
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getReportedPosts(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('reported.posts.pagination.limit', 10);

        $user = Auth::guard('advertiser-api')->user();

        $reports = $user->reports()
            ->where('reported_type', Post::class)
            ->paginate($limit);

        return $this->apiResponse(ReportedPostsResource::collection($reports));
    }
}
