<?php

namespace App\Http\Resources\Advertisers\Subscriptions\Packages;

use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class PackagesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        //get name column to show countries, cities in current user language
        $name = (App::currentLocale() === 'ar') ? 'name_ar' : 'name_en';
        $description = (App::currentLocale() === 'ar') ? 'description_ar' : 'description_en';
        $specifications = (App::currentLocale() === 'ar') ? 'specifications_ar' : 'specifications_en';

        $is_subscribed = Auth::guard('advertiser-api')->user()
            ->packages()
            ->where('package_id', $this->id)
            ->where('is_current', true)
            ->where('is_active', true)
            ->where(function ($q) {
                return $q->where('ends_at', '>', now())
                    ->orWhere('ends_at', null);
            })
            ->first();
        $currency = __("api/advertisers/subscriptions/packages/packages.currencies.$this->currency");
        $subscription_type = __("api/advertisers/subscriptions/packages/packages.subscription_types.$this->subscription_type");

        $ends_at = $is_subscribed ? $is_subscribed->ends_at : null;
        return [
            'id' => $this->id,
            'productId' => $this->product_id,
            'name' => $this->{$name},
            'description' => $this->{$description},
            'specifications' => $this->{$specifications},
            'maximumPosts' => $this->maximum_posts,
            'price' => $this->price,
            'oldPrice' => $this->old_price,
            'duration' => $this->duration,
            'subscriptionType' => $subscription_type,
            'currency' => $currency,
            'isSubscribed' => (bool)$is_subscribed,
            'isTrial' => (bool)$this->is_trial,
            'endsAt' => $ends_at ? Carbon::make($ends_at)->locale(App::currentLocale())->translatedFormat('d F Y') : null,
        ];
    }
}
