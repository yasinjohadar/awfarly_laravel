<?php

namespace App\Http\Resources\Customers\Community\Proposals;

use App\Helpers\Files;
use App\Http\Resources\Media\MediaResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommunityProposalsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $is_expired = null;
        $expires_at = $this->expires_in ? Carbon::make($this->answered_at)->addDays($this->expires_in) : null;
        if ($expires_at) {
            $is_expired = $expires_at->copy()->isPast();
            $expires_at = $expires_at->diffForHumans();
        }

        //get name column to show countries, cities in current user language
        $name_column = (App::currentLocale() === 'ar') ? 'name_ar' : 'name_en';

        //get user rate
        $advertiser_rate = $this->advertiser->rate ?? 0;

        $user_rate = ($this->user->user_type === 'advertiser') ? ($this->advertiser->rate ?? 0) : null;

        if ($this->answered_at) {
            $expiresIn = Carbon::make($this->answered_at)->addDays($this->expires_in);
            $expires_in = $expiresIn->diffInDays(now(), false);
            $expires_in = $expires_in >= 0 ? 0 : abs($expires_in) + 1;
        } else {
            $expires_in = $this->expires_in ?? null;
        }//check whether this user is being followed or by the current logged user or not
        $followed = Auth::guard('customer-api')->user()
            ->followed()
            ->where('followed_type', $this->user->class)
            ->where('followed_id', $this->user->id)
            ->first();

        if ($followed) {
            $follow_status = $followed->status;
        } else {
            $follow_status = 'unfollowed';
        }
        return [
            'id' => $this->id,
            'content' => $this->content,
            'answer' => $this->answer ?? null,
            'media' => MediaResource::collection($this->getMedia('proposals')),
            'owner' => [
                'id' => $this->user->id,
                'type' => $this->user->user_type,
                'name' => $this->user->name,
                'username' => $this->user->username,
                'bio' => $this->user->bio ?? null,
                'businessTypeName' => $this->user->business_type ? $this->user->business->{$name_column} : null,
                'imageUrl' => route('files.image.get', $this->user->image) ?? null,
                'isFollowed' => (bool)$followed,
                'followStatus' => $follow_status,
                'country' => $this->user->country->{$name_column},
                'city' => $this->user->city->{$name_column},
                'rate' => $user_rate,
                'isElite' => (bool)$this->user->is_elite,
                'isOnline' => $this->user->is_online,
            ],
            'toUser' => [
                'id' => $this->advertiser->id,
                'type' => $this->advertiser->user_type,
                'name' => $this->advertiser->name,
                'username' => $this->advertiser->username,
                'bio' => $this->advertiser->bio ?? null,
                'businessTypeName' => $this->advertiser->business_type ? $this->advertiser->business->{$name_column} : null,
                'imageUrl' => route('files.image.get', $this->advertiser->image) ?? null,
                'country' => $this->advertiser->country->{$name_column},
                'city' => $this->advertiser->city->{$name_column},
                'rate' => $advertiser_rate,
                'isElite' => (bool)$this->advertiser->is_elite,
                'isOnline' => $this->advertiser->is_online,
            ],
            'expiresIn' => $expires_in ?? null,
            'expiresAt' => $expires_at,
            'isExpired' => $is_expired,
            'isOwner' => true,
            'isAllowAnswer' => false,
            'answeredAt' => $this->answered_at ? Carbon::make($this->answered_at)->diffForHumans() : null,
            'createdAt' => $this->created_at ? Carbon::make($this->created_at)->diffForHumans() : null,
        ];
    }
}
