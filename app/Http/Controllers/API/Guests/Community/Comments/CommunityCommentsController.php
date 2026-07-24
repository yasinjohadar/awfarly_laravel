<?php

namespace App\Http\Controllers\API\Guests\Community\Comments;

use App\Helpers\Filter;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Guests\Community\Comments\CommunityCommentsResource;
use App\Http\Resources\Guests\Community\Comments\Reports\ReportedCommentsResource;
use App\Models\Posts\Comments\PostComments;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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

        //get comments for the post
        $post_comments = PostComments::where('post_id', $id)
            ->whereHasMorph('user', '*', function ($q) {
                return $q->where('status', 'active');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return $this->apiPaginateResponse(CommunityCommentsResource::collection($post_comments));
    }

    /**
     * @param $comment_id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function reportComment($comment_id, Request $request)
    {
        //set data
        $data = $request->only([
            'reason',
            'type',
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

        try {
            //create report
            $report = $comment->reports()
                ->create([
                    'reason' => $data['reason'] ? Filter::RemoveHtml($data['reason']) : null,
                    'type' => $data['type'] ?? null,
                ]);
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
}
