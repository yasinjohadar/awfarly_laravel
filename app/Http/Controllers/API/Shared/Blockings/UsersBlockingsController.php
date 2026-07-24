<?php

namespace App\Http\Controllers\API\Shared\Blockings;

use App\Http\Controllers\Controller;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsersBlockingsController extends Controller
{
    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function toggleBlock(Request $request)
    {
        //set data
        $data = $request->only([
            'userType',
            'userId',
            'isBlocked'
        ]);

        //validate data
        $this->apiValidate($data, [
            'userType' => ['required', 'in:advertiser,customer'],
            'userId' => ['required'],
            'isBlocked' => ['nullable', 'boolean'],
        ]);

        //get user
        $user = Auth::guard(Auth::user()->token()->name . '-api')->user();
        if (!$user) {
            return $this->apiExceptionResponse(__('api/shared/followings/followings.something-wrong'));
        }

        if ($data['userId'] == $user->id && $data['userType'] === $user->user_type) {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.blocks.self-block'));
        }

        if ($user->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }
        if ($data['userType'] === 'customer') {
            $blocked = CustomerUser::query()
                ->where('id', $data['userId'])
                ->first();
        } else {
            $blocked = AdvertiserUser::query()
                ->where('id', $data['userId'])
                ->first();
        }
        if (!$blocked) {
            return $this->apiBadRequestResponse(__('api/auth/auth.no-user'));
        }

        DB::beginTransaction();
        try {
            $user_blocked = $user->block()
                ->where('blocked_id', $data['userId'])
                ->where('blocked_type', $blocked->class)
                ->first();

            if ($request->has('isBlocked')) {
                if ($request->get('isBlocked')) {
                    if (!$user_blocked) {
                        $user->block()
                            ->create([
                                'blocked_id' => $data['userId'],
                                'blocked_type' => $blocked->class,
                            ]);
                    }
                    $isBlocked = true;
                    $type = __('api/advertisers/advertisers/advertisers.blocks.block');
                } else {
                    if ($user_blocked) {
                        $user_blocked->delete();
                    }
                    $isBlocked = false;
                    $type = __('api/advertisers/advertisers/advertisers.blocks.unblock');
                }
            } else {
                if ($user_blocked) {
                    $user_blocked->delete();
                    $isBlocked = false;
                    $type = __('api/advertisers/advertisers/advertisers.blocks.unblock');
                } else {
                    $user->block()
                        ->create([
                            'blocked_id' => $data['userId'],
                            'blocked_type' => $blocked->class,
                        ]);
                    $isBlocked = true;
                    $type = __('api/advertisers/advertisers/advertisers.blocks.block');
                }

            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/advertisers/advertisers.something-wrong'));
        }
        DB::commit();
        $blocked_type = $data['userType'] === 'customer' ? 'customers' : 'advertisers';
        return $this->apiResponse([
            'message' => __("api/advertisers/{$blocked_type}/{$blocked_type}.blocks.toggle", ['toggle' => $type]),
            'data' => [
                'isBlocked' => $isBlocked,
            ],
        ]);
    }
}
