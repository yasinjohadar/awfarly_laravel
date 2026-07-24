<?php

namespace App\Helpers\FirebaseAuth;

use Illuminate\Support\Facades\Http;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Exception;

class FirebaseAuthHelper
{
    /**
     * Login with phone number
     *
     * @param $phone
     * @return false|UserRecord
     */
    public static function loginWithPhone($phone)
    {
        try {
            return Firebase::auth()->getUserByPhoneNumber($phone);
        } catch (AuthException | FirebaseException $e) {
            dd($e);
            return false;
        }
    }
    /**
     * Login with phone number
     *
     * @param $uid
     * @return false|UserRecord
     */
    public static function loginWithUid($uid)
    {
        try {
            return Firebase::auth()->getUser($uid);
        } catch (AuthException | FirebaseException $e) {
            return false;
        }
    }

    /**
     * Login with phone number
     *
     * @param $phone
     * @return false|UserRecord
     */
    public static function disableUserWithPhone($phone)
    {
        try {
            $auth = Firebase::auth();
            // Get user
            $user = $auth->getUserByPhoneNumber($phone);

            return $auth->updateUser($user->uid, [
                'disabled' => true,
            ]);
        } catch (AuthException | FirebaseException $e) {
            return false;
        }
    }
    /**
     * Login with phone number
     *
     * @param $phone
     * @return false|UserRecord
     */
    public static function enableUserWithPhone($phone)
    {
        try {
            $auth = Firebase::auth();
            // Get user
            $user = $auth->getUserByPhoneNumber($phone);

            return $auth->updateUser($user->uid, [
                'disabled' => false,
            ]);
        } catch (AuthException | FirebaseException $e) {
            return $e;
        }
    }
    /**
     * @param $uid
     * @return bool
     */
    public static function removeUser($uid): bool
    {
        try {
            $auth = Firebase::auth();
            $user = $auth->getUser($uid);
            if (!$user) {
                return false;
            }
            $auth->deleteUser($uid);
        } catch (AuthException | FirebaseException $e) {
            return false;
        }
        return true;
    }
}
