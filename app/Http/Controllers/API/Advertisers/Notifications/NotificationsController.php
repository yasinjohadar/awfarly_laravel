<?php

namespace App\Http\Controllers\API\Advertisers\Notifications;

use App\Helpers\FCM\FcmHelper;
use App\Helpers\Notifications;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Notifications\NotificationsResource;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kreait\Laravel\Firebase\Facades\Firebase;

class NotificationsController extends Controller
{
    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getNotifications(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('proposals.pagination.limit', 10);

        $notifications = Auth::guard('advertiser-api')->user()
            ->notifications()
            ->with('notifiable')
            ->orderBy('id', 'desc')
            ->orderBy('read_at');

        if ($request->has('isUnread') && !is_null($request->get('isUnread'))) {
            if ($request->get('isUnread')) {
                $notifications = $notifications->whereNull('read_at');
            } else {
                $notifications = $notifications->whereNotNull('read_at');
            }
        }

        $notifications = $notifications->paginate($limit);
        try {
            return $this->apiPaginateResponse(NotificationsResource::collection($notifications));
        } finally {
            //read all notifications
            $this->makeAllRead();
        }
    }

    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function getNotificationById($id)
    {
        $notification = Auth::guard('advertiser-api')->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return $this->apiBadRequestResponse(__('api/notifications/notifications.wrong-id'));
        }

        return $this->apiResponse([
            'data' => NotificationsResource::make($notification),
        ]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'title'         =>  'required|string|min:3',
            'description'   =>  'required|string|min:3',
            'country_code'  =>  'required|string',
            'governorate_id'=>  'sometimes|nullable|integer|exists:governorates,id',
            'city_id'       =>  'sometimes|nullable|integer|exists:cities,id',
            'age_from'       =>  'sometimes|nullable|integer',
            'age_to'       =>  'sometimes|nullable|integer',
            'distance_in_meter'       =>  'sometimes|nullable|integer',
            'gender'       =>  'sometimes|nullable|string|in:male,female',
            'language'       =>  'sometimes|nullable|string|in:en,ar',
        ]);
        $user = Auth::guard('advertiser-api')->user();

        $has_points = $user->balance >= 10;

        if(!$has_points) return $this->apiResponse([
            'message'   =>  'you dont have enough points to send notifications'
        ]);

        if((!$user->address_latitude || !$user->address_longitude) ) return $this->apiResponse([
            'message'   =>  'يرجي اضافة الموقع الحالي حتي تتمكن من ارسال اشعارات .'
        ]);
        if((isset($request->distance_in_meter) && $request->distance_in_meter < 1000)) return $this->apiResponse([
            'message'   =>  'يرجي تحديد الموقع  حتي تتمكن من ارسال اشعارات .'
        ]);

        $users = CustomerUser::when($request->country_code,fn($q)=>$q->where('country_code',$request->country_code))
            ->when($request->city_id,fn($q)=>$q->where('city_id',$request->city_id))
            ->when($request->governorate_id && !$request->city_id,fn($q)=>$q->where('governorate_id',$request->governorate_id))
            ->when($request->gender,fn($q)=>$q->where('gender',$request->gender))
//            ->when($request->language,fn($q)=>$q->where('notify_language',$request->language))
            ->when($request->distance_in_meter,fn($q)=>$q->whereDistance('location', optional($user)->location, '<=', $request->distance_in_meter * 1000))
            ->when($request->age_from && $request->age_to,fn($q)=>$q->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN ? AND ?", [$request->age_from, $request->age_to]))
            ->get();

        $customProperties = [
            'title'             => $request->title,
            'title_en'         => $request->title,
            'body'              => $request->description,
            'body_en'           => $request->description,
            'postId'                => $request->id,
            'offerId'                => $request->id,
            'type'              => $request->type,
            'notify_link'       => null,
        ];

        Notifications::sendFromAdmin($users, $request->type ?? 'admin.notification', $request->description, 'add', $customProperties);
        foreach($users->pluck('fcm_token')->toArray() as $token){

            $is_notifications_sent = FcmHelper::sendFcmNotification($customProperties, [$token]);
        }

        $advertisers = AdvertiserUser::when($request->country_code,fn($q)=>$q->where('country_code',$request->country_code))
            ->when($request->city_id,fn($q)=>$q->where('city_id',$request->city_id))
            ->when($request->governorate_id && !$request->city_id,fn($q)=>$q->where('governorate_id',$request->governorate_id))
            ->when($request->gender,fn($q)=>$q->where('gender',$request->gender))
//            ->when($request->language,fn($q)=>$q->where('notify_language',$request->language))
            ->when($request->distance_in_meter,fn($q)=>$q->whereDistance('location', optional(auth()->user())->location, '<=', $request->distance_in_meter))
            ->when($request->age_from && $request->age_to,fn($q)=>$q->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN ? AND ?", [$request->age_from, $request->age_to]))
            ->get();

        Notifications::sendFromAdmin($advertisers, $request->type ?? 'admin.notification', $request->description, 'add', $customProperties);
        foreach($advertisers->pluck('fcm_token')->toArray() as $token){

            $is_notifications_sent = FcmHelper::sendFcmNotification($customProperties, [$token]);
        }


        $user->withdraw(10);

        return $this->apiResponse([
            'message'   =>  'done'
        ]);

    }
    /**
     * @return Application|ResponseFactory|Response
     */
    public function makeAllRead()
    {
        $user = Auth::guard('advertiser-api')->user();
        DB::beginTransaction();
        try {
            $user->unreadNotifications
                ->markAsRead();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/notifications/notifications.something-wrong'));
        }
        DB::commit();

        try {
            return $this->apiResponse([
                'message' => __('api/notifications/notifications.read'),
            ]);
        } finally {
            $count = $user->unreadNotifications()
                ->count();

            Notifications::setNotificationsCount($user, $count);
        }
    }

    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function deleteNotificationById($id)
    {
        $notification = Auth::guard('advertiser-api')->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return $this->apiBadRequestResponse(__('api/notifications/notifications.wrong-id'));
        }
        DB::beginTransaction();
        try {

            $notification->delete();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/notifications/notifications.something-wrong'));
        }
        DB::commit();
        try {
            return $this->apiResponse([
                'message' => __('api/notifications/notifications.notification-deleted'),
            ]);
        } finally {
            $count = Auth::guard('advertiser-api')->user()
                ->unreadNotifications()
                ->count();

            Notifications::setNotificationsCount(Auth::guard('advertiser-api')->user(), $count);
        }
    }
}
