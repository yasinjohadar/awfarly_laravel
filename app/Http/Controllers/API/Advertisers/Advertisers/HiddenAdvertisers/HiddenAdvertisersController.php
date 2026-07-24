<?php

namespace App\Http\Controllers\API\Advertisers\Advertisers\HiddenAdvertisers;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Advertisers\HiddenAdvertisers\HiddenAdvertisersResource;
use App\Models\Users\Advertisers\AdvertiserUser;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HiddenAdvertisersController extends Controller
{
    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function toggleHideAdvertisers($id, Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //get user
        $user = Auth::guard('advertiser-api')->user();

        //set data
        $data = $request->only([
            'isHidden'
        ]);

        $data['advertiserId'] = $id;

        //validate data
        $this->apiValidate($data, [
            'advertiserId' => ['required', 'exists:advertisers_users,id'],
            'isHidden' => ['nullable', 'boolean'],
        ]);


        //get advertiser
        $advertiser = AdvertiserUser::where('id', $data['advertiserId'])
            ->first();

        if ($advertiser->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        DB::beginTransaction();
        try {
            $hidden_post = $user->hiddenAdvertisers()
                ->where('advertiser_id', $data['advertiserId'])
                ->first();

            if ($request->has('isHidden')) {
                if ($request->get('isHidden')) {
                    if (!$hidden_post) {
                        $hidden_post = $user->hiddenAdvertisers()
                            ->create([
                                'advertiser_id' => $data['advertiserId'],
                            ]);
                    }
                    $isHidden = true;
                    $type = __('api/advertisers/advertisers/hidden-advertisers.hidden');
                } else {
                    if ($hidden_post) {
                        $hidden_post->delete();
                    }
                    $isHidden = false;
                    $type = __('api/advertisers/advertisers/hidden-advertisers.unhidden');
                }
            } else {
                if ($hidden_post) {
                    $hidden_post->delete();
                    $isHidden = false;
                    $type = __('api/advertisers/advertisers/hidden-advertisers.unhidden');
                } else {
                    $hidden_post = $user->hiddenAdvertisers()
                        ->create([
                            'advertiser_id' => $data['advertiserId'],
                        ]);
                    $isHidden = true;
                    $type = __('api/advertisers/advertisers/hidden-advertisers.hidden');
                }

            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/advertisers/hidden-advertisers.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/advertisers/hidden-advertisers.follow-toggle', ['toggle' => $type]),
            'data' => [
                'isHidden' => $isHidden,
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getHiddenAdvertisers(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('hidden.posts.pagination.limit', 10);

        //get hidden posts
        $hidden_posts = Auth::guard('advertiser-api')->user()
            ->hiddenAdvertisers()
            ->whereHas('advertiser', function ($q) {
                return $q->where('status', 'active');
            })
            ->paginate($limit);

        return $this->apiPaginateResponse(HiddenAdvertisersResource::collection($hidden_posts));
    }
}
