<?php

namespace App\Http\Controllers\API\Customers\Account;

use App\Helpers\Files;
use App\Helpers\Filter;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Customers\Account\AccountResource;
use App\Http\Resources\Users\Advertisers\Reports\ReportedAdvertisersResource;
use App\Http\Resources\Users\Customers\Reports\ReportedCustomersResource;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use MatanYadaev\EloquentSpatial\Objects\Point;

class AccountController extends Controller
{
    /**
     * @return Application|ResponseFactory|Response
     */
    public function getAccountData()
    {
        return $this->apiResponse(AccountResource::make(Auth::guard('customer-api')->user()));
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function updateAccount(Request $request)
    {
        $user = Auth::guard('customer-api')->user();

        //Set user data
        $data = $request->only([
            'name',
            'email',
            'image',
            'bio',
            'mobile',
            'countryCode',
            'cityId',
            'languageCode',
            'contactNumber',
            'whatsappNumber',
            'facebookUrl',
            'twitterUrl',
            'websiteUrl',
            'chatStatus',
            'profilePrivacy',
            'oldPassword',
            'password',
            'isAcceptedSendNotifications',
            'addressLatitude',
            'addressLongitude',
            'status',
            'deleteImage',
            'isDisableAccount',
            'interestedCategories',
            'gender',
            'birth_date',
            'latitude',
            'longitude',
        ]);

        //validate data
        $this->apiValidate($data, [
            'name' => ['nullable', 'string'],
            'image' => ['nullable', 'array'],
            'image.file' => ['image', 'max:15500'],
            'bio' => ['nullable'],
            'username' => ['nullable', "unique:customers_users,username,$user->id", 'unique:advertisers_users,username', 'unique:admins_users,username'],
            'email' => ['nullable', 'email:rfc,dns', "unique:customers_users,email,$user->id", 'unique:advertisers_users,email', 'unique:admins_users,email'],
            'mobile' => ['nullable', 'string', "unique:customers_users,mobile,$user->id", 'unique:advertisers_users,mobile', 'unique:admins_users,mobile', 'regex:^\+\d+$^'],
            'gender' => ['nullable','sometimes', 'string','in:male,female'],
            'birth_date' => ['nullable','sometimes', 'date'],
            'countryCode' => ['nullable', 'exists:countries,code'],
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
            'status' => ['nullable', 'in:active,inactive'],
            'deleteImage' => ['nullable', 'boolean'],
            'isDisableAccount' => ['nullable', 'boolean'],
            'interestedCategories' => ['nullable', 'array'],
            'interestedCategories.*' => ['exists:categories,id'],
            'latitude'  =>  'sometimes|nullable|string',
            'longitude'  =>  'sometimes|nullable|string',
        ]);

        //Validate the old password
        if ((!empty($request->get('password'))) && !Hash::check($request->get('oldPassword'), $user->getAuthPassword())) {
            return $this->apiBadRequestResponse(__('api/customers/account/account.incorrect-password'), false);
        }


        //change name
        if ($request->has('name') && (bool)Settings::Get('allow.users.change.name', true)) {
            if (empty($request->get('name'))) {
                $user->name = null;
            } elseif ($request->get('name')) {
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
        if ($request->hasFile('image')) {
            //Request delete old image
            if ($user->image) {
                Files::deleteS3File($user->image);
            }
            $data['image'] = $request->file('image.file')->storeAs('uploads/avatars', md5($request->file('image.file')->getClientOriginalName()));;
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

        if ($request->latitude && $request->longitude) {
                $user->location = new Point($request->latitude, $request->longitude, 4326);
        }

        //change city id
        if ($request->has('cityId')) {
            if (!empty($request->get('cityId'))) {
                $user->city_id = Filter::RemoveHtml($data['cityId']);
            }
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

        if ($request->has('birth_date')) {
            $user->birth_date = $data['birth_date'];
        }

        if ($request->has('gender')) {
            $user->gender = $data['gender'];
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

        //change status
        if ($request->has('isDisableAccount')) {
            $user->status = $request->get('isDisableAccount') ? 'inactive' : 'active';
        }

        //Begin Transaction
        DB::beginTransaction();
        try {
            //change category id
            if ($request->has('interestedCategories')) {
                $user->categories()
                    ->delete();
                foreach ($data['interestedCategories'] as $category) {
                    $user->categories()
                        ->updateOrCreate([
                            'category_id' => $category,
                        ]);
                }
            }
            //edit account
            $user->save();
        } catch (Exception $e) {
            //roll back
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/account/account.something-wrong'));
        }
        //commit
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/customers/account/account.edited'),
            'data' => AccountResource::make(Auth::guard('customer-api')->user()),
        ]);
    }

    /**
     * Set user status to online
     */
    public function ping()
    {
        DB::beginTransaction();
        try {
            Auth::guard('customer-api')->user()
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
        $user = Auth::guard('customer-api')->user();

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
