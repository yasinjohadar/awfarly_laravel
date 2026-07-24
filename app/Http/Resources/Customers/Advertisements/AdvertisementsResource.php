<?php

namespace App\Http\Resources\Customers\Advertisements;

use App\Helpers\Files;
use App\Http\Resources\Media\MediaResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdvertisementsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        //check whether user liked this post
        $isLiked = Auth::guard('customer-api')->user()
            ->postsLikes()
            ->where('post_id', $this->post->id)
            ->exists();

        $false_permissions = !(Auth::guard('customer-api')->user()->status === 'inactive');

        return [
            'id' => $this->id,
            'type' => $this->type,
            'postId' => $this->post->id ?? null,
            'users' => $this->users,
            'advertiserName' => $this->advertiser_name ?? null,
            'advertiserUrl' => $this->advertiser_url ?? null,
            'advertiserImage' => $this->advertiser_image ? route('files.image.get', $this->advertiser_image) : null,
            'websiteUrl' => route('post.index', ['id' => $this->post->id]),
            'content' => $this->content ?? null,
            'media' => MediaResource::collection($this->getMedia('advertisements')),
            'statistics' => [
                'views' => $this->post->views_count ?? null,
                'likes' => $this->post->likes_count ?? null,
                'comments' => $this->post->comments_count ?? null,
            ],
            'isLiked' => $isLiked,
            'permissions' => [
                'isAllowActions' => $false_permissions,
                'isAllowLike' => $false_permissions,
                'isAllowComment' => $false_permissions,
                'isAllowShare' => true,
            ],
            'startsAt' => $this->starts_at ? Carbon::parse($this->starts_at)->format('Y-m-d h:i A') : null,
            'endsAt' => $this->ends_at ? Carbon::parse($this->ends_at)->format('Y-m-d h:i A') : null,
            'createdAt' => $this->created_at ? Carbon::parse($this->created_at)->format('Y-m-d h:i A') : null,
        ];
    }
}
