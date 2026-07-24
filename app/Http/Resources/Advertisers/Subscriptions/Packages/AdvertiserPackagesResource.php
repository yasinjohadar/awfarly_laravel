<?php

namespace App\Http\Resources\Advertisers\Subscriptions\Packages;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdvertiserPackagesResource extends JsonResource
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
            'startsAt' => $this->starts_at ? Carbon::make($this->starts_at)->format('Y-m-d h:i A') : null,
            'endsAt' => $this->ends_at ? Carbon::make($this->ends_at)->format('Y-m-d h:i A') : null,
            'isEnded' => (bool)$this->is_ended,
            'isActive' => (bool)$this->is_active,
            'isCurrent' => $this->is_current,
            'package' => PackagesResource::make($this->package),
        ];
    }
}
