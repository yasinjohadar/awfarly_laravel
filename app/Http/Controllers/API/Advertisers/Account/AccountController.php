<?php

namespace App\Http\Controllers\API\Advertisers\Account;

use App\Helpers\Files;
use App\Helpers\Filter;
use App\Helpers\Categories\CategoriesFilter;
use App\Helpers\Geography\Geography;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Advertisers\Account\AccountResource;
use App\Http\Resources\Users\Advertisers\Reports\ReportedAdvertisersResource;
use App\Http\Resources\Users\Customers\Reports\ReportedCustomersResource;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\BusinessTypes\AdvertiserBusinessType;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Spatie\ImageOptimizer\OptimizerChain;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;


class AccountController extends Controller
{
    /**
     * @return Application|ResponseFactory|Response
     */
    public function getAccountData()
    {
        return $this->apiResponse(AccountResource::make(Auth::guard('advertiser-api')->user()));
    }


    public function increase(Request $request)
    {
        $data = $request->validate([
            'allowed_posts_count'    =>  'sometimes|nullable|integer|min:0',
            'allowed_offers_count'    =>  'sometimes|nullable|integer|min:0'
        ]);


        $user = Auth::guard('advertiser-api')->user();

        if($request->allowed_posts_count)
        $user->increment('allowed_posts_count',$request->allowed_posts_count);

        if($request->allowed_offers_count)
        $user->increment('allowed_offers_count',$request->allowed_offers_count);

        return $this->apiResponse(AccountResource::make(Auth::guard('advertiser-api')->user()));

    }

