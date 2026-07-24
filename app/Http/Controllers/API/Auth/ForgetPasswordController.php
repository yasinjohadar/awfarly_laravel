<?php

namespace App\Http\Controllers\API\Auth;

use Exception;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Helpers\ActivationCodeService;
use App\Models\Users\Customers\CustomerUser;
use App\Helpers\FirebaseAuth\FirebaseAuthHelper;
use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use App\Mail\Auth\ActivationCode\ActivationCodeMail;
use App\Services\SmsServices\SaudiGateway\SaudiSmsService;

class ForgetPasswordController extends Controller
{
    /**
     * Get user from login[email|username|mobile]
     * @param $login
     * @return mixed
     */
    public function getUserFromLogin($login)
    {
        //Set users
        $customer = CustomerUser::where('mobile', $login)
            ->first();

        $advertiser = AdvertiserUser::where('mobile', $login)
            ->first();

        return $customer ?? $advertiser;
    }

    /**
     * Send activation code to reset user password.
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function forgetPassword(Request $request)
    {
        //Maintenance mode
        if (Settings::Get('maintenance.mode', 0)) {
            return $this->apiExceptionResponse(__('api/auth/auth.maintenance'));
        }

        //Set data
        $data = $request->only([
            'mobile',
            'uid',
            'password',
            'send_reset_sms',
            'sms_otp',
        ]);

        $this->apiValidate($data, [
            'mobile' => ['required', 'string'],
            'uid' => ['nullable', 'string'],
            'password' => ['nullable', 'string'],
            'send_reset_sms' => ['sometimes', 'nullable', 'string'],
            'sms_otp' => ['sometimes', 'nullable', 'string'],
        ]);

        //Set user
        $user = $this->getUserFromLogin($data['mobile']);

        //check user
        if (!$user) {
            return $this->apiBadRequestResponse(__('api/auth/auth.no-user'));
        }

        if (isset($data['uid']) && $data['uid']) {
            $fireBaseUser = FirebaseAuthHelper::loginWithPhone($data['mobile']);
            if (!$fireBaseUser) {
                return $this->apiBadRequestResponse(__('api/auth/auth.no-user'));
            }

            //check uid
            if ($fireBaseUser->uid !== $data['uid']) {
                return $this->apiBadRequestResponse(__('api/auth/auth.wrong-uid'));
            }
            DB::beginTransaction();
            try {
                $user->update([
                    'password' => Hash::make($data['password']),
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                return $this->apiExceptionResponse(__('api/auth/auth.something-wrong'));
            }
            DB::commit();
            $message = __('api/auth/auth.password-updated');
        } else {
            $message = __('api/auth/auth.activation-sent');
        }

        if (isset($data['send_reset_sms']) && !is_null($data['send_reset_sms'])) {
            // send code to mobile here
            ActivationCodeService::generate($data['mobile']);

            $code = ActivationCodeService::get($data['mobile']);

            $message = "$code is your activation code for Ma3rdy app";

            $message_sent = (new SaudiSmsService)->send($request->mobile, $message);

            // // if error happend stop request
            if (!$message_sent) return $this->apiExceptionResponse(__('api/auth/auth.something-wrong'));

            return $this->apiResponse([
                'message' => __('api/auth/auth.activation-sent'),
                'data' => [
                    'isMessageSent' => true,
                ],
            ]);
        }

        if (
            isset($data['sms_otp']) && !is_null($data['sms_otp']) && ActivationCodeService::get($data['mobile']) == $data['sms_otp']
        ) {
            if (!is_null($data['password']) && $data['password'] != '') {
                try {
                    $user->update([
                        'password' => Hash::make($data['password']),
                    ]);
                } catch (Exception $e) {
                    DB::rollBack();
                    return $this->apiExceptionResponse(__('api/auth/auth.something-wrong'));
                }
                DB::commit();

                // Remove verification code form cache
                ActivationCodeService::remove($data['mobile']);
            }

            $message = __('api/auth/auth.password-updated');
        } elseif (isset($data['sms_otp']) && !is_null($data['sms_otp']) && ActivationCodeService::get($data['mobile']) != $data['sms_otp']) {
            return $this->apiExceptionResponse(__('api/auth/auth.invalid-verification'));
        }


        return $this->apiResponse([
            'message' => $message,
        ]);
    }
}
