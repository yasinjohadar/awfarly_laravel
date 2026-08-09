<?php

namespace App\Http\Controllers\API\Advertisers\Interests;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Interests\InterestsResource;
use App\Http\Resources\Users\Advertisers\Interests\AdvertiserInterestsResource;
use App\Models\Interests\Interest;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InterestsController extends Controller
{

    /**
     * @return Application|ResponseFactory|Response
     */
    public function getInterests(Request $request)
    {
        $interests = Interest::whereNull('parent_interest_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return $this->apiResponse(InterestsResource::collection($interests));
    }

    public function deleteAdvertiserInterests(Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }
        //get data
        $data = $request->all();

        //validate interests
        $this->apiValidate($data, [
            'interests' => ['nullable', 'array'],
            'interests.*' => ['exists:interests,id'],
        ]);

        DB::beginTransaction();
        try {

            //delete interests foreach one
            foreach ($data['interests'] as $interest) {
                Auth::guard('advertiser-api')->user()
                    ->interests()
                    ->where('interest_id', $interest)
                    ->delete();
            }
            //get user interests
            $interests = Auth::guard('advertiser-api')->user()
                ->interests()
                ->get();

        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/interests/interests.something-wrong'));
        }

        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/interests/interests.deleted'),
            'data' => AdvertiserInterestsResource::collection($interests),
        ]);

    }


    /**
     * @return Application|ResponseFactory|Response
     */
    public function getUserInterests()
    {
        $interests = Auth::guard('advertiser-api')->user()
            ->interests()
            ->pluck('interest_id')
            ->toArray();

        $interests = Interest::whereIn('id', $interests)
            ->orderBy('order')
            ->get();

        return $this->apiResponse([
            'data' => InterestsResource::collection($interests)
        ]);
    }

    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function getInterestById($id)
    {
        $interest = Interest::where('id', $id)
            ->where('is_active', true)
            ->first();


        //return error if interest wasn't found
        if (!$interest) {
            return $this->apiBadRequestResponse(__('api/advertisers/interests/interests.wrong-id'));
        }

        return $this->apiResponse(InterestsResource::make($interest));
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function addAdvertiserInterests(Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //get data
        $data = $request->all();

        //validate interests
        $this->apiValidate($data, [
            'interests' => ['nullable', 'array'],
            'interests.*' => ['exists:interests,id'],
        ]);

        $max_interests = Settings::Get('max.user.interests.interests', 200);

        $interests = Auth::guard('advertiser-api')->user()
            ->interests()
            ->count();

        if ($interests >= $max_interests) {
            return $this->apiExceptionResponse(__('api/advertisers/interests/interests.exceeded-limit'));
        }

        DB::beginTransaction();
        try {
            Auth::guard('advertiser-api')->user()
                ->interests()
                ->delete();
            //add interests foreach one
            foreach ($data['interests'] as $interest) {
                Auth::guard('advertiser-api')->user()
                    ->interests()
                    ->updateOrCreate([
                        'interest_id' => $interest
                    ]);
            }
            //get user interests
            $interests = Auth::guard('advertiser-api')->user()
                ->interests()
                ->get();

        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/interests/interests.something-wrong'));
        }

        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/interests/interests.added'),
            'data' => AdvertiserInterestsResource::collection($interests),
        ]);
    }
}
