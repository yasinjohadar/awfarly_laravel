<?php

namespace App\Http\Controllers\API\Customers\Community\Posts\Saved;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Customers\Community\Posts\CommunityPostsResource;
use App\Http\Resources\Customers\Community\Posts\Saved\SavedPostsResource;
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

class SavedPostsController extends Controller
{
    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function toggleSavedPosts($id, Request $request)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //get user
        $user = Auth::guard('customer-api')->user();

        //set data
        $data = $request->only([
            'isSaved'
        ]);

        $data['postId'] = $id;

        //validate data
        $this->apiValidate($data, [
            'postId' => ['required', 'exists:posts,id'],
            'isSaved' => ['nullable', 'boolean'],
        ]);


        DB::beginTransaction();
        try {
            $hidden_post = $user->savedPosts()
                ->where('post_id', $data['postId'])
                ->first();

            if ($request->has('isSaved')) {
                if ($request->get('isSaved')) {
                    if (!$hidden_post) {
                        $user->savedPosts()
                            ->create([
                                'post_id' => $data['postId'],
                            ]);
                    }
                    $type = __('api/customers/community/posts/saved-posts.saved');
                    $isSaved = true;
                } else {
                    if ($hidden_post) {
                        $hidden_post->delete();
                    }
                    $isSaved = false;
                    $type = __('api/customers/community/posts/saved-posts.unsaved');
                }
            } else {
                if ($hidden_post) {
                    $hidden_post->delete();
                    $isSaved = false;
                    $type = __('api/customers/community/posts/saved-posts.unsaved');
                } else {
                    $user->savedPosts()
                        ->create([
                            'post_id' => $data['postId'],
                        ]);
                    $isSaved = true;
                    $type = __('api/customers/community/posts/saved-posts.saved');
                }

            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/community/posts/saved-posts.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/customers/community/posts/saved-posts.follow-toggle', ['toggle' => $type]),
            'data' => [
                'isSaved' => $isSaved,
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getSavedPosts(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('saved.posts.pagination.limit', 10);
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

        $advertisers_blocks = array_unique([...$blockers_advertisers, ...$blocked_advertisers]);

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
            ->join('saved_posts', function ($q) {
                return $q->on('saved_posts.post_id', 'posts.id')
                    ->where('saved_posts.user_type', CustomerUser::class)
                    ->where('saved_posts.user_id', Auth::guard('customer-api')->id());
            })
            ->whereNull('posts.advertisement_id')
            ->paginate($limit);

        return $this->apiPaginateResponse(CommunityPostsResource::collection($posts));
    }
}
