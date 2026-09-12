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
use Illuminate\Support\Facades\Log;
use Kreait\Laravel\Firebase\Facades\Firebase;

class Notifications
{
    /**
     * @param AdvertiserUser|CustomerUser|Collection|Authenticatable $user
     * @param string $type
     * @param string $message
     * @param string $action
     * @param array|null $customProperties
     * @param bool $skipPush When true, the notification is still persisted (so it
     *                       shows in the in-app list) and the Firestore unread
     *                       count still updates (bell badge), but no FCM push is
     *                       sent regardless of online status — used for "someone
     *                       commented on a post/offer you're subscribed to"
     *                       broadcasts, which are too frequent/impersonal to
     *                       warrant interrupting the recipient.
     */
    public static function sendForCommunity($user, string $type, string $message, string $action, array $customProperties = null, bool $skipPush = false)
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
                    //silently-failing here means the in-app bell badge never updates for
                    //this user with zero visibility into why (e.g. the PHP grpc extension
                    //required by google/cloud-firestore missing on this server) — log it
                    //so a broken Firestore write is diagnosable instead of invisible.
                    Log::warning('Failed to update Firestore notification count', [
                        'user_type' => $item->user_type ?? null,
                        'user_id' => $item->id ?? null,
                        'error' => $e->getMessage(),
                    ]);
                } finally {
                    if (!$skipPush && !$item->is_online && $item->fcm_token && $type !== 'chats') {
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
                Log::warning('Failed to update Firestore notification count', [
                    'user_type' => $user->user_type ?? null,
                    'user_id' => $user->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            } finally {
                if (!$skipPush && !$user->is_online && $user->fcm_token && $type !== 'chats') {
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

        // The admin-configured site logo, so every push shows current branding
        // (e.g. after a rebrand) instead of whatever generic icon the client
        // app was originally built with — unless the caller already supplied
        // a more specific image (e.g. the reported post's own photo).
        $result['image'] = $customProperties['image'] ?? url(Settings::Logo());

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
            Log::warning('Failed to update Firestore notification count', [
                'user_type' => $user->user_type ?? null,
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);
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
     * Returns three counts, since "notified" and "actually pushed to a device"
     * are genuinely different things and conflating them hides real failures:
     * - notified: a DB notification record was created (this is the real
     *   "did this send at all" signal — most users won't have a registered
     *   device token at any given moment, and that's expected, not a failure).
     * - push_attempted: how many of those had a token, so a push was tried.
     * - push_delivered: how many of those attempts actually succeeded per
     *   FcmHelper. If push_attempted > 0 but push_delivered is far lower (or
     *   zero), that's a real delivery problem worth surfacing to the admin —
     *   this was previously invisible, since a successful DB write always
     *   made the send "look" successful even when every push silently failed.
     *
     * @param AdvertiserUser|CustomerUser|Collection $users
     * @param string $type
     * @param string $message
     * @param string $action
     * @param array|null $customProperties
     * @return array{notified: int, push_attempted: int, push_delivered: int, push_error_sample: string|null}
     */
    public static function sendFromAdmin($users, string $type, string $message, string $action, array $customProperties = null): array
    {
        $notifiedCount = 0;
        $pushAttempted = 0;
        $pushDelivered = 0;
        $pushErrorSample = null;

        $title = $customProperties['title'] ?? trans("api/notifications/notifications.{$type}.title", [], 'ar');
        $titleEn = $customProperties['title_en'] ?? trans("api/notifications/notifications.{$type}.title", [], 'en');
        $bodyEn = $customProperties['body_en'] ?? $message;
        // Same site-logo default as buildFcmMessage() — see its comment.
        $image = $customProperties['image'] ?? url(Settings::Logo());

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
                Log::warning('Failed to update Firestore notification count', [
                    'user_type' => $user->user_type ?? null,
                    'user_id' => $user->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($user->fcm_token) {
                $pushAttempted++;
                $pushError = null;
                $delivered = FcmHelper::sendFcmNotification([
                    'title' => $title,
                    'title_en' => $titleEn,
                    'body' => $message,
                    'body_en' => $bodyEn,
                    'image' => $image,
                ], [$user->fcm_token], $customProperties, $pushError);

                if ($delivered) {
                    $pushDelivered++;
                } elseif ($pushErrorSample === null && $pushError) {
                    $pushErrorSample = $pushError;
                }
            }

            $notifiedCount++;
        }

        return [
            'notified' => $notifiedCount,
            'push_attempted' => $pushAttempted,
            'push_delivered' => $pushDelivered,
            'push_error_sample' => $pushErrorSample,
        ];
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

        $customersByInterest = $customerIds->count();
        $advertisersByInterest = $advertiserIds->count();

        $governorateId = $advertiser->governorate_id;
        $cityId = $advertiser->city_id;

        $customerIds = Geography::candidatesInterestedInLocation($customerIds, CustomerPreferredGovernorate::class, CustomerPreferredCity::class, 'customer_id', $governorateId, $cityId);
        $advertiserIds = Geography::candidatesInterestedInLocation($advertiserIds, AdvertiserPreferredGovernorate::class, AdvertiserPreferredCity::class, 'advertiser_id', $governorateId, $cityId);

        /// Every stage of this fan-out was previously invisible: a recipient can be
        /// dropped by the category match or by the location match, and the counts
        /// sendFromAdmin() returns were discarded, so "the offer never reached me"
        /// could not be told apart from "it was sent and the push failed".
        Log::info('[offer-notify] fan-out', [
            'offer_id' => $offer->id,
            'offer_category_id' => $offer->category_id,
            'matched_category_ids' => $categoryIds,
            'customers_by_interest' => $customersByInterest,
            'advertisers_by_interest' => $advertisersByInterest,
            'advertiser_governorate_id' => $governorateId,
            'advertiser_city_id' => $cityId,
            'customers_after_location' => $customerIds->count(),
            'advertisers_after_location' => $advertiserIds->count(),
        ]);

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

        $customerResult = self::sendFromAdmin($users, 'offers', $offer->content, 'add', $customProperties);
        $advertiserResult = self::sendFromAdmin($advertisers, 'offers', $offer->content, 'add', $customProperties);

        Log::info('[offer-notify] sent', [
            'offer_id' => $offer->id,
            'customers' => $customerResult,
            'advertisers' => $advertiserResult,
        ]);
    }

    /**
     * Notify a post's own advertiser that the admin approved or declined a
     * (re-)review of their post — the review is triggered either by the admin's
     * own approve/reject action, or automatically because editing a post resets
     * it to `pending`. Never fired for the initial auto-approve-at-creation case,
     * since `updated()` (the only caller, via PostObserver) never fires on `create()`.
     *
     * `status` is also forwarded via customProperties so the Flutter app can tell
     * an approval apart from a decline (both share the existing `posts` type/icon
     * and navigate to the same post).
     *
     * @param Post $post
     * @return void
     */
    public static function notifyOwnerPostStatusChanged(Post $post): void
    {
        $advertiser = $post->advertiser;
        if (!$advertiser || !in_array($post->status, ['approved', 'unapproved'])) {
            return;
        }

        $key = $post->status === 'approved' ? 'approved' : 'declined';
        $message = trans("api/notifications/notifications.posts.{$key}", [], 'ar');
        $messageEn = trans("api/notifications/notifications.posts.{$key}", [], 'en');

        $customProperties = [
            'body_en' => $messageEn,
            'postId' => $post->id,
            'status' => $post->status,
        ];

        self::sendFromAdmin(collect([$advertiser]), 'posts', $message, 'view', $customProperties);
    }

    /**
     * Notify an offer's own advertiser that the admin approved or declined a
     * (re-)review of their offer. See notifyOwnerPostStatusChanged() for the
     * exact same reasoning (only fired by OfferObserver::updated()).
     *
     * @param Offer $offer
     * @return void
     */
    public static function notifyOwnerOfferStatusChanged(Offer $offer): void
    {
        $advertiser = $offer->advertiser;
        if (!$advertiser || !in_array($offer->status, ['approved', 'unapproved'])) {
            return;
        }

        $key = $offer->status === 'approved' ? 'approved' : 'declined';
        $message = trans("api/notifications/notifications.offers.{$key}", [], 'ar');
        $messageEn = trans("api/notifications/notifications.offers.{$key}", [], 'en');

        $customProperties = [
            'body_en' => $messageEn,
            'offerId' => $offer->id,
            'status' => $offer->status,
        ];

        self::sendFromAdmin(collect([$advertiser]), 'offers', $message, 'view', $customProperties);
    }

    /**
     * Notify a comment's author that someone replied to it. Unlike the generic
     * "someone commented on a post/offer you're subscribed to" broadcast (sent
     * via sendForCommunity() with $skipPush = true — too frequent/impersonal to
     * interrupt the recipient for), a direct reply is a real push regardless of
     * the recipient's online status: sendFromAdmin() has no online-status gate
     * at all, unlike sendForCommunity().
     *
     * @param AdvertiserUser|CustomerUser|null $recipient The parent comment's author
     * @param string $type 'posts.comments' or 'offers.comments'
     * @param string $commenterName
     * @param array $customProperties Same shape the caller already built for its
     *                                own comment notifications (postId/offerId,
     *                                commentId, userId/userType of the replier)
     * @return void
     */
    public static function notifyCommentReply($recipient, string $type, string $commenterName, array $customProperties): void
    {
        if (!$recipient) {
            return;
        }

        $key = $type === 'offers.comments' ? 'offers.comment_reply' : 'posts.comment_reply';
        $message = trans("api/notifications/notifications.{$key}", ['name' => $commenterName], 'ar');
        $messageEn = trans("api/notifications/notifications.{$key}", ['name' => $commenterName], 'en');

        $customProperties['body_en'] = $messageEn;

        self::sendFromAdmin(collect([$recipient]), $type, $message, 'add', $customProperties);
    }
}
