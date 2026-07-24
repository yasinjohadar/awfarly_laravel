<?php

namespace App\Http\Resources\Advertisers\Requests;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsernameRequestsResource extends JsonResource
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
            'oldUsername' => $this->old_username,
            'newUsername' => $this->new_username,
            'reason' => $this->reason,
            'status' => $this->status
        ];
    }
}
