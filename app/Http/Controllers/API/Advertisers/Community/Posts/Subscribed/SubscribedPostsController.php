<?php

namespace App\Http\Controllers\API\Advertisers\Community\Posts\Subscribed;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Advertisers\Community\Posts\CommunityPostsResource;
use App\Http\Resources\Advertisers\Community\Posts\Subscribed\SubscribedPostsResource;
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

class SubscribedPostsController extends Controller
{
    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function toggleSubscribedPosts($id, Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //get user
        $user = Auth::guard('advertiser-api')->user();

        //set data
        $data = $request->only([
            'isSubscribed'
        ]);

        $data['postId'] = $id;

        //validate data
        $this->apiValidate($data, [
            'postId' => ['required', 'exists:posts,id'],
            'isSubscribed' => ['nullable', 'boolean'],
        ]);


        DB::beginTransaction();
        try {
            $hidden_post = $user->subscribedPosts()
                ->where('post_id', $data['postId'])
                ->first();

            $post = Post::where('id', $data['postId'])
                ->first();

            if ($request->has('isSubscribed')) {
                if ($request->get('isSubscribed')) {
                    if (!$hidden_post) {
                        $user->subscribedPosts()
                            ->create([
                                'post_id' => $data['postId'],
                            ]);
                    }
                    $isSubscribed = true;
                    $type = __('api/advertisers/community/posts/subscribed-posts.subscribed');
                } else {
                    if ($hidden_post) {
                        $hidden_post->delete();
                    }
                    $isSubscribed = false;
                    $type = __('api/advertisers/community/posts/subscribed-posts.unsubscribed');
                }
            } else {
                if ($hidden_post) {
                    $hidden_post->delete();
                    $isSubscribed = false;
                    $type = __('api/advertisers/community/posts/subscribed-posts.unsubscribed');
                } else {
                    $user->subscribedPosts()
                        ->create([
                            'post_id' => $data['postId'],
                        ]);
                    $isSubscribed = true;
                    $type = __('api/advertisers/community/posts/subscribed-posts.subscribed');
                }

            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/posts/subscribed-posts.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/community/posts/subscribed-posts.follow-toggle', ['toggle' => $type]),
            'data' => [
                'isSubscribed' => $isSubscribed,
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getSubscribedPosts(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('subscribed.posts.pagination.limit', 10);
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

        $advertisers_blocks = array_unique([...$blockers_advertisers, ...$blocked_advertisers]);

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
        $posts = Post::query()
            ->select('posts.*')
            ->whereHasMorph('user', '*', function ($q, $type) use ($advertisers_blocks, $customers_blocks) {
                if ($type === AdvertiserUser::class) {
                    $q->whereNotIn('id', $advertisers_blocks);
                } else if ($type === CustomerUser::class) {
                    $q->whereNotIn('id', $customers_blocks);
                }
                $q->where('status', 'active');
            })
            ->join('posts_subscriptions', function ($q) {
                return $q->on('posts_subscriptions.post_id', 'posts.id')
                    ->where('posts_subscriptions.user_type', AdvertiserUser::class)
                    ->where('posts_subscriptions.user_id', Auth::guard('advertiser-api')->id());
            })
            ->whereNull('posts.advertisement_id')
            ->paginate($limit);

        return $this->apiPaginateResponse(CommunityPostsResource::collection($posts));
    }
}
