<?php

namespace App\Http\Resources\Advertisers\Community\Proposals\Reports;

use App\Http\Resources\Advertisers\Community\Proposals\CommunityProposalsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportedProposalsResource extends JsonResource
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
            'proposal' => CommunityProposalsResource::make($this->reported),
        ];
    }
}
