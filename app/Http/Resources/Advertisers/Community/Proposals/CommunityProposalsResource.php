<?php

namespace App\Http\Resources\Advertisers\Community\Proposals;

use App\Helpers\Files;
use App\Http\Resources\Media\MediaResource;
use App\Models\Users\Advertisers\AdvertiserUser;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App;
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
        $user = Auth::guard('advertiser-api')->user();
        $is_expired = null;
        $expires_at = $this->expires_in ? Carbon::make($this->answered_at)->addDays($this->expires_in) : null;
        if ($expires_at) {
            $is_expired = $expires_at->copy()->isPast();
            $expires_at = $expires_at->diffForHumans();
        }

        //get name column to show countries, cities in current user language
        $name_column = (App::currentLocale() === 'ar') ? 'name_ar' : 'name_en';

        //check whether the sender is this user
        $isOwner = ($this->user->id == $user->id && $this->user->user_type === $user->user_type);

        //get user rate
        $advertiser_rate = $this->advertiser->rate ?? 0;

        $user_rate = ($this->user->user_type === 'advertiser') ? ($this->advertiser->rate ?? 0) : null;

        $is_allow_answer = (!$isOwner && !$this->answered_at);

        if ($this->answered_at) {
            $expiresIn = Carbon::make($this->answered_at)->addDays($this->expires_in);
            $expires_in = $expiresIn->diffInDays(now(), false);
            $expires_in = $expires_in >= 0 ? 0 : abs($expires_in) + 1;
        } else {
            $expires_in = $this->expires_in ?? null;
        }
        //check whether this user is being followed or by the current logged user or not
        $followed = Auth::guard('advertiser-api')->user()
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
                'country' => optional($this->user->country)->{$name_column},
                'governorate' => optional($this->user->governorate)->{$name_column},
                'city' => optional($this->user->city)->{$name_column},
                'governorateId' => $this->user->governorate_id,
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
                'country' => optional($this->advertiser->country)->{$name_column},
                'governorate' => optional($this->advertiser->governorate)->{$name_column},
                'city' => optional($this->advertiser->city)->{$name_column},
                'governorateId' => $this->advertiser->governorate_id,
                'rate' => $advertiser_rate,
                'isElite' => (bool)$this->advertiser->is_elite,
                'isOnline' => $this->advertiser->is_online,
            ],
            'expiresIn' => $expires_in ?? null,
            'expiresAt' => $expires_at,
            'isExpired' => $is_expired,
            'isOwner' => $isOwner,
            'isAllowAnswer' => $is_allow_answer,
            'answeredAt' => $this->answered_at ? Carbon::make($this->answered_at)->diffForHumans() : null,
            'createdAt' => $this->created_at ? Carbon::make($this->created_at)->diffForHumans() : null,
        ];
    }
}
