<?php

namespace App\Helpers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Auth;
use Storage;

class Images
{
    /*******************************************************************************************************
     * All users
     ******************************************************************************************************
     * Get user profile image
     * @param null $image
     * @return Application|RedirectResponse|Redirector|mixed
     */
    public static function GetUserProfileImage($image = null)
    {
        //Get image if exists
        if (!is_null($image) && !empty($image) && $image != null) {
            $storagePath = Storage::exists($image) ? Storage::exists($image) ? Storage::get($image) : public_path('assets/images/user-default.png') : public_path('assets/images/user-default.png');
            return Image::make($storagePath)->response();
        }

        //Set default image
        $storagePath = public_path('assets/images/user-default.png');

        return Image::make($storagePath)->response();
    }

    /*
    * Get current user profile image
    */
    public static function GetCurrentUserProfileImage()
    {
        //Check and set image if user is login
        if (Auth::guard('customer')->check()) {
            $image = Auth::guard('customer')->user()->image;
        } elseif (Auth::guard('advertiser')->check()) {
            $image = Auth::guard('advertiser')->user()->image;
        } elseif (Auth::guard('admin')->check()) {
            $image = Auth::guard('admin')->user()->image;
        } else {
            $image = null;
        }

        return self::GetUserProfileImage($image);
    }


    /**
     * get image by path
     * @param null $image
     * @return Application|RedirectResponse|Redirector|mixed
     */
    public static function getChatImage($image = null)
    {
        //Get image if exists
        if (!is_null($image) && !empty($image)) {
            $storagePath = Storage::exists($image) ? Storage::get($image) : public_path('assets/images/user-default.png');
            return Image::make($storagePath)->response();
        }

        //Set default image
        $storagePath = storage_path('app/uploads/chats/not-found.png');

        return Image::make($storagePath)->response();
    }

    /**
     * get image by path
     * @param null $image
     * @return Application|RedirectResponse|Redirector|mixed
     */
    public static function getCategoryImage($image = null)
    {
        //Get image if exists
        if (!is_null($image) && !empty($image)) {
            $storagePath = Storage::exists($image) ? Storage::get($image) : public_path('assets/images/user-default.png');
            return Image::make($storagePath)->response();
        }

        //Set default image
        $storagePath = public_path('assets/images/user-default.png');

        return Image::make($storagePath)->response();
    }

    /**
     * @param null $image
     * @return mixed
     */
    public static function getImage($image = null)
    {
        //Get image if exists
        if (!is_null($image) && !empty($image)) {
            $storagePath = Storage::exists($image) ? Storage::exists($image) ? Storage::get($image) : public_path('assets/images/user-default.png') : public_path('assets/images/user-default.png');
            return Image::make($storagePath)->response();
        }

        //Set default image
        $storagePath = public_path('assets/images/user-default.png');

        return Image::make($storagePath)->response();
    }
}

