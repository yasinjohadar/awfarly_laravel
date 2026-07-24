<?php

namespace App\Helpers;

use App\Helpers\FirebaseAuth\FirebaseAuthHelper;
use Illuminate\Support\Facades\Cache;
use Kreait\Laravel\Firebase\Facades\Firebase;

class ActivationCodeService
{
    /**
     * Generate an activation code and save it in cache
     *
     * @param $mobile
     *
     * @return bool
     */
    public static function generate($mobile): bool
    {
        try{

        $createdUser = Firebase::auth()->createUser(['phoneNumber' => $mobile]);
        }
        catch (\Exception $exception)
        {

        }

        $digits = 6;
        $code = "";
        while ($digits > 0) {
            $code .= mt_rand(1, 9);
            $digits -= 1;
        }

        $expiresAt = 30 * 60;

        // Send the same code if exists
        if (Cache::has(md5(trim($mobile)))) {
            $code = Cache::forget(md5(trim($mobile)));
        }

        // Save code
        Cache::put(
            md5(trim($mobile)),
            $code,
            $expiresAt
        );
        return self::SendSMSWithCode($mobile, $code);
    }

    /**
     * Validate activation code of user
     *
     * @param string $mobile
     * @param string $code
     *
     * @return bool
     */
    public static function validate($mobile, $code): bool
    {
        // Set mobile
        $mobile = trim($mobile);

        // Get user from FB Auth (user will be created if the code is valid)
        $user = FirebaseAuthHelper::loginWithPhone($mobile);

        // If user exists (user activate his phone and registered to Firebase) and the user is active

        $storedCode = Cache::get(md5($mobile));

        return ($user !== false && $user->disabled == false);
//        return ($storedCode && $storedCode == $code);
    }

    /**
     * Get activation code from cache
     *
     * @param string $mobile
     *
     * @return void
     */
    public static function get($mobile)
    {
        return Cache::get(md5(trim($mobile)));
    }

    /**
     * Remove activation code from cache
     *
     * @param string $mobile
     *
     * @return void
     */
    public static function remove($mobile): void
    {
        Cache::forget(md5(trim($mobile)));
    }

    /**
     *
     * Send sms with code
     * @param $mobile
     * @param $code
     */
    protected static function SendSMSWithCode($mobile, $code)
    {
        return true;
    }
}
