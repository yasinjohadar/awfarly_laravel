<?php

namespace App\Http\Controllers\API\Auth;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Models\Users\Shared\Social\SocialAccount;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public string $guard = 'customer';

    /**
     * Check if the user is authenticated
     * @return Application|ResponseFactory|Response
     */
    public function check()
    {
        //Maintenance mode
        if (Settings::Get('maintenance.mode', 0)) {
            return $this->apiExceptionResponse(__('api/auth/auth.maintenance'));
        }

        try {
            $user = $this->getUserType();

            if ($user) {
                return $this->apiResponse([
                    'user' => [
                        'id' => $user->id,
                        'type' => $user->user_type,
                        'status' => $user->status,
                        'isActive' => $user->status == 'active'
                    ],
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();

            $this->apiExceptionResponse(__('api/auth/auth.something-wrong'));
        }

        return $this->apiResponse(['type' => 'unauthenticated']);
    }

    /**
     * Get the type of the user
     * @return Authenticatable|null
     */
    public function getUserType()
    {
        //Check login
        if (auth()->check() && auth(auth()->user()->token()->name . '-api')->check()) {
            //Set user
            return auth(auth()->user()->token()->name . '-api')->user();
        }
    }

    public function update_token(Request $request)
    {
        $data = $this->validate($request,[
            'fcmToken' => 'nullable|string',
        ]);

        $user = auth()->user();

        // Add fcm token
        if (isset($data['fcmToken']) && $data['fcmToken']) {
            $user->update(['fcm_token' => $data['fcmToken']]);
        }

        return $this->apiResponse([
            'user' => [
                'id' => $user->id,
                'type' => $user->user_type,
                'status' => $user->status,
                'isActive' => $user->status == 'active',
            ],
        ]);
    }
    /**
     * Login user
     *
     * @param Request $request
     *
     * @return Application|ResponseFactory|Response
     */
    public function login(Request $request)
    {
        //Maintenance mode
        if (Settings::Get('maintenance.mode', 0)) {
            return $this->apiExceptionResponse(__('api/auth/auth.maintenance'));
        }

        $data = $request->only([
            'login',
            'password',
            'provider',
            'providerId',
            'fcmToken',
        ]);

        //Validate data
        $this->apiValidate($data, [
            'login' => 'required_without:provider|string',
            'password' => 'required_with:login|string',
            'provider' => 'required_without:login|string|in:google,apple',
            'providerId' => 'required_with:provider|string',
            'fcmToken' => 'nullable|string',
        ]);

        //Set guards
        $guards = [
            'customer',
            'advertiser',
        ];

        //Set user
        $user = null;

        DB::beginTransaction();
        try {

            // Login with (login, password)
            if (isset($data['login']) && $data['login']) {

                //Check login data with guards
                foreach ($guards as $guard) {
                    //Auth user
                    if (filter_var($data['login'], FILTER_VALIDATE_EMAIL)) {
                        //Try email
                        Auth::guard($guard)->attempt(['email' => $data['login'], 'password' => $data['password']]);
                    } else {
                        //Try mobile
                        Auth::guard($guard)->attempt(['mobile' => $data['login'], 'password' => $data['password']]);

                        //Try username
                        if (!Auth::guard($guard)->check()) {
                            Auth::guard($guard)->attempt(['username' => $data['login'], 'password' => $data['password']]);
                        }
                    }

                    //If authenticated
                    if (Auth::guard($guard)->check()) {

                        //Set new guard
                        $this->guard = $guard;

                        //Set user data
                        $user = Auth::guard($this->guard)->user();

                        //Create token
                        $token = $user->createToken($user->user_type, [$user->user_type])->accessToken;

                        //End
                        break;
                    }
                }
            } // Login with (social account)
            elseif (isset($data['provider']) && $data['provider']) {
                // Get account
                $account = SocialAccount::where('provider', $data['provider'])
                    ->where('provider_id', $data['providerId'])
                    ->first();

                // Authenticated
                if ($account) {
                    // Set user
                    $user = $account->user;

                    //Set new guard
                    $this->guard = "{$user->user_type}-api";

                    //Create token
                    $token = $user->createToken($user->user_type, [$user->user_type])->accessToken;
                }
            }

        } catch (Exception $e) {
            DB::rollBack();

            $this->apiExceptionResponse(__('api/auth/auth.something-wrong'));
        }
        DB::commit();

        if (isset($token)) {
            // Add fcm token
            if (isset($data['fcmToken']) && $data['fcmToken']) {
                $user->update(['fcm_token' => $data['fcmToken']]);
            }

            // Check if the account is banned
            if ($user->status == 'banned') {
                return $this->apiExceptionResponse(__('api/auth/auth.banned-account'));
            }

            //update last login at
            $user->update([
                'last_login_at' => now(),
                'last_online_at' => now(),
                'is_online' => true,
            ]);

            return $this->apiResponse([
                'user' => [
                    'id' => $user->id,
                    'type' => $user->user_type,
                    'status' => $user->status,
                    'isActive' => $user->status == 'active',
                ],
                'token' => $token,
                'scope' => [$user->user_type],
            ]);
        }

        // Send social error
        if ($request->has('provider')) {
            return $this->apiExceptionResponse(__('api/auth/auth.wrong-credentials-social'));
        }

        return $this->apiExceptionResponse(__('api/auth/auth.wrong-credentials'));
    }

    /**
     * Logout user
     *
     * @return Application|ResponseFactory|Response
     */
    public function logout()
    {
        $user = auth($this->guard)->user();

        if ($user) {
            DB::beginTransaction();
            try {
                // Delete fcm
                $user->update(['fcm_token' => null, 'is_online' => false]);

            } catch (Exception $e) {
                DB::rollBack();

                $this->apiExceptionResponse(__('api/auth/auth.something-wrong'));
            }
            DB::commit();

            // Delete tokens
            $user->tokens()
                ->each(function ($token) {
                    $token->revoke();
                });
        } else {
            return $this->apiResponse(['type' => 'unauthenticated']);
        }
    }
}
