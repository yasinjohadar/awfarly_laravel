<?php

namespace App\Http\Resources\Users\Advertisers\Reports;

use App\Http\Resources\Advertisers\Community\Posts\CommunityPostsResource;
use App\Http\Resources\Users\Advertisers\AdvertisersResource;
use App\Http\Resources\Users\Customers\CustomersResource;
use App\Models\Users\Customers\CustomerUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportedAdvertisersResource extends JsonResource
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
            'advertiser' => AdvertisersResource::make($this->reported),
        ];
    }
}
