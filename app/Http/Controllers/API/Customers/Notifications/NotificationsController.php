<?php

namespace App\Http\Controllers\API\Customers\Notifications;

use App\Helpers\Notifications;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Notifications\NotificationsResource;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $notifications = Auth::guard('customer-api')->user()
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
        $notification = Auth::guard('customer-api')->user()
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

    /**
     * @return Application|ResponseFactory|Response
     */
    public function makeAllRead()
    {
        $user = Auth::guard('customer-api')->user();
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
        $notification = Auth::guard('customer-api')->user()
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
            $count = Auth::guard('customer-api')->user()
                ->unreadNotifications()
                ->count();

            Notifications::setNotificationsCount(Auth::guard('customer-api')->user(), $count);
        }
    }
}
