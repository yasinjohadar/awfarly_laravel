<?php

namespace App\Http\Controllers\API\Customers\Community\Posts;

use App\Helpers\Filter;
use App\Helpers\Categories\CategoriesFilter;
use App\Helpers\Geography\Geography;
use App\Helpers\Interests\InterestsFilter;
use App\Helpers\Notifications;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Customers\Community\Posts\CommunityPostsResource;
use App\Http\Resources\Customers\Community\Posts\Reports\ReportedPostsResource;
use App\Models\Posts\Post;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $hidden_advertisers = Auth::guard('customer-api')->user()
            ->hiddenAdvertisers()
            ->pluck('advertiser_id')
            ->toArray();
        $blocked_advertisers = Auth::guard('customer-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers = Auth::guard('customer-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $blocks = array_unique([...$blockers, ...$blocked_advertisers, ...$hidden_advertisers]);

        //get followed advertisers
        $followed_advertisers = Auth::guard('customer-api')->user()
            ->followed()
            ->where('followed_type', AdvertiserUser::class)
            ->where('status', 'approved')
            ->pluck('followed_id');

        $data = $request->only([
            'countryCode',
            'governorateId',
            'cityId',
            'categoryId',
            'isGetAllCategories',
            'interestId',
            'isGetAllInterests'
        ]);

        $this->apiValidate($data, [
            'countryCode' => 'nullable|string|exists:countries,code',
            'governorateId' => 'nullable|string|exists:governorates,id',
            'cityId' => 'nullable|string|exists:cities,id',
            'categoryId' => 'nullable|string|exists:categories,id',
            'isGetAllCategories' => ['nullable'],
            'interestId' => 'nullable|string|exists:interests,id',
            'isGetAllInterests' => ['nullable'],
        ]);

        //get posts and paginate it
        $posts = Post::select('posts.*')
            ->join('advertisers_users', function ($q) use ($blocks, $followed_advertisers) {
                return $q->on('advertisers_users.id', 'posts.user_id')
                    ->where(function ($query) use ($followed_advertisers) {
                        return $query->where('advertisers_users.profile_privacy', 'public')
                            ->orWhere(function ($q) use ($followed_advertisers) {
                                return $q->where('advertisers_users.profile_privacy', 'followers')
                                    ->whereIn('advertisers_users.id', $followed_advertisers);
                            });
                    })
                    ->where('advertisers_users.status', 'active')
                    ->where('posts.user_type', AdvertiserUser::class)
                    ->whereNotIn('advertisers_users.id', $blocks)
                    ->whereNull('posts.advertisement_id');
            })
            ->where('posts.status','approved')
            ->whereNull('posts.advertisement_id')
            ->leftJoin('categories', 'categories.id', 'posts.category_id');

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
                Auth::guard('customer-api')->user()
            );
        }


        // Filter categories (expand parents to children; apply interests by default)
        $posts = CategoriesFilter::applyFeedCategoryFilter(
            $posts,
            $data,
            Auth::guard('customer-api')->user(),
            'posts.category_id'
        );

        // Filter by interests (matches advertisers whose own selected interests overlap with the customer's)
        $posts = InterestsFilter::applyFeedInterestFilter(
            $posts,
            $data,
            Auth::guard('customer-api')->user(),
            'advertisers_users'
        );

        $posts = $posts
            ->orderBy('posts.id', 'desc')
            ->groupBy('posts.id')
            ->paginate($limit);

        //add post view or update it
        foreach ($posts as $post) {
            $view_added = Auth::guard('customer-api')->user()
                ->viewedPosts()
                ->where('post_id', $post->id)
                ->first();

            if (!$view_added) {
                $post->views_count += 1;
                $post->save();
                Auth::guard('customer-api')->user()
                    ->viewedPosts()
                    ->create([
                        'post_id' => $post->id
                    ]);
            }
        }
        return $this->apiPaginateResponse(CommunityPostsResource::collection($posts));
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
            'isGetAllCategories',
            'interestId',
            'isGetAllInterests'
        ]);

        $this->apiValidate($data, [
            'keyword' => 'nullable|string|min:3',
            'countryCode' => 'nullable|string|exists:countries,code',
            'governorateId' => 'nullable|string|exists:governorates,id',
            'cityId' => 'nullable|string|exists:cities,id',
            'categoryId' => 'nullable|string|exists:categories,id',
            'isGetAllCategories' => ['nullable'],
            'interestId' => 'nullable|string|exists:interests,id',
            'isGetAllInterests' => ['nullable'],
        ]);

        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('posts.pagination.limit', 10);

        //get hidden advertisers
        $hidden_advertisers = Auth::guard('customer-api')->user()
            ->hiddenAdvertisers()
            ->pluck('advertiser_id')
            ->toArray();
        $blocked_advertisers = Auth::guard('customer-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers = Auth::guard('customer-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $blocks = array_unique([...$blockers, ...$blocked_advertisers, ...$hidden_advertisers]);

        //get followed advertisers
        $followed_advertisers = Auth::guard('customer-api')->user()
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
                            });
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
                Auth::guard('customer-api')->user()
            );
        }


        // Filter categories (expand parents to children; apply interests by default)
        $posts = CategoriesFilter::applyFeedCategoryFilter(
            $posts,
            $data,
            Auth::guard('customer-api')->user(),
            'posts.category_id'
        );

        // Filter by interests (matches advertisers whose own selected interests overlap with the customer's)
        $posts = InterestsFilter::applyFeedInterestFilter(
            $posts,
            $data,
            Auth::guard('customer-api')->user(),
            'advertisers_users'
        );

        //get the posts
        $posts = $posts
            ->orderBy('posts.id', 'desc')
            ->groupBy('posts.id')
            ->paginate($limit);

        //add post view or update it
        foreach ($posts as $post) {
            $view_added = Auth::guard('customer-api')->user()
                ->viewedPosts()
                ->where('post_id', $post->id)
                ->first();

            if (!$view_added) {
                $post->views_count += 1;
                $post->save();
                Auth::guard('customer-api')->user()
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
            ->first();

        //return error if post wasn't found
        if (!$post) {
            return $this->apiBadRequestResponse(__('api/customers/community/posts/posts.wrong-id'));
        }

        if ($post->user->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        //add post view or update it
        $view_added = Auth::guard('customer-api')->user()
            ->viewedPosts()
            ->where('post_id', $post->id)
            ->first();

        if (!$view_added) {
            $post->views_count += 1;
            $post->save();

            Auth::guard('customer-api')->user()
                ->viewedPosts()
                ->create([
                    'post_id' => $post->id
                ]);
        }

        return $this->apiResponse(CommunityPostsResource::make($post));
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

        //get advertiser that has this user id
        $user = AdvertiserUser::where('id', $user_id)
            ->where('status', 'active')
            ->first();

        //return error if user wasn't found
        if (!$user) {
            return $this->apiBadRequestResponse(__('api/customers/community/posts/posts.wrong-userid'));
        }
        $block = Auth::guard('customer-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->where('blocked_id', $user_id)
            ->exists();

        $blocked = Auth::guard('customer-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->where('blocker_id', $user_id)
            ->exists();

        if ($block || $blocked) {
            return $this->apiBadRequestResponse(__('api/customers/advertisers/advertisers.unavailable'));
        }

        //return error if user wasn't found
        if ($user->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        //get followed advertisers
        $followed_advertisers = Auth::guard('customer-api')->user()
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
                            });
                    });
            })
            ->whereNull('posts.advertisement_id')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        //add post view or update it
        foreach ($posts as $post) {
            $view_added = Auth::guard('customer-api')->user()
                ->viewedPosts()
                ->where('post_id', $post->id)
                ->first();

            if (!$view_added) {
                $post->views_count += 1;
                $post->save();
                Auth::guard('customer-api')->user()
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
            return $this->apiBadRequestResponse(__('api/customers/community/posts/posts.wrong-userid'));
        }
        $blocked_customers = Auth::guard('customer-api')->user()
            ->block()
            ->where('blocked_type', CustomerUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers_customers = Auth::guard('customer-api')->user()
            ->blocked()
            ->where('blocker_type', CustomerUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $customers_blocks = array_unique([...$blockers_customers, ...$blocked_customers]);

        $customers_followed = Auth::guard('customer-api')->user()
            ->followed()
            ->where('followed_type', CustomerUser::class)
            ->where('status', 'approved')
            ->pluck('followed_id');

        $liked_posts = $customer->postsLikes()
            ->join('customers_users', function ($q) use ($customers_followed, $user_id, $customers_blocks) {
                return $q->on('customers_users.id', 'posts_likes.user_id')
                    ->where(function ($query) use ($customers_followed, $user_id) {
                        if ($user_id != Auth::guard('customer-api')->id()) {
                            return $query->where('customers_users.profile_privacy', 'public')
                                ->orWhere(function ($q) use ($customers_followed) {
                                    return $q->where('customers_users.profile_privacy', 'followers')
                                        ->whereIn('customers_users.id', $customers_followed);
                                });
                        }
                        return $query;
                    })
                    ->whereNotIn('customers_users.id', $customers_blocks)
                    ->where('user_type', CustomerUser::class);
            })
            ->pluck('post_id')
            ->toArray();

        $commented_posts = $customer->postsComments()
            ->join('customers_users', function ($q) use ($customers_followed, $user_id, $customers_blocks) {
                return $q->on('customers_users.id', 'posts_comments.user_id')
                    ->where(function ($query) use ($customers_followed, $user_id) {
                        if ($user_id != Auth::guard('customer-api')->id()) {
                            return $query->where('customers_users.profile_privacy', 'public')
                                ->orWhere(function ($q) use ($customers_followed) {
                                    return $q->where('customers_users.profile_privacy', 'followers')
                                        ->whereIn('customers_users.id', $customers_followed);
                                });
                        }
                        return $query;
                    })
                    ->whereNotIn('customers_users.id', $customers_blocks)
                    ->where('user_type', CustomerUser::class);
            })
            ->pluck('post_id')
            ->toArray();

        $posts_ids = array_unique(array_merge($liked_posts, $commented_posts));

        //get hidden advertisers
        $hidden_advertisers = Auth::guard('customer-api')->user()
            ->hiddenAdvertisers()
            ->pluck('advertiser_id')
            ->toArray();
        $blocked_advertisers = Auth::guard('customer-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers_advertisers = Auth::guard('customer-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $advertisers_blocks = array_unique([...$blockers_advertisers, ...$blocked_advertisers, ...$hidden_advertisers]);
        //get followed advertisers
        $followed_advertisers = Auth::guard('customer-api')->user()
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
                            });
                    })
                    ->where('advertisers_users.status', 'active')
                    ->where('posts.user_type', AdvertiserUser::class)
                    ->whereNotIn('advertisers_users.id', $advertisers_blocks);
            })
            ->whereNull('posts.advertisement_id')
            ->whereIn('posts.id', $posts_ids)

            ->orderBy('posts.created_at', 'desc')
            ->paginate($limit);

        //add post view or update it
        foreach ($posts as $post) {
            $view_added = Auth::guard('customer-api')->user()
                ->viewedPosts()
                ->where('post_id', $post->id)
                ->first();

            if (!$view_added) {
                $post->views_count += 1;
                $post->save();
                Auth::guard('customer-api')->user()
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
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function toggleLikePost($id, Request $request)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //get post by id
        $post = Post::where('id', $id)
            ->first();

        //return error if post wasn't found
        if (!$post) {
            return $this->apiBadRequestResponse(__('api/customers/community/posts/posts.wrong-id'));
        }

        //return error if user wasn't found
        if (!$post->advertisement_id) {
            if ($post->user->status === 'banned') {
                return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
            }
        }

        DB::beginTransaction();
        try {
            $like = Auth::guard('customer-api')->user()
                ->postsLikes()
                ->where('post_id', $id)
                ->first();

            if ($request->has('isLiked')) {
                if ($request->get('isLiked')) {
                    if (!$like) {
                        Auth::guard('customer-api')->user()
                            ->postsLikes()
                            ->create([
                                'post_id' => $id
                            ]);
                        $post->likes_count += 1;
                    }

                    $isLiked = true;
                    $type = __('api/customers/community/posts/posts.liked');
                } else {
                    if ($like) {
                        $like->delete();
                        $post->likes_count -= 1;
                    }
                    $isLiked = false;
                    $type = __('api/customers/community/posts/posts.disliked');
                }
            } else {
                if ($like) {
                    $like->delete();
                    $post->likes_count -= 1;
                    $isLiked = false;
                    $type = __('api/customers/community/posts/posts.disliked');
                } else {
                    Auth::guard('customer-api')->user()
                        ->postsLikes()
                        ->create([
                            'post_id' => $id
                        ]);
                    $post->likes_count += 1;
                    $isLiked = true;
                    $type = __('api/customers/community/posts/posts.liked');
                }
            }
            $post->save();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/community/posts/posts.something-wrong'));
        }
        DB::commit();

        //send notification
        if ($isLiked && !$post->advertisement_id && !($post->user->user_type === 'customer' && $post->user_id == Auth::guard('customer-api')->id())) {
            $customProperties = [
                'postId' => $post->id,
                'userId' => Auth::guard('customer-api')->id(),
                'userType' => 'customer',
            ];
            $post_user = $post->user;

            $post_user->notifications()
                ->whereJsonContains('data->customProperties->postId', $post->id)
                ->whereJsonContains('data->action', 'like')
                ->delete();

            Notifications::sendForCommunity($post->user, 'posts', 'posts.like', 'like', $customProperties);
        }
        return $this->apiResponse([
            'message' => __('api/customers/community/posts/posts.like-toggle', ['toggle' => $type]),
            'isLiked' => $isLiked
        ]);
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function reportPost(Request $request)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $user = Auth::guard('customer-api')->user();
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
            return $this->apiBadRequestResponse(__('api/customers/community/posts/posts.wrong-id'));
        }

        //return error if user wasn't found
        if ($post->user->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        //check if user already reported
        $report = $post->reports()
            ->where('user_type', CustomerUser::class)
            ->where('user_id', $user->id)
            ->first();

        try {
            //create report
            if (!$report) {
                $report = $post->reports()
                    ->create([
                        'user_type' => CustomerUser::class,
                        'user_id' => $user->id,
                        'reason' => $data['reason'] ? Filter::RemoveHtml($data['reason']) : null,
                        'type' => $data['type'] ?? null,
                    ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/community/posts/posts.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/customers/community/posts/posts.reports.report-added'),
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
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $user = Auth::guard('customer-api')->user();

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
            return $this->apiBadRequestResponse(__('api/customers/community/posts/posts.reports.no-report'));
        }

        try {
            //create report
            $report = $report->update([
                'reason' => $data['reason'] ? Filter::RemoveHtml($data['reason']) : null,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/community/posts/posts.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/customers/community/posts/posts.reports.report-edited'),
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

        //set user
        $user = Auth::guard('customer-api')->user();

        //get reports
        $reports = $user->reports()
            ->where('reported_type', Post::class)
            ->paginate($limit);

        return $this->apiResponse(ReportedPostsResource::collection($reports));
    }
}
