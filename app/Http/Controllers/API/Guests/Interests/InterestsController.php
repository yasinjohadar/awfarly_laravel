<?php

namespace App\Http\Controllers\API\Guests\Interests;

use App\Http\Controllers\Controller;
use App\Http\Resources\Interests\InterestsResource;
use App\Models\Interests\Interest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InterestsController extends Controller
{
    /**
     * @return Application|ResponseFactory|Response
     */
    public function getInterests(Request $request)
    {
        $interests = Interest::whereNull('parent_interest_id')
            ->orderBy('order')
            ->get();

        return $this->apiResponse(InterestsResource::collection($interests));
    }


    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function getInterestById($id)
    {
        $interest = Interest::where('id', $id)
            ->first();


        //return error if interest wasn't found
        if (!$interest) {
            return $this->apiBadRequestResponse(__('api/guests/interests/interests.wrong-id'));
        }

        return $this->apiResponse(InterestsResource::make($interest));
    }
}
