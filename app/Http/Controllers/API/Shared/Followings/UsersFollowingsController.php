<?php

namespace App\Http\Controllers\API\Shared\Followings;

use App\Helpers\Notifications;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Shared\UsersFollowings\FollowRequestsResource;
use App\Http\Resources\Shared\UsersFollowings\UsersFollowedResource;
use App\Http\Resources\Shared\UsersFollowings\UsersFollowersResource;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsersFollowingsController extends Controller
{
    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function toggleUserFollow(Request $request)
    {
        //set data
        $data = $request->only([
            'userType',
            'userId',
            'isFollowed'
        ]);

        //validate data
        $this->apiValidate($data, [
            'userType' => ['required', 'in:advertiser,customer'],
            'userId' => ['required'],
            'isFollowed' => ['nullable', 'boolean'],
        ]);

        //get user
        $user = Auth::guard(Auth::user()->token()->name . '-api')->user();
        if (!$user) {
            return $this->apiExceptionResponse(__('api/shared/followings/followings.something-wrong'));
        }
        if ($user->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //get followed
        if ($data['userType'] === 'advertiser') {
            $followed = AdvertiserUser::where('id', $data['userId'])
                ->first();
        } else {
            $followed = CustomerUser::where('id', $data['userId'])
                ->first();
        }

        if ($user->user_type === $data['userType'] && $data['userId'] == $user->id) {
            return $this->apiBadRequestResponse(__('api/shared/followings/followings.follow-self'));
        }


        //return error if post wasn't found
        if (!$followed) {
            return $this->apiBadRequestResponse(__('api/shared/followings/followings.wrong-id'));
        }


        if ($followed->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        //if user doesn't allow follow return error
        if (!$followed->is_follow_allowed || $followed->profile_privacy === 'private') {
            return $this->apiBadRequestResponse(__('api/shared/followings/followings.follow-disabled'));
        } else if ($followed->profile_privacy === 'followers') {
            $approve_status = 'pending';
            $is_approved = false;
        } else {
            $approve_status = 'approved';
            $is_approved = true;
        }
        DB::beginTransaction();
        try {
            $follow = $user->followed()
                ->where('followed_id', $data['userId'])
                ->where('followed_type', $followed->class)
                ->first();

            if ($follow && $follow->status === 'declined') {
                return $this->apiBadRequestResponse(__('api/shared/followings/followings.follow-declined'));
            }


            if ($request->has('isFollowed')) {
                if ($data['isFollowed']) {
                    if (!$follow) {
                        $follow = $user->followed()
                            ->create([
                                'followed_id' => $data['userId'],
                                'followed_type' => $followed->class,
                                'status' => $approve_status,
                            ]);
                    }
                    $isFollowed = $follow && $follow->status === 'approved';
                    if ($isFollowed) {
                        $type = __('api/shared/followings/followings.followed');
                        $message = __('api/shared/followings/followings.follow-toggle', ['toggle' => $type]);
                    } else {
                        $message = __('api/shared/followings/followings.follow-pending');
                    }

                } else {
                    if ($follow) {
                        $follow->delete();
                    }
                    $isFollowed = false;
                    $approve_status = 'removed';
                    $type = __('api/shared/followings/followings.unfollowed');
                    $message = __('api/shared/followings/followings.follow-toggle', ['toggle' => $type]);
                }
            } else {
                if ($follow) {
                    $follow->delete();
                    $approve_status = 'removed';
                    $isFollowed = false;
                    $type = __('api/shared/followings/followings.unfollowed');
                    $message = __('api/shared/followings/followings.follow-toggle', ['toggle' => $type]);
                } else {
                    $follow = $user->followed()
                        ->create([
                            'followed_id' => $data['userId'],
                            'followed_type' => $followed->class,
                            'status' => $approve_status,
                        ]);
                    $isFollowed = $follow && $follow->status === 'approved';
                    if ($isFollowed) {
                        $type = __('api/shared/followings/followings.followed');
                        $message = __('api/shared/followings/followings.follow-toggle', ['toggle' => $type]);
                    } else {
                        $message = __('api/shared/followings/followings.follow-pending');
                    }
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/shared/followings/followings.something-wrong'));
        }
        DB::commit();

        if (in_array($approve_status, ['approved', 'pending'])) {
            $follow_exists = $followed->notifications()
                ->whereJsonContains('data->customProperties->userId', $user->id)
                ->whereJsonContains('data->customProperties->userType', $user->userType)
                ->whereNotNull('data->customProperties->followId')
                ->exists();

            if (!$follow_exists) {
                $notification_message = $approve_status === 'pending' ? 'requested' : 'followed';

                $customProperties = [
                    'userId' => $user->id,
                    'userType' => $user->user_type,
                    'status' => $approve_status,
                    'followId' => $follow->id,
                ];
                Notifications::sendForCommunity($followed, 'followings', "followings.{$notification_message}", 'add', $customProperties);
            }
        }
        return $this->apiResponse([
            'message' => $message,
            'data' => [
                'isFollowed' => $isFollowed,
                'followStatus' => $approve_status,
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getFollowers(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('user.followings.pagination.limit', 10);

        $data = $request->only([
            'userId',
            'userType',
        ]);

        $this->apiValidate($data, [
            'userId' => ['nullable', 'integer'],
            'userType' => ['nullable', 'in:customer,advertiser'],
        ]);
        $auth = Auth::guard(Auth::user()->token()->name . '-api')->user();
        if ((isset($data['userId']) && $data['userId'] != null) && $data['userType']) {
            if ($data['userType'] === 'advertiser') {
                $user = AdvertiserUser::where('id', $data['userId'])
                    ->first();
            } else {
                $user = CustomerUser::where('id', $data['userId'])
                    ->first();
            }
            if (!$user) {
                return $this->apiBadRequestResponse(__('api/shared/followings/followings.wrong-id'));
            }

            if ($auth->user_type === $data['userType'] && $auth->id == $user->id) {
            } else {
                if ($user->profile_privacy === 'followers') {
                    //check if user is followed
                    $user_followed = Auth::guard(Auth::user()->token()->name . '-api')->user()
                        ->followed()
                        ->where('followed_type', $user->class)
                        ->where('followed_id', $data['userId'])
                        ->where('status', 'approved')
                        ->first();
                    if (!$user_followed) {
                        return $this->apiBadRequestResponse(__('api/shared/followings/followings.profile-followers'));
                    }
                } else if ($user->profile_privacy === 'private') {
                    return $this->apiBadRequestResponse(__('api/shared/followings/followings.profile-private'));
                }
            }

        } else {
            //get user
            $user = Auth::guard(Auth::user()->token()->name . '-api')->user();
        }

        $followers_users = $user->followers()
            ->whereHasMorph('follower', '*', function ($q) {
                return $q->where('status', 'active');
            })
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return $this->apiPaginateResponse(UsersFollowersResource::collection($followers_users));
    }


    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getFollowedUsers(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('user.followings.pagination.limit', 10);

        $data = $request->only([
            'userId',
            'userType',
        ]);

        $this->apiValidate($data, [
            'userId' => ['nullable', 'integer'],
            'userType' => ['nullable', 'in:customer,advertiser'],
        ]);
        $auth = Auth::guard(Auth::user()->token()->name . '-api')->user();
        if ((isset($data['userId']) && $data['userId'] != null) && $data['userType']) {
            if ($data['userType'] === 'advertiser') {
                $user = AdvertiserUser::where('id', $data['userId'])
                    ->first();
            } else {
                $user = CustomerUser::where('id', $data['userId'])
                    ->first();
            }

            if (!$user) {
                return $this->apiBadRequestResponse(__('api/shared/followings/followings.wrong-id'));
            }

            if (!($auth->user_type === $data['userType'] && $auth->id == $user->id)) {
                if ($user->profile_privacy === 'followers') {
                    //check if user is followed
                    $user_followed = Auth::guard(Auth::user()->token()->name . '-api')->user()
                        ->followed()
                        ->where('followed_type', $user->class)
                        ->where('followed_id', $data['userId'])
                        ->where('status', 'approved')
                        ->first();
                    if (!$user_followed) {
                        return $this->apiBadRequestResponse(__('api/shared/followings/followings.profile-followers'));
                    }
                } else if ($user->profile_privacy === 'private') {
                    return $this->apiBadRequestResponse(__('api/shared/followings/followings.profile-private'));
                }
            }

        } else {
            //get user
            $user = Auth::guard(Auth::user()->token()->name . '-api')->user();
        }

        $followed_users = $user->followed()
            ->whereHasMorph('followed', '*', function ($q) {
                return $q->where('status', 'active');
            })
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return $this->apiPaginateResponse(UsersFollowedResource::collection($followed_users));
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getFollowRequests(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('user.followings.pagination.limit', 10);

        //get user
        $user = Auth::guard(Auth::user()->token()->name . '-api')->user();

        $follow_requests = $user->followers()
            ->whereHas('follower')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);


        return $this->apiPaginateResponse(FollowRequestsResource::collection($follow_requests));
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function updateFollowRequests(Request $request, $id)
    {
        //get user
        $user = Auth::guard(Auth::user()->token()->name . '-api')->user();

        $follow_request = $user->followers()
            ->whereHas('follower')
            ->where('status', 'pending')
            ->where('id', $id)
            ->first();

        if (!$follow_request) {
            return $this->apiBadRequestResponse(__('api/shared/followings/followings.wrong-request-id'));
        }

        $data = $request->only([
            'status',
        ]);

        $this->apiValidate($data, [
            'status' => ['required', 'in:accept,remove,deny'],
        ]);

        DB::beginTransaction();
        try {
            if ($data['status'] === 'remove') {
                $follow_request->delete();
                $status = 'removed';
                $message = __('api/shared/followings/followings.request-removed');
            } else {
                if ($data['status'] === 'accept') {
                    $status = 'approved';
                    $message = __('api/shared/followings/followings.request-accepted');
                } else {
                    $status = 'declined';
                    $message = __('api/shared/followings/followings.request-declined');
                }

                $follow_request->update([
                    'status' => $status,
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/shared/followings/followings.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => $message,
            'data' => [
                'status' => $status,
            ]
        ]);
    }
}
