<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SocialAuthController extends Controller
{
    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function addSocialAccount(Request $request)
    {
        //set data
        $data = $request->only([
            'provider',
            'providerId',
        ]);

        //Validate data
        $this->apiValidate($data, [
            'provider' => 'required:string|in:google,apple',
            'providerId' => 'required:provider|string|unique:social_accounts,provider_id',
        ]);

        //current user guard
        if (Auth::check() && Auth::guard(Auth::user()->token()->name . '-api')->check()) {
            $user = Auth::guard(Auth::user()->token()->name . '-api')->user();
        } else {
            $user = null;
        }

        //return error if user doesn't exist
        if (!$user) {
            return $this->apiBadRequestResponse(__('api/auth/socialAccounts.wrong-user'));
        }

        DB::beginTransaction();
        try {

            //create or update the social account
            $user->socialAccounts()
                ->updateOrCreate([
                    'provider' => $data['provider'],
                    'provider_id' => $data['providerId'],
                ]);

        } catch (Exception $e) {
            DB::rollBack();
            //return error
            return $this->apiExceptionResponse(__('api/auth/socialAccounts.something-wrong'));
        }
        DB::commit();
        //get social account with this provider and user
        $social = $user->socialAccounts()
            ->pluck('provider')
            ->toArray();

        //return success
        return $this->apiResponse([
            'message' => __('api/auth/socialAccounts.added'),
            'data' => $social
        ]);
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function removeSocialAccount(Request $request)
    {
        //set data
        $data = $request->only([
            'provider',
        ]);

        //Validate data
        $this->apiValidate($data, [
            'provider' => 'required:string|in:google,apple',
        ]);

        //current user guard
        if (Auth::check() && Auth::guard(Auth::user()->token()->name . '-api')->check()) {
            $user = Auth::guard(Auth::user()->token()->name . '-api')->user();
        } else {
            $user = null;
        }

        //return error if user wasn't found
        if (!$user) {
            return $this->apiBadRequestResponse(__('api/auth/socialAccounts.no-social'));
        }

        //get social account with this provider and user
        $social = $user->socialAccounts()
            ->where('provider', $data['provider'])
            ->first();

        //return error if there is no social with this provider linked to the user
        if (!$social) {
            return $this->apiBadRequestResponse(__('api/auth/socialAccounts.wrong-social'));
        }

        DB::beginTransaction();
        try {
            //delete social account
            $social->delete();

        } catch (Exception $e) {
            DB::rollBack();
            //return error
            return $this->apiExceptionResponse(__('api/auth/socialAccounts.something-wrong'));
        }
        DB::commit();
        //return success
        return $this->apiResponse([
            'message' => __('api/auth/socialAccounts.deleted'),
        ]);
    }

    /**
     * @return Application|Response|ResponseFactory
     */
    public function getSocialAccounts()
    {
        //get user
        if (Auth::check() && Auth::guard(Auth::user()->token()->name . '-api')->check()) {
            $user = Auth::guard(Auth::user()->token()->name . '-api')->user();
        } else {
            $user = null;
        }

        //return error if user wasn't found
        if (!$user) {
            return $this->apiBadRequestResponse(__('api/auth/socialAccounts.no-social'));
        }

        //get social account with this provider and user
        $social = $user->socialAccounts()
            ->pluck('provider')
            ->toArray();

        return $this->apiResponse($social);
    }
}
