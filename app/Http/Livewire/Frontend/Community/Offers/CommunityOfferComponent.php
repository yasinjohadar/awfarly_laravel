<?php

namespace App\Http\Livewire\Frontend\Community\Offers;

use App\Helpers\Files;
use App\Helpers\Settings;
use App\Http\Resources\Media\MediaResource;
use App\Models\Offers\Offer;
use App\Models\Posts\Post;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class CommunityOfferComponent extends Component
{
    public int $offer_id;
    public string $name_column;

    public function render()
    {
        //get language column to show
        $this->name_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        $offer = Offer::where('id', $this->offer_id)
            ->first();
        //Get image if exists
        if (!is_null($offer->advertiser->image) && !empty($offer->advertiser->image) && $offer->advertiser->image != null) {
            $storagePath = route('files.image.get', $offer->advertiser->image);
        } else {
            //Set default image
            $storagePath = Settings::Logo('assets/images/frontend/logo.png');
        }
        //get user rate
        $rate = $offer->advertiser->rate ?? null;
        $offer = [
            'id' => $offer->id,
            'content' => $offer->content,
            'categoryId' => $offer->category_id ?? null,
            'websiteUrl' => route('offer.index', ['id' => $offer->id]),
            'media' => MediaResource::collection($offer->getMedia('offers'))->resolve(),
            'salePercentage' => $offer->sale_percentage ?? null,
            'advertisementUrl' => $offer->advertisement_url ?? null,
            'rate' => $offer->rate ?? null,
            'expiresInDays' => $offer->expires_at ? Carbon::make($offer->expires_at)->diffInDays(now()) : null,
            'expiresAt' => $offer->expires_at ? CarbonImmutable::make($offer->expires_at)->locale(App::currentLocale())->calendar() : null,
            'isExpired' => ($offer->expires_at && Carbon::make($offer->expires_at)->isPast()),
            'statistics' => [
                'views' => $offer->views_count ?? 0,
                'likes' => $offer->likes_count ?? 0,
                'comments' => $offer->comments_count ?? 0,
            ],
            'owner' => [
                'id' => $offer->advertiser->id,
                'username' => $offer->advertiser->username,
                'bio' => $offer->advertiser->bio ?? null,
                'name' => $offer->advertiser->name,
                'businessTypeName' => $offer->advertiser->business_type ? $offer->advertiser->business->{$this->name_column} : null,
                'imageUrl' => $storagePath,
                'type' => $offer->advertiser->user_type,
                'country' => $offer->advertiser->country->{$this->name_column},
                'city' => $offer->advertiser->city->{$this->name_column},
                'rate' => $rate,
                'isElite' => (bool)$offer->advertiser->is_elite,
                'chatStatus' => $offer->advertiser->chats_privacy,
                'profilePrivacy' => $offer->advertiser->profile_privacy,
                'isSelf' => false,
                'isOnline' => $offer->advertiser->is_online,
            ],
            'createdAt' => $offer->created_at ? Carbon::make($offer->created_at)->diffForHumans() : null,
        ];

        return view('livewire.frontend.community.offers.offer', ['offer' => $offer]);
    }
}
