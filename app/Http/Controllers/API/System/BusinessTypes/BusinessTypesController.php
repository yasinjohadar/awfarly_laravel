<?php

namespace App\Http\Controllers\API\System\BusinessTypes;

use App\Http\Controllers\Controller;
use App\Http\Resources\Advertisers\BusinessTypes\BusinessTypesResource;
use App\Models\Users\Advertisers\BusinessTypes\AdvertiserBusinessType;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

class BusinessTypesController extends Controller
{
    /**
     * @return Application|ResponseFactory|Response
     */
    public function getBusinessTypes()
    {
        //get all business types
        $business_types = AdvertiserBusinessType::orderBy('order')
            ->where('is_active', true)
            ->get();
        return $this->apiResponse(BusinessTypesResource::collection($business_types));
    }
}
