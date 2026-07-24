<?php

namespace App\Http\Controllers\API\Advertisers\Community\Comments;

use App\Helpers\Filter;
use App\Helpers\Notifications;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Advertisers\Community\Comments\CommunityCommentsResource;
use App\Http\Resources\Advertisers\Community\Comments\Reports\ReportedCommentsResource;
use App\Models\Posts\Comments\PostComments;
use App\Models\Posts\Post;
use App\Models\Posts\Subscriptions\PostSubscriptions;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Auth;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Kreait\Laravel\Firebase\Facades\Firebase;

class CommunityCommentsController extends Controller
{
    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getPostComments($id, Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('comments.pagination.limit', 10);

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

        //get comments for the post
        $post_comments = PostComments::where('post_id', $id)
            ->whereHasMorph('user', '*', function ($q, $type) use ($advertisers_blocks, $customers_blocks) {
                if ($type === AdvertiserUser::class) {
                    $q->whereNotIn('id', $advertisers_blocks);
                } else if ($type === CustomerUser::class) {
                    $q->whereNotIn('id', $customers_blocks);
                }
                $q->where('comment_id', null);
                $q->where('status', 'active');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return $this->apiPaginateResponse(CommunityCommentsResource::collection($post_comments));
    }

    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function addPostComment($id, Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $post = Post::where('id', $id)
            ->first();

        //return error if post wasn't found
        if (!$post) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/comments/comments.wrong-post'));
        }

        if ($post->user) {
            if ($post->user->status === 'banned') {
                return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
            }
        }

        //get data
        $data = [
            'comment' => $request->has('comment') ? Filter::RemoveHtml($request->get('comment')) : null,
            'comment_id' => $request->has('comment_id') ? Filter::RemoveHtml($request->get('comment_id')) : null,
            'post_id' => $id,
        ];

        //validate
        $this->apiValidate($data, [
            'comment' => ['required', 'string'],
            'comment_id' => ['sometimes', 'nullable', 'integer'],
        ]);

        DB::beginTransaction();
        try {
            //add the comment
            $comment = Auth::guard('advertiser-api')->user()
                ->postsComments()
                ->create($data);

            //edit comments count on post
            $post->comments_count += 1;
            $post->save();
        } catch (Exception $e) {
            //roll back
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/comments/comments.something-wrong'));
        }
        //commit
        DB::commit();

        $customProperties = [
            'postId' => $post->id,
            'commentId' => $comment->id,
            'userId' => Auth::guard('advertiser-api')->id(),
            'userType' => 'advertiser',
        ];

        $posts_subscriptions = PostSubscriptions::where('post_id', $post->id)
            ->whereHasMorph('user', '*', function ($q, $type) {
                if ($type === AdvertiserUser::class) {
                    return $q->where('id', '!=', Auth::guard('advertiser-api')->id());
                }
                return $q;
            })
            ->get()
            ->map(function ($subscription) {
                return $subscription->user;
            });

        //send notifications
        Notifications::sendForCommunity($posts_subscriptions, 'posts.comments', 'posts.comment_add_subscription', 'add', $customProperties);

        $isPostOwner = ($post->user && $post->user->user_type === 'advertiser' && $post->user->id == Auth::guard('advertiser-api')->id());
        if (!$isPostOwner && !$post->advertisement_id) {
            Notifications::sendForCommunity($post->user, 'posts.comments', 'posts.comment_add', 'add', $customProperties);
        }

        return $this->apiResponse([
            'message' => __('api/advertisers/community/comments/comments.comment-added'),
            'data' => CommunityCommentsResource::make($comment),
        ]);
    }

    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function editPostComment($id, Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $comment = Auth::guard('advertiser-api')->user()
            ->postsComments()
            ->where('id', $id)
            ->first();

        //return error if post wasn't found
        if (!$comment) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/comments/comments.wrong-id'));
        }

        //get data
        $data = [
            'comment' => $request->has('comment') ? Filter::RemoveHtml($request->get('comment')) : null,
        ];

        //validate
        $this->apiValidate($data, [
            'comment' => ['required', 'string'],
        ]);

        DB::beginTransaction();
        try {
            //add the comment
            $comment->update($data);
        } catch (Exception $e) {
            //roll back
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/comments/comments.something-wrong'));
        }
        //commit
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/community/comments/comments.comment-edited'),
            'data' => CommunityCommentsResource::make($comment),
        ]);
    }

    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function deletePostComment($id)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $comment = Auth::guard('advertiser-api')->user()
            ->postsComments()
            ->where('id', $id)
            ->first();

        //return error if post wasn't found
        if (!$comment) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/comments/comments.wrong-id'));
        }

        DB::beginTransaction();
        try {
            //get post
            $post = $comment->post;

            DB::table('notifications')
                ->whereJsonContains('data->customProperties->postId', $post->id)
                ->whereJsonContains('data->customProperties->commentId', $comment->id)
                ->delete();

            //update post comments count
            $post->comments_count -= 1;
            $post->save();

            //delete comment
            $comment->delete();
        } catch (Exception $e) {
            //roll back
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/comments/comments.something-wrong'));
        }
        //commit
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/community/comments/comments.comment-deleted'),
        ]);
    }

    /**
     * @param $comment_id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function reportComment($comment_id, Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $user = Auth::guard('advertiser-api')->user();
        //set data
        $data = $request->only([
            'type',
            'reason',
        ]);
        $data['commentId'] = $comment_id;

        //validate data
        $this->apiValidate($data, [
            'commentId' => ['required', 'exists:posts_comments,id'],
            'type' => ['nullable', 'in:Sexually Inappropriate,Abusive Content,Misleading or Scam,Offensive,Violence,Prohibited Content,Spam,False News,Other'],
            'reason' => ['nullable'],
        ]);

        //get post
        $comment = PostComments::where('id', $data['commentId'])
            ->first();

        //return error if post wasn't found
        if (!$comment) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/comments/comments.wrong-id'));
        }

        if ($comment->user->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        //check if user already reported
        $report = $comment->reports()
            ->where('user_type', AdvertiserUser::class)
            ->where('user_id', $user->id)
            ->first();

        try {
            //create report
            if (!$report) {
                $report = $comment->reports()
                    ->create([
                        'user_type' => AdvertiserUser::class,
                        'user_id' => $user->id,
                        'type' => $data['type'] ?? null,
                        'reason' => $data['reason'] ? Filter::RemoveHtml($data['reason']) : null,
                    ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/comments/comments.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/community/comments/comments.reports.report-added'),
            'data' => ReportedCommentsResource::make($report),
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function editCommentReport(Request $request, $id)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //get user
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
            return $this->apiBadRequestResponse(__('api/advertisers/community/comments/comments.reports.no-report'));
        }

        try {
            //create report
            $report->update([
                'reason' => $data['reason'] ? Filter::RemoveHtml($data['reason']) : null,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/comments/comments.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/community/comments/comments.reports.report-edited'),
            'data' => ReportedCommentsResource::make($report),
        ]);
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getReportedComments(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('reported.comments.pagination.limit', 10);

        //set user
        $user = Auth::guard('advertiser-api')->user();

        //get reports
        $reports = $user->reports()
            ->where('reported_type', PostComments::class)
            ->paginate($limit);

        return $this->apiResponse(ReportedCommentsResource::collection($reports));
    }

    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function toggleLikeComment($id, Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //get comment by id
        $comment = PostComments::where('id', $id)
            ->first();

        //return error if comment wasn't found
        if (!$comment) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/comments/comments.wrong-id'));
        }

        if ($comment->user->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }
        DB::beginTransaction();
        try {
            $like = Auth::guard('advertiser-api')->user()
                ->commentsLikes()
                ->where('comment_id', $id)
                ->first();

            if ($request->has('isLiked')) {
                if ($request->get('isLiked')) {
                    if (!$like) {
                        Auth::guard('advertiser-api')->user()
                            ->commentsLikes()
                            ->create([
                                'comment_id' => $id
                            ]);
                        $comment->likes_count += 1;
                    }
                    $isLiked = true;
                    $type = __('api/advertisers/community/comments/comments.liked');
                } else {
                    if ($like) {
                        $like->delete();
                        $comment->likes_count -= 1;
                    }
                    $isLiked = false;
                    $type = __('api/advertisers/community/comments/comments.disliked');
                }
            } else {
                if ($like) {
                    $like->delete();
                    $comment->likes_count -= 1;
                    $isLiked = false;
                    $type = __('api/advertisers/community/comments/comments.disliked');
                } else {
                    Auth::guard('advertiser-api')->user()
                        ->commentsLikes()
                        ->create([
                            'comment_id' => $id
                        ]);
                    $comment->likes_count += 1;
                    $isLiked = true;
                    $type = __('api/advertisers/community/comments/comments.liked');
                }
            }
            $comment->save();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/comments/comments.something-wrong'));
        }
        DB::commit();

        if ($isLiked && !($comment->user->user_type === 'advertiser' && $comment->user_id == Auth::guard('advertiser-api')->id())) {
            $customProperties = [
                'postId' => $comment->post->id,
                'commentId' => $comment->id,
                'userId' => Auth::guard('advertiser-api')->id(),
                'userType' => 'advertiser',
            ];
            $comment_user = $comment->user;

            $comment_user->notifications()
                ->whereJsonContains('data->customProperties->postId', $comment->post->id)
                ->whereJsonContains('data->customProperties->commentId', $comment->id)
                ->whereJsonContains('data->action', 'like')
                ->delete();

            Notifications::sendForCommunity($comment->user, 'posts.comments', 'posts.comment_like', 'like', $customProperties);
        }
        return $this->apiResponse([
            'message' => __('api/advertisers/community/comments/comments.like-toggle', ['toggle' => $type]),
            'data' => [
                'isLiked' => $isLiked,
            ]
        ]);
    }
}
