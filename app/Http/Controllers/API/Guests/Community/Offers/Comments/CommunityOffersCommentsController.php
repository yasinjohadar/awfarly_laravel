<?php

namespace App\Http\Controllers\API\Guests\Community\Offers\Comments;

use App\Helpers\Filter;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Guests\Community\Offers\Comments\CommunityOffersCommentsResource;
use App\Http\Resources\Guests\Community\Offers\Comments\Reports\ReportedOffersCommentsResource;
use App\Models\Offers\Comments\OffersComments;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CommunityOffersCommentsController extends Controller
{
    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getOfferComments($id, Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('offers.comments.pagination.limit', 10);

        //get comments for the offer
        $offer_comments = OffersComments::where('offer_id', $id)
            ->whereHasMorph('user', '*', function ($q) {
                return $q->where('status', 'active');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return $this->apiPaginateResponse(CommunityOffersCommentsResource::collection($offer_comments));
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function reportComment(Request $request)
    {
        //set data
        $data = $request->only([
            'commentId',
            'type',
            'reason',
        ]);

        //validate data
        $this->apiValidate($data, [
            'commentId' => ['required', 'exists:offers_comments,id'],
            'type' => ['nullable', 'in:Sexually Inappropriate,Abusive Content,Misleading or Scam,Offensive,Violence,Prohibited Content,Spam,False News,Other'],
            'reason' => ['nullable'],
        ]);

        //get offer
        $comment = OffersComments::where('id', $data['commentId'])
            ->first();

        //return error if offer wasn't found
        if (!$comment) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/offers/comments/comments.wrong-id'));
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
            return $this->apiExceptionResponse(__('api/advertisers/community/offers/comments/comments.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/community/offers/comments/comments.reports.report-added'),
            'data' => ReportedOffersCommentsResource::make($report),
        ]);
    }
}
