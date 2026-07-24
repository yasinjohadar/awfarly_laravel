<?php

namespace App\Http\Resources\Advertisers\Community\Offers\Reports;

use App\Http\Resources\Advertisers\Community\Offers\CommunityOffersResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportedOffersResource extends JsonResource
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
            'reason' => $this->reason ?? '-',
            'type' => $this->type ? __("api/reports/reports.types.$this->type") : null,
            'offer' => CommunityOffersResource::make($this->reported),
        ];
    }
}
