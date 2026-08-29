<?php

namespace App\Helpers;

use App\Helpers\Categories\CategoriesFilter;
use App\Helpers\FCM\FcmHelper;
use App\Helpers\Geography\Geography;
use App\Models\Offers\Offer;
use App\Models\Posts\Post;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\Categories\AdvertiserInterests;
use App\Models\Users\Advertisers\Locations\AdvertiserPreferredCity;
use App\Models\Users\Advertisers\Locations\AdvertiserPreferredGovernorate;
use App\Models\Users\Customers\Categories\CustomerCategories;
use App\Models\Users\Customers\CustomerUser;
use App\Models\Users\Customers\Locations\CustomerPreferredCity;
use App\Models\Users\Customers\Locations\CustomerPreferredGovernorate;
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
                        $fcmData = self::buildFcmMessage($item, $type, $message, $customProperties);

                        FcmHelper::sendFcmNotification($fcmData, [$item->fcm_token], $customProperties);
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
                    $fcmData = self::buildFcmMessage($user, $type, $message, $customProperties);

                    FcmHelper::sendFcmNotification($fcmData, [$user->fcm_token], $customProperties);
                }
            }
        }
    }

    /**
     * Build the Arabic + English title/body for a notification, regardless of the
     * app's currently-active locale (which reflects the triggering request, not the
     * recipient's own `notify_language`).
     *
     * @param AdvertiserUser|CustomerUser $recipient
     * @param array|null $customProperties
     * @return array{title: string, title_en: string, body: string, body_en: string}
     */
    private static function buildFcmMessage($recipient, string $type, string $message, ?array $customProperties): array
    {
        $result = [];

        foreach (['ar' => 'title', 'en' => 'title_en'] as $locale => $key) {
            $result[$key] = trans("api/notifications/notifications.{$type}.title", [], $locale);
        }

        foreach (['ar' => 'body', 'en' => 'body_en'] as $locale => $key) {
            if ($type === 'admin.notification') {
                $result[$key] = $message;
                continue;
            }

            $userData = null;
            if (isset($customProperties['userId'])) {
                $userData = ($customProperties['userType'] ?? null) === 'advertiser'
                    ? AdvertiserUser::where('id', $customProperties['userId'])->first()
                    : CustomerUser::where('id', $customProperties['userId'])->first();
            }

            if ($message === 'posts.comment_add_subscription' && isset($customProperties['postId'])) {
                $post = Post::where('id', $customProperties['postId'])->first();
                $result[$key] = trans("api/notifications/notifications.{$message}", [
                    'name' => $userData->name ?? $recipient->name,
                    'owner' => $post->user->name ?? '',
                ], $locale);
            } else {
                $result[$key] = trans("api/notifications/notifications.{$message}", [
                    'name' => $userData->name ?? $recipient->name,
                ], $locale);
            }
        }

        return $result;
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
     * Notify users from an admin-triggered action, persisting the notification and
     * updating the Firestore unread-count, then pushing an FCM notification to any
     * user with a registered token. Centralizes what was previously duplicated
     * ad-hoc (a separate `foreach` + `FcmHelper::sendFcmNotification` loop) at every
     * call site.
     *
     * Callers may pass `title`/`title_en`/`body_en`/`image` inside $customProperties
     * to override the defaults; otherwise the title falls back to a translated
     * "{$type}.title" lookup and the English body falls back to the Arabic $message.
     *
     * The return value counts users actually notified (a DB notification record was
     * created for them) — NOT how many received an FCM push. Most users won't have a
     * registered device token at any given moment (logged out, token expired, web-only
     * account, etc.) and that is expected, not a failure: the in-app notification still
     * lands either way, so callers checking "did this send at all" should check this
     * count, not push-delivery success.
     *
     * @param AdvertiserUser|CustomerUser|Collection $users
     * @param string $type
     * @param string $message
     * @param string $action
     * @param array|null $customProperties
     * @return int number of users actually notified
     */
    public static function sendFromAdmin($users, string $type, string $message, string $action, array $customProperties = null): int
    {
        $sentCount = 0;

        $title = $customProperties['title'] ?? trans("api/notifications/notifications.{$type}.title", [], 'ar');
        $titleEn = $customProperties['title_en'] ?? trans("api/notifications/notifications.{$type}.title", [], 'en');
        $bodyEn = $customProperties['body_en'] ?? $message;
        $image = $customProperties['image'] ?? null;

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

            if ($user->fcm_token) {
                FcmHelper::sendFcmNotification([
                    'title' => $title,
                    'title_en' => $titleEn,
                    'body' => $message,
                    'body_en' => $bodyEn,
                    'image' => $image,
                ], [$user->fcm_token], $customProperties);
            }

            $sentCount++;
        }

        return $sentCount;
    }

    /**
     * Notify customers/advertisers interested in a newly-approved post: those who
     * follow its category (expanded to parent/child, matching CategoriesFilter's own
     * convention) AND either have no saved location preference or prefer the post's
     * own governorate/city (falling back to the advertiser's location when the post
     * has none — legacy rows). The author is never notified about their own post.
     *
     * @param Post $post
     * @return void
     */
    public static function notifyInterestedUsersForPost(Post $post): void
    {
        $advertiser = $post->advertiser;
        if (!$advertiser || !$post->category_id) {
            return;
        }

        $categoryIds = CategoriesFilter::categoryAndAncestorIds($post->category_id);

        $customerIds = CustomerCategories::whereIn('category_id', $categoryIds)->pluck('customer_id');
        $advertiserIds = AdvertiserInterests::whereIn('category_id', $categoryIds)->pluck('advertiser_id')
            ->diff([$advertiser->id]);

        $governorateId = $post->governorate_id ?? $advertiser->governorate_id;
        $cityId = $post->city_id ?? $advertiser->city_id;

        $customerIds = Geography::candidatesInterestedInLocation($customerIds, CustomerPreferredGovernorate::class, CustomerPreferredCity::class, 'customer_id', $governorateId, $cityId);
        $advertiserIds = Geography::candidatesInterestedInLocation($advertiserIds, AdvertiserPreferredGovernorate::class, AdvertiserPreferredCity::class, 'advertiser_id', $governorateId, $cityId);

        $users = CustomerUser::whereIn('id', $customerIds)->get();
        $advertisers = AdvertiserUser::whereIn('id', $advertiserIds)->get();
        $name = $advertiser->name;

        $customProperties = [
            'title' => " منشور جديد - $name",
            'title_en' => " منشور جديد - $name",
            'body_en' => $post->content,
            'notify_link' => null,
            'postId' => $post->id,
            'userId' => $advertiser->id,
            'type' => 'posts',
            'userType' => 'advertiser',
            'customProperties' => [
                'postId' => $post->id,
                'type' => 'posts',
            ],
        ];

        self::sendFromAdmin($users, 'posts', $post->content, 'add', $customProperties);
        self::sendFromAdmin($advertisers, 'posts', $post->content, 'add', $customProperties);
    }

    /**
     * Notify customers/advertisers interested in a newly-approved offer. Same
     * targeting as notifyInterestedUsersForPost(), except offers carry no location
     * of their own — the advertiser's own governorate/city is used directly.
     *
     * @param Offer $offer
     * @return void
     */
    public static function notifyInterestedUsersForOffer(Offer $offer): void
    {
        $advertiser = $offer->advertiser;
        if (!$advertiser || !$offer->category_id) {
            return;
        }

        $categoryIds = CategoriesFilter::categoryAndAncestorIds($offer->category_id);

        $customerIds = CustomerCategories::whereIn('category_id', $categoryIds)->pluck('customer_id');
        $advertiserIds = AdvertiserInterests::whereIn('category_id', $categoryIds)->pluck('advertiser_id')
            ->diff([$advertiser->id]);

        $governorateId = $advertiser->governorate_id;
        $cityId = $advertiser->city_id;

        $customerIds = Geography::candidatesInterestedInLocation($customerIds, CustomerPreferredGovernorate::class, CustomerPreferredCity::class, 'customer_id', $governorateId, $cityId);
        $advertiserIds = Geography::candidatesInterestedInLocation($advertiserIds, AdvertiserPreferredGovernorate::class, AdvertiserPreferredCity::class, 'advertiser_id', $governorateId, $cityId);

        $users = CustomerUser::whereIn('id', $customerIds)->get();
        $advertisers = AdvertiserUser::whereIn('id', $advertiserIds)->get();
        $name = $advertiser->name;

        $customProperties = [
            'title' => " اعلان جديد - $name",
            'title_en' => " اعلان جديد - $name",
            'body_en' => $offer->content,
            'notify_link' => null,
            'offerId' => $offer->id,
            'type' => 'offers',
            'message' => 'offers.add',
            'userId' => $advertiser->id,
            'userType' => 'advertiser',
            'customProperties' => [
                'offerId' => $offer->id,
                'type' => 'offers',
            ],
        ];

        self::sendFromAdmin($users, 'offers', $offer->content, 'add', $customProperties);
        self::sendFromAdmin($advertisers, 'offers', $offer->content, 'add', $customProperties);
    }
}
