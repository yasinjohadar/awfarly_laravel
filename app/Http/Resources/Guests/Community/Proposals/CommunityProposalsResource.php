<?php

namespace App\Http\Resources\Guests\Community\Proposals;

use Illuminate\Http\Resources\Json\JsonResource;

class CommunityProposalsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
