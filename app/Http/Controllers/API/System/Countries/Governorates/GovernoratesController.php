<?php

namespace App\Http\Controllers\API\System\Countries\Governorates;

use App\Http\Controllers\Controller;
use App\Http\Resources\System\Countries\Governorates\GovernoratesResource;
use App\Models\Countries\Governorates\Governorate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

class GovernoratesController extends Controller
{
    /**
     * @return Application|ResponseFactory|Response
     */
    public function getGovernorates()
    {
        $governorates = Governorate::orderBy('order')->get();

        return $this->apiResponse(GovernoratesResource::collection($governorates));
    }

    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function getGovernorateById($id)
    {
        $governorate = Governorate::where('id', $id)->first();

        if (!$governorate) {
            return $this->apiBadRequestResponse(__('api/system/countries/governorates/governorates.wrong-id'));
        }

        return $this->apiResponse(GovernoratesResource::make($governorate));
    }
}
