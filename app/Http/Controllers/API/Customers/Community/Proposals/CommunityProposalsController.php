<?php

namespace App\Http\Controllers\API\Customers\Community\Proposals;

use App\Helpers\Files;
use App\Helpers\Filter;
use App\Helpers\Notifications;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Customers\Community\Proposals\CommunityProposalsResource;
use App\Http\Resources\Customers\Community\Proposals\Reports\ReportedProposalsResource;
use App\Models\Proposals\Proposal;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\Video\X264;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Image\Image;

class CommunityProposalsController extends Controller
{
    /**
     * @return Application|ResponseFactory|Response
     */
    public function getUserProposals(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('proposals.pagination.limit', 10);
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
        //get proposals
        $proposals = Auth::guard('customer-api')->user()
            ->sentProposals()
            ->whereHas('advertiser', function ($q) use ($advertisers_blocks) {
                return $q->where('status', 'active')
                    ->whereNotIn('advertisers_users.id', $advertisers_blocks);
            });

        // answered or not
        if ($request->has('isAnswered') && !is_null($request->get('isAnswered'))) {
            if ($request->get('isAnswered')) {
                $proposals = $proposals->whereNotNull('answer')
                    ->where('answer', '!=', '');
            } else {
                $proposals = $proposals->whereNull('answer')
                    ->orWhere('answer', '');
            }
        }

        $proposals = $proposals->orderBy('updated_at', 'desc')
            ->paginate($limit);

        return $this->apiPaginateResponse(CommunityProposalsResource::collection($proposals));
    }

    /**
     * @return Application|ResponseFactory|Response
     */
    public function getProposal($id)
    {
        //get proposals
        $proposal = Auth::guard('customer-api')->user()
            ->sentProposals()
            ->whereHas('advertiser', function ($q) {
                return $q->where('status', 'active');
            })
            ->where('id', $id)
            ->first();

        if (!$proposal) {
            return $this->apiBadRequestResponse(__('api/customers/community/proposals/proposals.wrong-id'));
        }

        return $this->apiResponse(CommunityProposalsResource::make($proposal));
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function addProposal(Request $request)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $data = $request->only([
            'advertiserId',
            'content',
            'media'
        ]);

        $this->apiValidate($data, [
            'advertiserId' => ['required', 'exists:advertisers_users,id'],
            'content' => ['required'],
            'media' => ['nullable', 'max:5'],
            'media.*.file' => ['nullable', 'mimes:jpg,jpeg,png,bmp,gif,mp4,mov,ogg,qt,avi,wmv,flv,ts,3gp', 'max:100000'],
            'media.*.startAt' => ['nullable', 'integer'],
            'media.*.endAt' => ['nullable', 'integer', 'gt:media.*.startAt'],
        ]);

        $advertiser = AdvertiserUser::where('id', $data['advertiserId'])
            ->first();

        if (!$advertiser) {
            return $this->apiBadRequestResponse(__('api/customers/community/proposals/proposals.wrong-id'));
        }

        if ($advertiser->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }
        if ($advertiser->profile_privacy === 'followers') {
            //check if user is followed
            $user_followed = Auth::guard('customer-api')->user()
                ->followed()
                ->where('followed_type', $advertiser->class)
                ->where('followed_id', $data['advertiserId'])
                ->where('status', 'approved')
                ->first();
            if (!$user_followed) {
                return $this->apiBadRequestResponse(__('api/customers/community/proposals/proposals.profile-followers'));
            }
        } else if ($advertiser->profile_privacy === 'private') {
            return $this->apiBadRequestResponse(__('api/customers/community/proposals/proposals.profile-private'));
        }
        DB::beginTransaction();
        try {
            $proposal = Auth::guard('customer-api')->user()
                ->sentProposals()
                ->create([
                    'advertiser_id' => $data['advertiserId'],
                    'content' => $data['content'],
                ]);

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
                    $proposal->addMedia($file)
                        ->withCustomProperties(['width' => $file_width, 'height' => $file_height])
                        ->toMediaCollection('proposals');
                }
            }


            $customProperties = [
                'proposalId' => $proposal->id,
                'userId' => Auth::guard('customer-api')->id(),
                'userType' => 'customer',
            ];

            $advertiser = $proposal->advertiser;

