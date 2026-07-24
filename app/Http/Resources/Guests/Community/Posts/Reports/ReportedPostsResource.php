<?php

namespace App\Http\Resources\Guests\Community\Posts\Reports;

use App\Http\Resources\Guests\Community\Posts\CommunityPostsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportedPostsResource extends JsonResource
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
            'post' => CommunityPostsResource::make($this->reported),
        ];
    }
}
