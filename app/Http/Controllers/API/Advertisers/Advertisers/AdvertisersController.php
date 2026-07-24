<?php

namespace App\Http\Controllers\API\Advertisers\Advertisers;

use App\Helpers\Filter;
use App\Helpers\Notifications;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Users\Advertisers\AdvertisersResource;
use App\Http\Resources\Shared\UsersRatings\AdvertisersRatedResource;
use App\Http\Resources\Shared\UsersRatings\AdvertisersRatersResource;
use App\Http\Resources\Users\Advertisers\Reports\ReportedAdvertisersResource;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\Ratings\AdvertiserRatings;
use App\Models\Users\Customers\CustomerUser;
use Auth;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AdvertisersController extends Controller
{
    /**
     * @return Application|ResponseFactory|Response
     */
    public function getEliteAdvertisers(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('advertisers.pagination.limit', 10);

        $data = $request->only([
            'countryCode',
            'cityId',
            'categoryId',
            'isGetAllCategories'
        ]);

        $this->apiValidate($data, [
            'categoryId' => 'nullable|string|exists:categories,id',
            'countryCode' => 'nullable|string|exists:countries,code',
            'cityId' => 'nullable|string|exists:cities,id',
            'isGetAllCategories' => ['nullable'],
        ]);
        //get followed advertisers
        $followed_advertisers = Auth::guard('advertiser-api')->user()
            ->followed()
            ->where('followed_type', AdvertiserUser::class)
            ->where('status', 'approved')
            ->pluck('followed_id');

        $blocked_advertisers = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $blocks = array_unique([...$blockers, ...$blocked_advertisers]);

        //get all elite advertisers
        $advertisers = AdvertiserUser::query()
            ->select('advertisers_users.*')
            ->whereNotIn('advertisers_users.id', $blocks)
            ->where('advertisers_users.is_elite', true)
            ->where('advertisers_users.status', 'active')
            ->leftJoin('advertiser_categories', 'advertiser_categories.advertiser_id', 'advertisers_users.id');


        //Filter country code
        if (isset($data['countryCode']) && $data['countryCode']) {
            $advertisers = $advertisers->where(function ($q) use ($data) {
                return $q->where('advertisers_users.country_code', $data['countryCode']);
            });
        }

        //Filter city
        if (isset($data['cityId']) && $data['cityId']) {
            $advertisers = $advertisers->where(function ($q) use ($data) {
                return $q->where('advertisers_users.city_id', $data['cityId']);
            });
        }

        //Filter Categories
        if (isset($data['categoryId']) && $data['categoryId']) {
            $advertisers = $advertisers->where(function ($q) use ($data) {
                return $q->where('advertiser_categories.category_id', $data['categoryId']);
            });
        } else if (isset($data['isGetAllCategories']) && $data['isGetAllCategories'] == false) {
            $categories = Auth::guard('advertiser-api')->user()
                ->categories()
                ->pluck('category_id')
                ->toArray();

            if (sizeof($categories) > 0) {
                $advertisers = $advertisers->where(function ($q) use ($data, $categories) {
                    return $q->whereIn('advertiser_categories.category_id', $categories);
                });
            }
        }


        $advertisers = $advertisers->groupBy('advertisers_users.id')
            ->paginate($limit);

        return $this->apiPaginateResponse(AdvertisersResource::collection($advertisers));
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function search(Request $request)
    {
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('advertisers.pagination.limit', 10);

        $data = $request->only([
            'page',
            'keyword',
            'countryCode',
            'cityId',
            'isGetAllCategories',
            'categoryId',
        ]);

        $this->apiValidate($data, [
            'keyword' => 'nullable|string|min:3',
            'countryCode' => 'nullable|string|exists:countries,code',
            'cityId' => 'nullable|string|exists:cities,id',
            'isGetAllCategories' => ['nullable'],
            'categoryId' => 'nullable|string|exists:categories,id',
        ]);
        //get followed advertisers
        $followed_advertisers = Auth::guard('advertiser-api')->user()
            ->followed()
            ->where('followed_type', AdvertiserUser::class)
            ->where('status', 'approved')
            ->pluck('followed_id');
        $blocked_advertisers = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $blocks = array_unique([...$blockers, ...$blocked_advertisers]);

        //get advertisers
        $advertisers = AdvertiserUser::query()
            ->select('advertisers_users.*')
            ->whereNotIn('advertisers_users.id', $blocks)
            ->where('advertisers_users.status', 'active')
            ->leftJoin('advertiser_categories', 'advertiser_categories.advertiser_id', 'advertisers_users.id');

        //filter keyword
        if (isset($data['keyword']) && !!trim($data['keyword'])) {
            $data['keyword'] = trim($data['keyword']);
            $advertisers = $advertisers->where(function ($q) use ($data) {
                return $q->where('advertisers_users.name', 'like', "%{$data['keyword']}%")
                    ->orWhere('advertisers_users.username', 'like', "%{$data['keyword']}%")
                    ->orWhere('advertisers_users.bio', 'like', "%{$data['keyword']}%");
            });
        }

        //Filter country code
        if (isset($data['countryCode']) && $data['countryCode']) {
            $advertisers = $advertisers->where(function ($q) use ($data) {
                return $q->where('advertisers_users.country_code', $data['countryCode']);
            });
        }

        //Filter city
        if (isset($data['cityId']) && $data['cityId']) {
            $advertisers = $advertisers->where(function ($q) use ($data) {
                return $q->where('advertisers_users.city_id', $data['cityId']);
            });
        }

        //Filter Categories
        if (isset($data['categoryId']) && $data['categoryId']) {
            $advertisers = $advertisers->where(function ($q) use ($data) {
                return $q->where('advertiser_categories.category_id', $data['categoryId']);
            });
        } else if (isset($data['isGetAllCategories']) && $data['isGetAllCategories'] == false) {
            $categories = Auth::guard('advertiser-api')->user()
                ->categories()
                ->pluck('category_id')
                ->toArray();

            if (sizeof($categories) > 0) {
                $advertisers = $advertisers->where(function ($q) use ($data, $categories) {
                    return $q->whereIn('advertiser_categories.category_id', $categories);
                });
            }
        }

        //get the advertisers
        $advertisers = $advertisers->orderBy('advertisers_users.is_elite', 'desc')
            ->groupBy('advertisers_users.id')
            ->paginate($limit);

        return $this->apiPaginateResponse(AdvertisersResource::collection($advertisers));
    }

    /**
     * @return Application|ResponseFactory|Response
     */
    public function getAdvertisers(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('advertisers.pagination.limit', 10);

        $data = $request->only([
            'countryCode',
            'cityId',
            'categoryId',
            'isGetAllCategories'
        ]);

        $this->apiValidate($data, [
            'categoryId' => 'nullable|string|exists:categories,id',
            'countryCode' => 'nullable|string|exists:countries,code',
            'cityId' => 'nullable|string|exists:cities,id',
            'isGetAllCategories' => ['nullable'],
        ]);
        //get followed advertisers
        $followed_advertisers = Auth::guard('advertiser-api')->user()
            ->followed()
            ->where('followed_type', AdvertiserUser::class)
            ->where('status', 'approved')
            ->pluck('followed_id');

        $blocked_advertisers = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $blocks = array_unique([...$blockers, ...$blocked_advertisers]);

        //get all elite advertisers
        $advertisers = AdvertiserUser::query()
            ->select('advertisers_users.*')
            ->whereNotIn('advertisers_users.id', $blocks)
            ->where('advertisers_users.status', 'active')
            ->leftJoin('advertiser_categories', 'advertiser_categories.advertiser_id', 'advertisers_users.id');;


        //Filter country code
        if (isset($data['countryCode']) && $data['countryCode']) {
            $advertisers = $advertisers->where(function ($q) use ($data) {
                return $q->where('advertisers_users.country_code', $data['countryCode']);
            });
        }

        //Filter city
        if (isset($data['cityId']) && $data['cityId']) {
            $advertisers = $advertisers->where(function ($q) use ($data) {
                return $q->where('advertisers_users.city_id', $data['cityId']);
            });
        }

        //Filter Categories
        if (isset($data['categoryId']) && $data['categoryId']) {
            $advertisers = $advertisers->where(function ($q) use ($data) {
                return $q->where('advertiser_categories.category_id', $data['categoryId']);
            });
        } else if (isset($data['isGetAllCategories']) && $data['isGetAllCategories'] == false) {
            $categories = Auth::guard('advertiser-api')->user()
                ->categories()
                ->pluck('category_id')
                ->toArray();

            if (sizeof($categories) > 0) {
                $advertisers = $advertisers->where(function ($q) use ($data, $categories) {
                    return $q->whereIn('advertiser_categories.category_id', $categories);
                });
            }
        }

        $advertisers = $advertisers->orderBy('advertisers_users.is_elite', 'desc')
            ->groupBy('advertisers_users.id')
            ->paginate($limit);

        return $this->apiPaginateResponse(AdvertisersResource::collection($advertisers));
    }

    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function getAdvertiserById($id)
    {
        //get user by id
        $advertiser = AdvertiserUser::where('id', $id)
            ->first();

        //return error if user wasn't found
        if (!$advertiser) {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.wrong-id'));
        }

        $block = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->where('blocked_id', $id)
            ->exists();

        $blocked = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->where('blocker_id', $id)
            ->exists();

        if ($block || $blocked) {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.unavailable'));
        }

        //return error if account is closed
        if ($advertiser->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.status-closed'));
        } elseif ($advertiser->status === 'banned') //return error if account is banned
        {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.status-banned'));
        }

        return $this->apiResponse(AdvertisersResource::make($advertiser));
    }

    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function rateAdvertisers($id, Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //set data
        $data = $request->all();

        $data['advertiserId'] = $id;

        $this->apiValidate($data, [
            'advertiserId' => ['required', 'exists:advertisers_users,id'],
            'comment' => ['nullable'],
            'rate' => ['required', 'numeric', 'max:5']
        ]);

        //return error if advertiser is trying to rate himself
        if ($data['advertiserId'] == Auth::guard('advertiser-api')->id()) {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.rating.not-allowed'));
        }

        //return  error if advertiser already rated
        $already_rated = Auth::guard('advertiser-api')->user()
            ->advertisersRated()
            ->where('advertiser_id', $data['advertiserId'])
            ->exists();

        if ($already_rated) {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.rating.already-rated'));
        }
        DB::beginTransaction();
        try {
            //check whether rate should be auto approved or not
            $auto_approve = Settings::Get('advertiser.rate.auto.approve', true);

            $rate = Auth::guard('advertiser-api')->user()
                ->advertisersRated()
                ->create([
                    'advertiser_id' => $data['advertiserId'],
                    'comment' => $data['comment'],
                    'rate' => $data['rate'],
                    'status' => $auto_approve ? 'approved' : 'pending',
                ]);

            //get user by id
            $advertiser = AdvertiserUser::where('id', $data['advertiserId'])
                ->first();

            if ($auto_approve) {
                //get average rate
                $average_rate = AdvertiserRatings::where('advertiser_id', $data['advertiserId'])
                    ->avg('rate');

                //update advertiser
                $advertiser->update([
                    'rate' => $average_rate,
                ]);
            }

        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/advertisers/advertisers.rating.something-wrong'));
        }
        DB::commit();

        if ($auto_approve) {
            $customProperties = [
                'userId' => Auth::guard('advertiser-api')->id(),
                'userType' => 'advertiser',
            ];

            Notifications::sendForCommunity($advertiser, 'advertisers.rates', 'advertisers.rates.advertiser_rate', 'add', $customProperties);
        }

        return $this->apiResponse([
            'message' => __('api/advertisers/advertisers/advertisers.rating.rated-successfully'),
            'data' => [
                'rate' => $advertiser->rate,
                'isRateApproved' => $auto_approve,
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function userRaters(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('advertisers.ratings.pagination.limit', 10);

        $blocked_advertisers = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers_advertisers = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $advertisers_blocks = array_unique([...$blockers_advertisers, ...$blocked_advertisers]);

        $blocked_customers = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', CustomerUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers_customers = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', CustomerUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $customers_blocks = array_unique([...$blockers_customers, ...$blocked_customers]);

        $rater_users = Auth::guard('advertiser-api')->user()
            ->rating()
            ->whereHasMorph('user', '*', function ($q, $type) use ($advertisers_blocks, $customers_blocks) {
                if ($type === AdvertiserUser::class) {
                    $q->whereNotIn('id', $advertisers_blocks);
                } else if ($type === CustomerUser::class) {
                    $q->whereNotIn('id', $customers_blocks);
                }
                $q->where('status', 'active');
            })
            ->where('status', 'approved')
            ->paginate($limit);

        return $this->apiPaginateResponse(AdvertisersRatersResource::collection($rater_users));
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function usersRated(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('advertisers.ratings.pagination.limit', 10);
        $blocked_advertisers = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers_advertisers = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $advertisers_blocks = array_unique([...$blockers_advertisers, ...$blocked_advertisers]);

        $rater_users = Auth::guard('advertiser-api')->user()
            ->advertisersRated()
            ->whereHas('advertiser', function ($q) use ($advertisers_blocks) {
                return $q->where('status', 'active')
                    ->whereNotIn('id', $advertisers_blocks);
            })
            ->where('status', 'approved')
            ->paginate($limit);

        return $this->apiPaginateResponse(AdvertisersRatedResource::collection($rater_users));
    }

    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getRatersByAdvertiserId($id, Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('advertisers.ratings.pagination.limit', 10);

        $advertiser = AdvertiserUser::where('id', $id)
            ->first();
        //return error if user wasn't found
        if (!$advertiser) {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.wrong-id'));
        }

        $block = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->where('blocked_id', $id)
            ->exists();

        $blocked = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->where('blocker_id', $id)
            ->exists();

        if ($block || $blocked) {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.unavailable'));
        }

        //return error if account is closed
        if ($advertiser->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.status-closed'));
        } elseif ($advertiser->status === 'banned') //return error if account is banned
        {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.status-banned'));
        }

        $blocked_advertisers = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers_advertisers = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $advertisers_blocks = array_unique([...$blockers_advertisers, ...$blocked_advertisers]);

        $blocked_customers = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', CustomerUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers_customers = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', CustomerUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $customers_blocks = array_unique([...$blockers_customers, ...$blocked_customers]);
        $raters = $advertiser->rating()
            ->whereHasMorph('user', '*', function ($q, $type) use ($advertisers_blocks, $customers_blocks) {
                if ($type === AdvertiserUser::class) {
                    $q->whereNotIn('id', $advertisers_blocks);
                } else if ($type === CustomerUser::class) {
                    $q->whereNotIn('id', $customers_blocks);
                }
                $q->where('status', 'active');
            })
            ->where('status', 'approved')
            ->paginate($limit);

        return $this->apiPaginateResponse(AdvertisersRatersResource::collection($raters));
    }
}