    public function addPoints(Request $request)
    {
        $data = $request->validate([
            'points'    =>  'required|integer|min:0'
        ]);
        $user = Auth::guard('advertiser-api')->user();

        $user->deposit($request->points);

        return $this->apiResponse(AccountResource::make(Auth::guard('advertiser-api')->user()));

    }
    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     * @throws FileNotFoundException
     */
    public function updateAccount(Request $request)
    {
        $user = Auth::guard('advertiser-api')->user();

        //Set user data
        $data = $request->only([
            'name',
            'email',
            'image',
            'username',
            'businessType',
            'bio',
            'mobile',
            'countryCode',
            'governorateId',
            'cityId',
            'languageCode',
            'contactNumber',
            'whatsappNumber',
            'facebookUrl',
            'twitterUrl',
            'websiteUrl',
            'chatStatus',
            'profilePrivacy',
            'password',
            'oldPassword',
            'isAcceptedSendNotifications',
            'addressLatitude',
            'addressLongitude',
            'status',
            'deleteImage',
            'isDisableAccount',
            'interestedCategories',
            'interests',
            'gender',
            'birth_date',
            'discount_percentage',
        ]);

        //older app builds send only cityId, derive the governorate so the pair
        //can never drift apart and block the user from posting later
        Geography::fillGovernorateFromCity($data, $request);

        //validate data
        $this->apiValidate($data, [
            'name' => ['nullable', 'string'],
            'image' => ['nullable', 'array'],
            'image.file' => ['image', 'max:15500'],
            'bio' => ['nullable'],
            'username' => ['nullable', "unique:advertisers_users,username,$user->id", 'unique:customers_users,username', 'unique:admins_users,username'],
            'email' => ['nullable', 'email:rfc', "unique:advertisers_users,email,$user->id", 'unique:customers_users,email', 'unique:admins_users,email'],
            'mobile' => ['nullable', 'string', "unique:advertisers_users,mobile,$user->id", 'unique:customers_users,mobile', 'unique:admins_users,mobile', 'regex:^\+\d+$^'],
            'businessType' => ['nullable', 'exists:advertisers_business_types,id'],
            'countryCode' => ['nullable', 'exists:countries,code'],
            'gender' => ['nullable','sometimes', 'string','in:male,female'],
            'birth_date' => ['nullable','sometimes', 'date'],
            'governorateId' => ['nullable', 'exists:governorates,id'],
            'cityId' => ['nullable', 'exists:cities,id'],
            'languageCode' => ['nullable', 'in:ar,en'],
            'contactNumber' => ['nullable', 'regex:^\+\d+$^'],
            'whatsappNumber' => ['nullable', 'regex:^\+\d+$^'],
            'facebookUrl' => ['nullable', 'url'],
            'twitterUrl' => ['nullable', 'url'],
            'websiteUrl' => ['nullable', 'url'],
            'chatStatus' => ['nullable', 'in:public,followers,disabled'],
            'profilePrivacy' => ['nullable', 'in:public,private,followers'],
            'password' => ['nullable', 'string', 'min:6'],
            'oldPassword' => ['required_with:password', 'string'],
            'isAcceptedSendNotifications' => ['nullable', 'boolean'],
            'addressLatitude' => ['nullable'],
            'addressLongitude' => ['nullable'],
            'discount_percentage' => ['required','numeric'],
            'status' => ['nullable', 'in:active,inactive'],
            'deleteImage' => ['nullable', 'boolean'],
            'isDisableAccount' => ['nullable', 'boolean'],
            'interestedCategories' => ['nullable', 'array'],
            'interestedCategories.*' => ['exists:categories,id'],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['exists:categories,id'],
        ]);


        if ($message = Geography::validateCityBelongsToGovernorate($data)) {
            return $this->apiBadRequestResponse($message);
        }

        //Validate the old password
        if ((!empty($request->get('password'))) && !Hash::check($request->get('oldPassword'), $user->getAuthPassword())) {
            return $this->apiBadRequestResponse(__('api/advertisers/account/account.incorrect-password'), false);
        }

        //change name
        if ($request->has('name') && (bool)Settings::Get('allow.users.change.name', true)) {
            if ($request->get('name')) {
                $user->name = Filter::RemoveHtml(trim($data['name']));
            }
        }

        //change email
        if ($request->has('email')) {
            if (empty($request->get('email'))) {
                $user->email = null;
            } elseif ($request->get('email')) {
                $user->email = Filter::RemoveHtml($data['email']);
            }
        }

        //change username
        if ($request->has('username')) {
            if ($user->username) {
//                return $this->apiBadRequestResponse(__('api/customers/account/account.username-assigned'));
            } elseif (!empty($request->get('username'))) {
                $user->username = Filter::RemoveHtml($data['username']);
            }
        }

        //Check and upload image
        if ($request->hasFile('image.file')) {
            //Request delete old image
            if ($user->image) {
                Files::deleteS3File($user->image);
            }

            // the image will be replaced with an optimized version which should be smaller
            $data['image'] = $request->file('image.file')->storeAs('uploads/avatars', md5($request->file('image.file')->getClientOriginalName()));
            /*$data['image'] = Files::uploadRequestImage($request, 'uploads/avatars', 'image.file', true, true);*/

            $user->image = $data['image'];
        } else {
            if ($request->has('deleteImage') && $request->get('deleteImage')) {
                //Request delete old image
                if ($user->image) {
                    Files::deleteS3File($user->image);
                }
                $data['image'] = null;
            } else {
                $data['image'] = $user->image ?? null;
            }
            $user->image = $data['image'];
        }

        //change username
        if ($request->has('bio')) {
            if (!empty($request->get('bio'))) {
                $user->bio = Filter::RemoveHtml($data['bio']);
            }
        }

        //change business type
        if ($request->has('businessTypeId')) {
            if (!empty($request->get('businessType'))) {
                $business_type = AdvertiserBusinessType::where('id', $data['businessTypeId'])
                    ->where('is_active', true)
                    ->first();

                if (!$business_type) {
                    return $this->apiBadRequestResponse(__('api/customers/account/account.business-disabled'));
                }

                $user->business_type = $data['businessTypeId'];
            }
        }

        //change mobile
        if ($request->has('mobile')) {
            if (!empty($request->get('mobile'))) {
                $user->mobile = Filter::RemoveHtml($data['mobile']);
            }
        }

        //Change password
        if (!empty($request->get('password'))) {
            $data['password'] = Hash::make($data['password']);
            $user->password = $data['password'];
        }

        //change country code
        if ($request->has('countryCode')) {
            if (!empty($request->get('countryCode'))) {
                $user->country_code = Filter::RemoveHtml($data['countryCode']);
            }
        }

        //change location
        if ($request->has('governorateId') || $request->has('cityId')) {
            Geography::assignUserLocation($user, $data);
        }

        //change language code
        if ($request->has('languageCode')) {
            if (!empty($request->get('languageCode'))) {
                $user->language_code = Filter::RemoveHtml($data['languageCode']);
            }
        }

        //change contact number
        if ($request->has('contactNumber')) {
            if (empty($request->get('contactNumber'))) {
                $user->contact_number = null;
            } elseif ($request->get('contactNumber')) {
                $user->contact_number = Filter::RemoveHtml($data['contactNumber']);
            }
        }

        //change whatsapp number
        if ($request->has('whatsappNumber')) {
            if (empty($request->get('whatsappNumber'))) {
                $user->whatsapp_number = null;
            } elseif ($request->get('whatsappNumber')) {
                $user->whatsapp_number = Filter::RemoveHtml($data['whatsappNumber']);
            }
        }

        //change facebook url
        if ($request->has('facebookUrl')) {
            if (empty($request->get('facebookUrl'))) {
                $user->facebook_url = null;
            } elseif ($request->get('facebookUrl')) {
                $user->facebook_url = Filter::RemoveHtml($data['facebookUrl']);
            }
        }

        //change twitter url
        if ($request->has('twitterUrl')) {
            if (empty($request->get('twitterUrl'))) {
                $user->twitter_url = null;
            } elseif ($request->get('twitterUrl')) {
                $user->twitter_url = Filter::RemoveHtml($data['twitterUrl']);
            }
        }

        //change website url
        if ($request->has('websiteUrl')) {
            if (empty($request->get('websiteUrl'))) {
                $user->website_url = null;
            } elseif ($request->get('websiteUrl')) {
                $user->website_url = Filter::RemoveHtml($data['websiteUrl']);
            }
        }

        if ($request->has('birth_date')) {
            $user->birth_date = $data['birth_date'];
        }

        if ($request->has('gender')) {
            $user->gender = $data['gender'];
        }

        if ($request->has('discount_percentage')) {
            $user->discount_percentage = $data['discount_percentage'];
        }


        //change chat status
        if ($request->has('chatStatus')) {
            $user->chats_privacy = $data['chatStatus'];
        }

        //change profile privacy
        if ($request->has('profilePrivacy')) {
            if (!empty($request->get('profilePrivacy'))) {
                $user->profile_privacy = $data['profilePrivacy'];
                if ($data['profilePrivacy'] === 'private') {
                    $user->is_follow_allowed = false;
                } else {
                    $user->is_follow_allowed = true;
                }
            }
        }

        //change accept send notifications
        if ($request->has('isAcceptedSendNotifications')) {
            $user->is_accepted_send_notifications = $data['isAcceptedSendNotifications'];
        }

        //change address Latitude
        if ($request->has('addressLatitude')) {
            if (empty($request->get('addressLatitude'))) {
                $user->address_latitude = null;
            } else if (!empty($request->get('addressLatitude'))) {
                $user->address_latitude = $data['addressLatitude'];
            }
        }

        //change address Longitude
        if ($request->has('addressLongitude')) {
            if (empty($request->get('addressLongitude'))) {
                $user->address_longitude = null;
            } elseif (!empty($request->get('addressLongitude'))) {
                $user->address_longitude = $data['addressLongitude'];
            }
        }

        if ($request->addressLatitude && $request->addressLongitude) {
            $user->location = new Point($request->addressLatitude, $request->addressLongitude, 4326);
        }

        //change status
        if ($request->has('isDisableAccount')) {
            $user->status = ($request->get('isDisableAccount') ? 'inactive' : 'active');
        }

        //edit account
        $user->save();
        //Begin Transaction
        DB::beginTransaction();
        try {

            //For an advertiser `interestedCategories` means their OWN business
            //categories: that is what installed builds send from the profile
            //editor and read back for the post/offer category dropdown. Their
            //interests are a separate set, written via `interests` below or
            //via POST /categories/interested.
            if ($request->has('interestedCategories')) {
                CategoriesFilter::syncCategories(
                    $user->categories(),
                    (array) ($data['interestedCategories'] ?? [])
                );
            }

            //change interests, independently of the own categories above
            if ($request->has('interests')) {
                CategoriesFilter::syncCategories(
                    $user->interests(),
                    (array) ($data['interests'] ?? [])
                );
            }
        } catch (Exception $e) {
            //roll back
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/account/account.something-wrong'));
        }
        //commit
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/account/account.edited'),
            'data' => AccountResource::make(Auth::guard('advertiser-api')->user()),
        ]);
    }

    /**
     * Set user status to online
     */
    public function ping()
    {
        DB::beginTransaction();
        try {
            Auth::guard('advertiser-api')->user()
                ->update([
                    'last_online_at' => now(),
                    'is_online' => true,
                ]);
        } catch (Exception $e) {
            DB::rollBack();
        }
        DB::commit();
        return $this->apiResponse(['type' => 'success']);
    }


    public function delete(Request $request)
    {
        $user = Auth::guard('advertiser-api')->user();

        //Set user data
        $data = $request->only([
            'password',
        ]);

        //validate data
        $this->apiValidate($data, [
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ((!empty($request->get('password'))) && !Hash::check($request->get('password'), $user->getAuthPassword())) {
            return $this->apiBadRequestResponse(__('api/advertisers/account/account.incorrect-password'), false);
        }

        //Validate the old password
        if ((!empty($request->get('password'))) && Hash::check($request->get('password'), $user->getAuthPassword())) {
            DB::beginTransaction();
            try {
                $user->delete();
            } catch (Exception $e) {
                //roll back
                DB::rollBack();
                return $this->apiExceptionResponse(__('api/advertisers/account/account.something-wrong'));
            }
            DB::commit();

            return $this->apiResponse([
                'message' => __('api/advertisers/account/account.deleted'),
                'data' => null,
            ]);
        }
    }
}
