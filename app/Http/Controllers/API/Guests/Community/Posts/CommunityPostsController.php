<?php

namespace App\Http\Controllers\API\Guests\Community\Posts;

use App\Helpers\Filter;
use App\Helpers\Geography\Geography;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Guests\Community\Posts\CommunityPostsResource;
use App\Http\Resources\Guests\Community\Posts\Reports\ReportedPostsResource;
use App\Models\Posts\Post;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

        $data = $request->only([
            'countryCode',
            'governorateId',
            'cityId',
            'categoryId',
        ]);

        $this->apiValidate($data, [
            'countryCode' => 'nullable|string|exists:countries,code',
            'governorateId' => 'nullable|string|exists:governorates,id',
            'cityId' => 'nullable|string|exists:cities,id',
            'categoryId' => 'nullable|string|exists:categories,id',
        ]);

        //get posts and paginate it
        $posts = Post::select('posts.*')
            ->join('advertisers_users', function ($q) {
                return $q->on('advertisers_users.id', 'posts.user_id')
                    ->where('advertisers_users.profile_privacy', 'public')
                    ->where('posts.user_type', AdvertiserUser::class)
                    ->where('advertisers_users.status', 'active');
            })
            ->where('posts.status','approved')
            ->leftJoin('categories', 'categories.id', 'posts.category_id')
            ->whereNull('posts.advertisement_id');

        //Filter country code
        if (isset($data['countryCode'])) {
            $posts = $posts->where(function ($q) use ($data) {
                return $q->where('advertisers_users.country_code', $data['countryCode']);
            });
        }

        $posts = Geography::applyUserLocationFilter($posts, $data);


        //Filter city
        if (isset($data['categoryId'])) {
            $posts = $posts->where(function ($q) use ($data) {
                return $q->where('posts.category_id', $data['categoryId']);
            });
        }

        $posts = $posts
            ->orderBy('posts.id', 'desc')
            ->groupBy('posts.id')
            ->paginate($limit);

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
        ]);

        $this->apiValidate($data, [
            'keyword' => 'nullable|string|min:3',
            'countryCode' => 'nullable|string|exists:countries,code',
            'governorateId' => 'nullable|string|exists:governorates,id',
            'cityId' => 'nullable|string|exists:cities,id',
            'categoryId' => 'nullable|string|exists:categories,id',
        ]);

        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('posts.pagination.limit', 10);

        //get posts
        $posts = Post::select([
            'posts.*'
        ])
            ->join('advertisers_users', function ($q) {
                return $q->on('advertisers_users.id', 'posts.user_id')
                    ->where('advertisers_users.profile_privacy', 'public')
                    ->where('posts.user_type', AdvertiserUser::class)
                    ->where('advertisers_users.status', 'active');
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
        if (isset($data['countryCode'])) {
            $posts = $posts->where(function ($q) use ($data) {
                return $q->where('advertisers_users.country_code', $data['countryCode']);
            });
        }

        $posts = Geography::applyUserLocationFilter($posts, $data);


        //Filter city
        if (isset($data['categoryId'])) {
            $posts = $posts->where(function ($q) use ($data) {
                return $q->where('posts.category_id', $data['categoryId']);
            });
        }

        //get the posts
        $posts = $posts
            ->orderBy('posts.created_at')
            ->groupBy('posts.id')
            ->paginate($limit);

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
            return $this->apiBadRequestResponse(__('api/guests/community/posts/posts.wrong-id'));
        }

        if ($post->user->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
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

        //get advertiser that has this username
        $advertiser = AdvertiserUser::where('id', $user_id)
            ->where('status', 'active')
            ->first();

        //get customer with this username
        $customer = CustomerUser::where('id', $user_id)
            ->where('status', 'active')
            ->first();

        //set user in case the user is found
        if ($advertiser) {
            $user = $advertiser;
        } elseif ($customer) {
            $user = $customer;
        } else {
            $user = null;
        }

        //return error if user wasn't found
        if (!$user) {
            return $this->apiBadRequestResponse(__('api/guests/community/posts/posts.wrong-userid'));
        }

        //get posts
        $posts = $user->posts()
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

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
            return $this->apiBadRequestResponse(__('api/guests/community/posts/posts.wrong-userid'));
        }

        if ($customer->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        $liked_posts = $customer->postsLikes()
            ->join('customers_users', function ($q) {
                return $q->on('customers_users.id', 'posts_likes.user_id')
                    ->where(function ($query) {
                        return $query->where('customers_users.profile_privacy', 'public');
                    })
                    ->where('user_type', CustomerUser::class);
            })
            ->pluck('post_id')
            ->toArray();

        $commented_posts = $customer->postsComments()
            ->join('customers_users', function ($q) {
                return $q->on('customers_users.id', 'posts_comments.user_id')
                    ->where(function ($query) {
                        return $query->where('customers_users.profile_privacy', 'public');
                    })
                    ->where('user_type', CustomerUser::class);
            })
            ->pluck('post_id')
            ->toArray();

        $posts_ids = array_unique(array_merge($liked_posts, $commented_posts));


        //get posts
        $posts = Post::select([
            'posts.*'
        ])
            ->join('advertisers_users', 'advertisers_users.id', 'posts.user_id')
            ->whereIn('posts.id', $posts_ids)
            ->whereNull('posts.advertisement_id')

            ->orderBy('posts.created_at', 'desc')
            ->paginate($limit);

        return $this->apiPaginateResponse(CommunityPostsResource::collection($posts));
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function reportPost(Request $request)
    {
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
        try {
            //create report
            $report = $post->reports()
                ->create([
                    'reason' => $data['reason'] ? Filter::RemoveHtml($data['reason']) : null,
                    'type' => $data['type'] ?? null,
                ]);
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
}
