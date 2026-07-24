<?php

namespace App\Helpers\FCM;

use App\Helpers\Files;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmHelper
{
    /**
     * Send message
     *
     * @param $data
     * @param $fcm_tokens
     * @param null $customProperties
     * @return bool
     */
    public static function sendFcmNotification($data, $fcm_tokens, $customProperties = null): bool
    {
        try {
            $messaging = app('firebase.messaging');

            $notification = Notification::fromArray([
                "title" => $data['title'] ?? null,
                "title_en" => $data['title_en'] ?? null,
                "body" => $data['body'] ?? null,
                "body_en" => $data['body_en'] ?? null,
                "image" => $data['image'] ?? null,
            ]);
            $newData = [
                "title" => $data['title'] ?? null,
                "content" => $data['body'] ?? null,
            ];

            if ($customProperties) {
                if (isset($customProperties['chatToken'])) {
                    $newData['chatToken'] = $customProperties['chatToken'];
                }
                if (isset($customProperties['postId'])) {
                    $newData['postId'] = $customProperties['postId'];
                }
                if (isset($customProperties['commentId'])) {
                    $newData['commentId'] = $customProperties['commentId'];
                }
                if (isset($customProperties['offerId'])) {
                    $newData['offerId'] = $customProperties['offerId'];
                }
                if (isset($customProperties['status'])) {
                    $newData['status'] = $customProperties['status'];
                }
                if (isset($customProperties['type'])) {
                    $newData['type'] = $customProperties['type'];
                }
                if (isset($customProperties['action'])) {
                    $newData['action'] = $customProperties['action'];
                }
            }
            foreach ($fcm_tokens as $token) {
                if(!$token) continue;
                $user = AdvertiserUser::where('fcm_token', $token)
                    ->where('status', 'active')
                    ->first();
                if (!$user) {
                    $user = CustomerUser::where('fcm_token', $token)
                        ->where('status', 'active')
                        ->first();
                    if (!$user) {
                        continue;
                    }
                }

                $badgesCount = $user->unreadNotifications()->count();
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification($notification)
                    ->withData($newData)
                    ->withHighestPossiblePriority()
                    ->withDefaultSounds()
                    ->withAndroidConfig(AndroidConfig::fromArray([
                        'priority' => 'high',
                        'notification' => [
                            'title' => $user->notify_language == 'ar' ? $data['title'] : $data['title_en'],
                            'body' => $user->notify_language == 'ar' ? $data['body'] : $data['body_en'],
                            'notification_count' => $badgesCount,
                            'sound' => 'default',
                            "click_action"=> "FLUTTER_NOTIFICATION_CLICK"

                        ]
                    ]))
                    ->withApnsConfig(ApnsConfig::fromArray([
                        'headers' => [
                            'apns-priority' => '10'
                        ],
                        'payload' => [
                            'alert' => [
                                'title' => $user->notify_language == 'ar' ? $data['title'] : $data['title_en'],
                                'body' => $user->notify_language == 'ar' ? $data['body'] : $data['body_en'],
                            ],
                            'aps' => [
                                'sound' => 'default',
                                'badge' => $badgesCount,
                            ]
                        ]
                    ]));

                $messaging->send($message);
            }
        } catch (MessagingException | FirebaseException $e) {
        }
        return true;
    }
}
