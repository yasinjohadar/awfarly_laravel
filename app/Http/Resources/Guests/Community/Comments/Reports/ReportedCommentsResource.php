<?php

namespace App\Http\Resources\Guests\Community\Comments\Reports;

use App\Http\Resources\Guests\Community\Comments\CommunityCommentsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportedCommentsResource extends JsonResource
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
            'comment' => CommunityCommentsResource::make($this->reported),
        ];
    }
}
