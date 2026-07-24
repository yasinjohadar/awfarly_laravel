<?php

namespace App\Http\Resources\Customers\Community\Posts\Subscribed;

use App\Http\Resources\Customers\Community\Posts\CommunityPostsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscribedPostsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'post' => CommunityPostsResource::make($this->post),
        ];
    }
}
