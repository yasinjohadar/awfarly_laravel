<?php

namespace App\Http\Resources\Customers\Account;

use App\Helpers\Files;
use App\Helpers\Settings;
use App\Http\Resources\Categories\CategoriesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
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

        $userCategories = $this->categories()
            ->whereHas('category')
            ->get()
            ->map(function ($category) {
                return $category->category;
            });
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'type' => 'customer',
            'imageUrl' => $this->image ? route('files.image.get', $this->image) : null,
            'bio' => $this->bio ?? null,
            'country' => $this->country->{$language_column} ?? null,
            'governorate' => $this->governorate->{$language_column} ?? null,
            'city' => $this->city->{$language_column} ?? null,
            'birth_date' => optional($this->birth_date)->format('d/m/Y') ?? null,
            'gender' => $this->gender ?? null,
            'countryCode' => $this->country_code ?? null,
            'governorateId' => $this->governorate_id ?? null,
            'cityId' => $this->city_id ?? null,
            'language' => [
                'id' => $this->language->id,
                'name' => $this->language->name,
                'code' => $this->language->code,
            ],
            'socialAccounts' => [
                'contactNumber' => $this->contact_number ?? null,
                'whatsappNumber' => $this->whatsapp_number ?? null,
                'facebookUrl' => $this->facebook_url ?? null,
                'twitterUrl' => $this->twitter_url ?? null,
                'websiteUrl' => $this->website_url ?? null,
            ],
            'interestedCategories' => CategoriesResource::collection($userCategories),
            'chatStatus' => $this->chats_privacy,
            'profilePrivacy' => $this->profile_privacy,
            'isFollowAllowed' => (bool)$this->isFollowAllowed,
            'followersCount' => $followers_count ?? null,
            'followedCount' => $followed_count ?? null,
            'isAcceptedSendNotifications' => (bool)$this->isAcceptedSendNotifications,
            'isOnline' => $this->is_online,
            'addressLatitude' => $this->address_latitude ?? null,
            'addressLongitude' => $this->address_longitude ?? null,
            'isProfileCompleted' => (bool)($this->username  && $this->location && $this->gender),
            'accountStatus' => $this->status,
            'fcm_token' => $this->fcm_token,
            'isAllowEditName' => (bool)Settings::Get('allow.users.change.name', true),
            'addressLatitude' => (optional($this->location)->latitude ?? null),
            'addressLongitude' =>(optional($this->location)->longitude ?? null),
        ];
    }
}