            Notifications::sendForCommunity($advertiser, 'proposals', 'proposals.requested', 'add', $customProperties);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/community/proposals/proposals.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/customers/community/proposals/proposals.proposal-added'),
            'data' => CommunityProposalsResource::make($proposal),
        ]);
    }

    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function deleteProposal($id)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //get proposal
        $proposal = Auth::guard('customer-api')->user()
            ->sentProposals()
            ->where('id', $id)
            ->first();

        if (!$proposal) {
            return $this->apiBadRequestResponse(__('api/customers/community/proposals/proposals.no-permission'));
        }
        DB::beginTransaction();
        try {
            DB::table('notifications')
                ->whereJsonContains('data->customProperties->proposalId', $proposal->id)
                ->delete();

            //soft-delete the post
            $proposal->delete();

        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/community/proposals/proposals.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/customers/community/proposals/proposals.deleted'),
        ]);
    }

    /**
     * edit post
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function editProposal(Request $request, $id)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $proposal = Auth::guard('customer-api')->user()
            ->sentProposals()
            ->where('id', $id)
            ->first();

        //return error if the user doesn't have permission to delete this post
        if (!$proposal) {
            return $this->apiBadRequestResponse(__('api/customers/community/proposals/proposals.no-permission'));
        }

        //check whether there is files to be deleted or not
        if ($request->has('deleteMedia') && $request->get('deleteMedia') != null) {
            //get media will be deleted
            $media_count = $proposal->getMedia('proposals')
                ->whereIn('id', $request->get('deleteMedia'))
                ->count();

            //check if the media to be deleted count belongs to this post and increase max count
            $add_max_count = (sizeof($request->get('deleteMedia')) == $media_count) ? $media_count : 0;
        } else {
            $add_max_count = 0;
        }

        //check whether the edit have new photos or not to set the maximum allowed images.
        if ($request->has('media')) {
            $max_count = $add_max_count + 3 - $proposal->getMedia('proposals')->count();
        } else {
            $max_count = 0;
        }

        //Set data
        $data = $request->only([
            'content',
            'media',
            'deleteMedia'
        ]);

        //Validate course id
        $this->apiValidate($data, [
            'content' => ['nullable', 'string'],
            'media' => ['nullable', "max:$max_count"],
            'media.*.file' => ['nullable', 'mimes:jpg,jpeg,png,bmp,gif,mp4,mov,ogg,qt,avi,wmv,flv,ts,3gp', 'max:100000'],
            'media.*.startAt' => ['nullable', 'integer'],
            'media.*.endAt' => ['nullable', 'integer', 'gt:media.*.startAt'],
            'deleteMedia' => ['nullable', 'array'],
            'deleteMedia.*' => ['nullable', 'exists:media,id']
        ]);


        DB::beginTransaction();
        try {
            //delete old media
            if ($request->has('deleteMedia') && $request->get('deleteMedia') != null) {
                foreach ($request->get('deleteMedia') as $media) {
                    $proposal->getMedia('proposals')
                        ->where('id', $media)
                        ->first()
                        ->delete();
                }
            }

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
                    $proposal->addMedia($file)
                        ->withCustomProperties(['width' => $file_width, 'height' => $file_height])
                        ->toMediaCollection('proposals');
                }
            }

            //update the post
            $proposal->update([
                'content' => $data['content'],
            ]);

        } catch (Exception $e) {
            //roll back
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/community/proposals/proposals.something-wrong'));
        }
        //commit
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/customers/community/proposals/proposals.proposal-edited'),
            'data' => CommunityProposalsResource::make($proposal),
        ]);
    }

    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function reportProposal($id, Request $request)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $user = Auth::guard('customer-api')->user();
        //set data
        $data = $request->only([
            'reason',
            'type',
        ]);
        $data['proposalId'] = $id;
        //validate data
        $this->apiValidate($data, [
            'proposalId' => ['required', 'exists:proposals,id'],
            'type' => ['nullable', 'in:Sexually Inappropriate,Abusive Content,Misleading or Scam,Offensive,Violence,Prohibited Content,Spam,False News,Other'],
            'reason' => ['nullable'],
        ]);

        //get post
        $proposal = Proposal::where('id', $data['proposalId'])
            ->first();

        //return error if post wasn't found
        if (!$proposal) {
            return $this->apiBadRequestResponse(__('api/customers/community/proposals/proposals.wrong-id'));
        }

        //return error if post wasn't found
        if ($proposal->advertiser->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }
        //check if user already reported
        $report = $proposal->reports()
            ->where('user_type', CustomerUser::class)
            ->where('user_id', $user->id)
            ->first();
        try {
            //create report
            if (!$report) {
                $report = $proposal->reports()
                    ->create([
                        'user_type' => CustomerUser::class,
                        'user_id' => $user->id,
                        'type' => $data['type'] ?? null,
                        'reason' => $data['reason'] ? Filter::RemoveHtml($data['reason']) : null,
                    ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/community/proposals/proposals.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/customers/community/proposals/proposals.reports.report-added'),
            'data' => ReportedProposalsResource::make($report),
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function editProposalReport(Request $request, $id)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //get user
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
            return $this->apiBadRequestResponse(__('api/customers/community/proposals/proposals.reports.no-report'));
        }

        try {
            //create report
            $report->update([
                'reason' => $data['reason'] ? Filter::RemoveHtml($data['reason']) : null,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/community/proposals/proposals.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/customers/community/proposals/proposals.reports.report-edited'),
            'data' => ReportedProposalsResource::make($report),
        ]);
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getReportedProposals(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('reported.proposals.pagination.limit', 10);

        //set user
        $user = Auth::guard('customer-api')->user();

        //get reports
        $reports = $user->reports()
            ->where('reported_type', Proposal::class)
            ->paginate($limit);

        return $this->apiResponse(ReportedProposalsResource::collection($reports));
    }
}
