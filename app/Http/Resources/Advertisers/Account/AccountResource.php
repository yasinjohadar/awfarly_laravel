<?php

namespace App\Http\Resources\Advertisers\Account;

use App\Helpers\Files;
use App\Helpers\Advertisers\OfferLimits;
use App\Helpers\Settings;
use App\Http\Resources\Advertisers\BusinessTypes\BusinessTypesResource;
use App\Http\Resources\Advertisers\Subscriptions\Packages\PackagesResource;
use App\Http\Resources\Categories\CategoriesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        //get language column to show
        $language_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';


        //get followers count
        $followers_count = $this->followers()
            ->get()
            ->count();

        //get followed users count
        $followed_count = $this->followed()
            ->get()
            ->count();

        //get user package
        $package = $this->packages()
            ->where('is_current', true)
            ->where('is_active', true)
            ->where('is_ended', false)
            ->where('ends_at', '>', now())
            ->first();

        $allowed_posts_count = Auth::guard('advertiser-api')->user()->allowed_posts_count;

        if ($package) {
            $current_pack = $package->package;
        } else {
            $current_pack = null;
        }

        //check user posts count
        $user_posts = Auth::guard('advertiser-api')->user()
            ->posts()
            ->count();

        //the actual quota enforced when creating a post (see
        //CommunityPostsController::store) is allowed_posts_count, a running
        //counter decremented per post — not the live package/settings value,
        //which can change after the counter was assigned and would otherwise
        //make "left" bigger than "maximum" in the UI.
        if ($this->allowed_posts_count !== null) {
            $left_posts = $this->allowed_posts_count;
        } elseif ($this->is_elite) {
            $left_posts = $current_pack ? $current_pack->maximum_posts : Settings::Get('user.allowed.posts', 10);
        } else {
            $left_posts = Settings::Get('user.allowed.posts', 10);
        }

        //the advertiser's OWN business categories: what they publish under
        $userCategories = $this->categories()
            ->whereHas('category')
            ->get()
            ->map(function ($category) {
                return $category->category;
            });

        //the categories they follow, which filter their own feed
        $userInterests = $this->interests()
            ->whereHas('category')
            ->get()
            ->map(function ($interest) {
                return $interest->category;
            });

        //profile completeness depends on having a business category, never on
        //interests — an advertiser with no interests can still publish.
        //A business type without categories (e.g. "Shopper") can never own
        //one, so it's exempt from this requirement entirely.
        $requiresOwnCategory = (bool) $this->business->has_categories;
        $has_categories = !$requiresOwnCategory || $userCategories->isNotEmpty();

        //check maximum allowed offers for advertiser (active + monthly)
        $limits = OfferLimits::evaluate(Auth::guard('advertiser-api')->user());
        $isAllowAddOffer = $limits['allowed'];
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'type' => 'advertiser',
            'businessTypeId' => $this->business->id,
            'businessTypeName' => $this->business->{$language_column},
            //whether this business type owns categories at all. "Shopper"
            //advertisers publish across every category, so they have no own
            //set. The app used to infer this by comparing the TRANSLATED name
            //against "Shopper", which breaks in any other locale.
            'businessTypeHasCategories' => (bool) $this->business->has_categories,
            'imageUrl' => $this->image ? route('files.image.get', $this->image) : null,
            'bio' => $this->bio ?? null,
            'birth_date' => optional($this->birth_date)->format('d/m/Y') ?? null,
            'gender' => $this->gender ?? null,
            'country' => $this->country->{$language_column} ?? null,
            'governorate' => $this->governorate->{$language_column} ?? null,
            'city' => $this->city->{$language_column} ?? null,
            'countryCode' => $this->country_code ?? null,
            'governorateId' => $this->governorate_id ?? null,
            'cityId' => $this->city_id ?? null,
            'language' => [
                'id' => $this->language->id,
                'name' => $this->language->name,
                'code' => $this->language->code,
            ],
            'rate' => $this->rate ?? null,
            'socialAccounts' => [
                'contactNumber' => $this->contact_number ?? null,
                'whatsappNumber' => $this->whatsapp_number ?? null,
                'facebookUrl' => $this->facebook_url ?? null,
                'twitterUrl' => $this->twitter_url ?? null,
                'websiteUrl' => $this->website_url ?? null,
            ],
            'statistics' => [
                'totalPosts' => $user_posts,
                'maximumPosts' => $user_posts + $left_posts,
                'leftPosts' => $left_posts,
                'activeOffers' => $limits['activeCount'],
                'maximumActiveOffers' => $limits['activeLimit'],
                'monthlyOffers' => $limits['monthlyCount'],
                'maximumMonthlyOffers' => $limits['monthlyLimit'],
                //null when the limit is switched off in the settings, so the
                //app hides that quota rather than drawing an empty bar
                'leftMonthlyOffers' => $limits['monthlyLimit'] === null
                    ? null
                    : max(0, $limits['monthlyLimit'] - $limits['monthlyCount']),
            ],
            //legacy key: for an advertiser this has always been the set the
            //post/offer category dropdown and the profile editor consume, i.e.
            //the OWN categories. Kept pointing there so installed builds keep
            //working; new builds should read ownCategories/interests instead.
            'interestedCategories' => CategoriesResource::collection($userCategories),
            'ownCategories' => CategoriesResource::collection($userCategories),
            'interests' => CategoriesResource::collection($userInterests),
            'isAllowAddOffer' => $isAllowAddOffer,
            'isAllowCreatePosts' => ((int) ($this->allowed_posts_count ?? 0)) > 0,
            'chatStatus' => $this->chats_privacy,
            'profilePrivacy' => $this->profile_privacy,
            'isFollowAllowed' => (bool)$this->isFollowAllowed,
            'isAcceptedSendNotifications' => (bool)$this->isAcceptedSendNotifications,
            'followersCount' => $followers_count ?? null,
            'followedCount' => $followed_count ?? null,
            'isElite' => (bool)$this->is_elite,
            'userPackage' => ($package && $package->package->is_visible) ? PackagesResource::make($package->package) : null,
            'isOnline' => $this->is_online,
            'discount_percentage' => $this->discount_percentage,
            'addressLatitude' => $this->address_latitude ?? null,
            'addressLongitude' => $this->address_longitude ?? null,
            'isProfileCompleted' => (bool)($this->username && $this->bio && $has_categories && $this->gender),
            'accountStatus' => $this->status,
            'points' => $this->balance,
            'isAllowEditName' => (bool)Settings::Get('allow.users.change.name', true),
            'allowed_posts_count'   =>  $this->allowed_posts_count,
            'allowed_offers_count'  =>  $this->allowed_offers_count,
            'fcm_token'  =>  $this->fcm_token,
        ];
    }
}
