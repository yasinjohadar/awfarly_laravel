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

        //check whether user is elite or not
        if ($this->is_elite) {
            //return maximum posts quantity
            $maximum_posts = $current_pack ? $current_pack->maximum_posts : Settings::Get('user.allowed.posts', 10);
        } else {
            //return maximum posts quantity
            $maximum_posts = Settings::Get('user.allowed.posts', 10);
        }

        //check user posts count
        $user_posts = Auth::guard('advertiser-api')->user()
            ->posts()
            ->count();

        $userCategories = $this->categories()
            ->whereHas('category')
            ->get()
            ->map(function ($category) {
                return $category->category;
            });

        $has_categories = $this->categories()
                ->whereHas('category')
                ->count() > 0;

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
                'maximumPosts' => $maximum_posts,
                'leftPosts' => $this->allowed_posts_count ?? Settings::Get('user.allowed.posts', 10),
                'activeOffers' => $limits['activeCount'],
                'maximumActiveOffers' => $limits['activeLimit'],
                'monthlyOffers' => $limits['monthlyCount'],
                'maximumMonthlyOffers' => $limits['monthlyLimit'],
                'leftMonthlyOffers' => max(0, $limits['monthlyLimit'] - $limits['monthlyCount']),
            ],
            'interestedCategories' => CategoriesResource::collection($userCategories),
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
