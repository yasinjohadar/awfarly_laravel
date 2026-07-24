<?php

namespace App\Helpers;

use App\Helpers\FCM\FcmHelper;
use App\Models\Posts\Post;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use App\Notifications\Community\CommunityNotifications;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Kreait\Laravel\Firebase\Facades\Firebase;

class Notifications
{
    /**
     * @param AdvertiserUser|CustomerUser|Collection|Authenticatable $user
     * @param string $type
     * @param string $message
     * @param string $action
     * @param array|null $customProperties
     */
    public static function sendForCommunity($user, string $type, string $message, string $action, array $customProperties = null)
    {
        if (is_a($user, Collection::class)) {
            foreach ($user as $item) {
                $item->notify(new CommunityNotifications([
                        'type' => $type,
                        'message' => $message,
                        'action' => $action,
                        'customProperties' => $customProperties,
                    ])
                );
                try {
                    $data = $item->unreadNotifications()->count();

                    $firebase = Firebase::firestore()
                        ->database();

                    $firebase->collection('notifications')
                        ->document("{$item->user_type}.{$item->id}")
                        ->set([
                            'count' => $data,
                        ]);

                } catch (Exception $e) {

                } finally {
                    if (!$item->is_online && $item->fcm_token && $type !== 'chats') {
                        if ($type === 'admin.notification') {
                            $fcm_message = $message;
                        } else {
                            if ($customProperties['userType'] === 'advertiser') {
                                $user_data = AdvertiserUser::where('id', $customProperties['userId'])
                                    ->first();
                            } else {
                                $user_data = CustomerUser::where('id', $customProperties['userId'])
                                    ->first();
                            }
                            if ($message === 'posts.comment_add_subscription' && isset($customProperties['postId'])) {
                                $post = Post::where('id', $customProperties['postId'])
                                    ->first();
                                $fcm_message = __("api/notifications/notifications.{$message}", ['name' => $user_data->name, 'owner' => $post->user->name]);
                            } else {
                                $fcm_message = __("api/notifications/notifications.{$message}", ['name' => $item->name]);
                            }
                        }
                        FcmHelper::sendFcmNotification([
                            'title' => __("api/notifications/notification.{$type}.title"),
                            'body' => $fcm_message,
                        ], [$item->fcm_token], $customProperties);
                    }
                }
            }
        } else {
            $user->notify(new CommunityNotifications([
                    'type' => $type,
                    'message' => $message,
                    'action' => $action,
                    'customProperties' => $customProperties,
                ])
            );

            try {
                $data = $user->unreadNotifications()->count();

                $firebase = Firebase::firestore()
                    ->database();

                $firebase->collection('notifications')
                    ->document("{$user->user_type}.{$user->id}")
                    ->set([
                        'count' => $data,
                    ]);

            } catch (Exception $e) {

            } finally {
                if (!$user->is_online && $user->fcm_token && $type !== 'chats') {
                    if ($type === 'admin.notification') {
                        $fcm_message = $message;
                    } else {
                        if ($customProperties['userType'] === 'advertiser') {
                            $user_data = AdvertiserUser::where('id', $customProperties['userId'])
                                ->first();
                        } else {
                            $user_data = CustomerUser::where('id', $customProperties['userId'])
                                ->first();
                        }
                        if ($message === 'posts.comment_add_subscription' && isset($customProperties['postId'])) {
                            $post = Post::where('id', $customProperties['postId'])
                                ->first();
                            $fcm_message = __("api/notifications/notifications.{$message}", ['name' => $user_data->name, 'owner' => $post->user->name]);
                        } else {
                            $fcm_message = __("api/notifications/notifications.{$message}", ['name' => $user_data->name]);
                        }
                    }
                    FcmHelper::sendFcmNotification([
                        'title' => __("api/notifications/notifications.{$type}.title"),
                        'body' => $fcm_message,
                    ], [$user->fcm_token], $customProperties);
                }
            }
        }
    }

    /**
     * @param $user
     * @param $count
     */
    public static function setNotificationsCount($user, $count)
    {
        try {

            $firebase = Firebase::firestore()
                ->database();

            $firebase->collection('notifications')
                ->document("{$user->user_type}.{$user->id}")
                ->set([
                    'count' => $count,
                ]);

        } catch (Exception $e) {

        }
    }

    /**
     * @param AdvertiserUser|CustomerUser|Collection $users
     * @param string $type
     * @param string $message
     * @param string $action
     * @param array|null $customProperties
     */
    public static function sendFromAdmin($users, string $type, string $message, string $action, array $customProperties = null)
    {
        foreach ($users as $user) {
            $user->notify(new CommunityNotifications([
                    'type' => $type,
                    'message' => $message,
                    'action' => $action,
                    'customProperties' => $customProperties,
                ])
            );

            try {
                $data = $user->unreadNotifications()->count();

                $firebase = Firebase::firestore()
                    ->database();

                $firebase->collection('notifications')
                    ->document("{$user->user_type}.{$user->id}")
                    ->set([
                        'count' => $data,
                    ]);

            } catch (Exception $e) {

            }
        }
    }
}
