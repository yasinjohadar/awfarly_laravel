<?php

namespace App\Http\Controllers\API\Auth;

use Exception;
use Carbon\Carbon;
use App\Helpers\Filter;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Helpers\ActivationCodeService;
use App\Helpers\FirebaseAuth\FirebaseAuthHelper;
use App\Models\Users\Customers\CustomerUser;
use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Contracts\Foundation\Application;
use App\Models\Users\Shared\Social\SocialAccount;
use Illuminate\Contracts\Routing\ResponseFactory;
use App\Services\SmsServices\SaudiGateway\SaudiSmsService;
use App\Models\Users\Advertisers\BusinessTypes\AdvertiserBusinessType;
use Illuminate\Support\Facades\Log;
use Kreait\Laravel\Firebase\Facades\FirebaseAuth;

class RegisterController extends Controller
{
    /**
     * Set default guard
     * @var string
     */
    public $guard = 'customer';

    /**
     * @param Request $request
     *
     * @return Application|ResponseFactory|Response
     */
    public function register(Request $request)
    {
        // Maintenance mode
        if (Settings::Get('maintenance.mode', 0)) {
            return $this->apiExceptionResponse(__('api/auth/auth.maintenance'));
        }

        // Set data
        $data = $request->only([
            'type',
            'name',
            'businessTypeId',
            'mobile',
            'email',
            'username',
            'password',
            'countryCode',
            'cityId',
            'fcmToken',
            'isAcceptedSendNotifications',
            'mobileVerificationCode',
            'isRequestMobileVerificationCode',
            'provider',
            'providerId',
            'interestedCategories',
            'birth_date',
            'gender',
            'discount_percentage',
        ]);

        // Validate data
        $this->apiValidate($data, [
            'type' => 'required|in:customer,advertiser',
            'name' => 'required|string',
            'businessTypeId' => 'nullable|integer|exists:advertisers_business_types,id',
            'mobile' => 'required|string|unique:customers_users|unique:advertisers_users|unique:admins_users',
            'email' => 'nullable|email:rfc,dns|unique:customers_users|unique:admins_users',
//            'username' => 'nullable|string|min:6|regex:/^[A-Za-z0-9.-]+$/|unique:customers_users|unique:advertisers_users|unique:admins_users',
            'discount_percentage' => ['sometimes','numeric'],
            'password' => 'required|min:6',
            'countryCode' => 'required|exists:countries,code',
            'cityId' => 'required|exists:cities,id',
            'fcmToken' => 'nullable|string',
            'isAcceptedSendNotifications' => 'nullable|bool',
            'mobileVerificationCode' => 'nullable|string',
            'isRequestMobileVerificationCode' => 'nullable|bool',
            'provider' => 'nullable|required_with:providerId',
            'providerId' => 'nullable|required_with:provider',
            'interestedCategories' => ['nullable','sometimes', 'array'],
            'gender' => ['nullable','sometimes', 'string','in:male,female'],
            'birth_date' => ['nullable','sometimes', 'date'],
            'interestedCategories.*' => ['nullable','sometimes']
        ]);
        // Set guard
        $this->guard = $data['type'];

        DB::beginTransaction();
        try {
            if (
                ($request->has('mobileVerificationCode') &&
                    is_null($data['mobileVerificationCode'])
                ) ||
                ($request->has('isRequestMobileVerificationCode') &&
                    $data['isRequestMobileVerificationCode'] == true
                )
            ) {
                // Send and generate mobile verification auth code
                $activation_code = ActivationCodeService::generate($data['mobile']);

            } elseif (
                isset($data['mobileVerificationCode']) &&
                (ActivationCodeService::validate($data['mobile'], $data['mobileVerificationCode'])
                    || ActivationCodeService::get($data['mobile']) == $data['mobileVerificationCode']
                )
            ) {
                // then make verficattion endpoint to verify and update firease user status
                FirebaseAuthHelper::enableUserWithPhone($data['mobile']);
                // } else {
                if ($data['type'] === 'advertiser' && $request->has('businessTypeId') && $request->get('businessTypeId')) {
                    $business_type = AdvertiserBusinessType::where('id', $data['businessTypeId'])
                        ->where('is_active', true)
                        ->first();

                    if (!$business_type) {
                        return $this->apiBadRequestResponse(__('api/auth/auth.business-disabled'));
                    }
                }
                $user = $this->model()::create([
                    'name' => $request->has('name') ? ucwords(Filter::RemoveHtml(trim($data['name']))) : null,
                    'business_type' => ($data['type'] == 'advertiser' && $request->has('businessTypeId') && $data['businessTypeId']) ? $data['businessTypeId'] : null,
                    'country_code' => $request->has('countryCode') ? $data['countryCode'] : null,
                    'city_id' => $request->has('cityId') ? $data['cityId'] : null,
//                    'username' => $request->has('username') ? Filter::RemoveHtml($data['username']) : null,
                    'mobile' => $request->has('mobile') ? Filter::RemoveHtml($data['mobile']) : null,
                    'email' => $request->has('email') ? Filter::RemoveHtml($data['email']) : null,
                    'password' => $request->has('password') ? bcrypt($data['password']) : null,
                    'fcm_token' => $request->has('fcmToken') ? Filter::RemoveHtml($data['fcmToken']) : null,
                    'discount_percentage' => $request->has('discount_percentage') ? Filter::RemoveHtml($data['discount_percentage']) : null,
                    'birth_date' => $request->has('birth_date') ? Filter::RemoveHtml($data['birth_date']) : null,
                    'gender' => $request->has('gender') ? Filter::RemoveHtml($data['gender']) : null,
                    'is_accepted_send_notifications' => $request->has('isAcceptedSendNotifications') && $data['isAcceptedSendNotifications'] == true,
                    'mobile_verified_at' => now(),
                    'allowed_posts_count' => Settings::Get('user.allowed.posts'),
                    'allowed_offers_count' => Settings::Get('user.allowed.offers'),
                    'last_login_at' => now(),
                ]);
                //change category id
                if ($request->has('interestedCategories') && count($request->interestedCategories) > 0) {
                    $user->categories()
                        ->delete();
                    foreach ($data['interestedCategories'] as $category) {
                        if ($category != null) {
                            $user->categories()
                                ->updateOrCreate([
                                    'category_id' => $category,
                                ]);
                        }
                    }
                }

                /// Set social account
                if (isset($data['provider']) && $data['provider']) {
                    SocialAccount::create([
                        'user_id' => $user->id,
                        'user_type' => $user->class,
                        'provider' => $data['provider'],
                        'provider_id' => $data['providerId'],
                    ]);
                }

                // Create login token
                $token = $user->createToken("{$this->guard}", [$this->guard])->accessToken;

                // Remove verification code form cache
                ActivationCodeService::remove($data['mobile']);
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('daily')->error($e);
            $this->apiExceptionResponse(__('api/auth/auth.something-wrong'));
        }

        DB::commit();

        // If token generated successfully
        if (isset($token) && $token) {
            return $this->apiResponse([
                'user' => [
                    'id' => $user->id,
                    'type' => $user->user_type,
                    'status' => 'active',
                    'isActive' => true,
                ],
                'token' => $token,
                'scope' => [$this->guard],
            ]);
        } elseif (isset($activation_code) && $activation_code) {
            $data = [
                'isMessageSent' => true,
                'saudi_number' => false
            ];
            if ($request->countryCode == 'SA') {
                // send code to mobile here
                $code = ActivationCodeService::get($request->mobile);

                $message = "$code is your activation code for Price Crush app";

                $message_sent = (new SaudiSmsService)->send($request->mobile, $message);

                // // if error happend stop request

                if (!$message_sent) return $this->apiExceptionResponse(__('api/auth/auth.something-wrong'));


                $data = array_merge($data, ['saudi_number' => true]);
            }
            return $this->apiResponse([
                'message' => __('api/auth/auth.activation-sent'),
                'data' => $data,
            ]);
        } elseif ($request->has('mobileVerificationCode') && $data['mobileVerificationCode']) {

            return $this->apiExceptionResponse(__('api/auth/auth.invalid-verification'));
        }
        return $this->apiExceptionResponse(__('api/auth/auth.something-wrong'));
    }

    /**
     * Get users model
     * @return string
     */
    protected function model()
    {
        if ($this->guard == 'customer') {
            return CustomerUser::class;
        } elseif ($this->guard == 'advertiser') {
            return AdvertiserUser::class;
        }
    }
}
