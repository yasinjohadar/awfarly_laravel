<?php

namespace App\Http\Resources\Advertisers\Community\Offers\Comments\Reports;

use App\Http\Resources\Advertisers\Community\Offers\Comments\CommunityOffersCommentsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportedOffersCommentsResource extends JsonResource
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
            'comment' => CommunityOffersCommentsResource::make($this->reported),
        ];
    }
}
