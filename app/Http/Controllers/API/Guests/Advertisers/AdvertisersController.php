<?php

namespace App\Http\Controllers\API\Guests\Advertisers;

use App\Helpers\Categories\CategoriesFilter;
use App\Helpers\Filter;
use App\Helpers\Geography\Geography;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Shared\UsersRatings\AdvertisersRatersResource;
use App\Http\Resources\Users\Advertisers\AdvertisersResource;
use App\Http\Resources\Users\Advertisers\Reports\ReportedAdvertisersResource;
use App\Models\Users\Advertisers\AdvertiserUser;
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
            'governorateId',
            'cityId',
            'categoryId',
            'isGetAllCategories'
        ]);

        $this->apiValidate($data, [
            'categoryId' => 'nullable|string|exists:categories,id',
            'countryCode' => 'nullable|string|exists:countries,code',
            'governorateId' => 'nullable|string|exists:governorates,id',
            'cityId' => 'nullable|string|exists:cities,id',
            'isGetAllCategories' => ['nullable'],
        ]);
        //get all elite advertisers
        $advertisers = AdvertiserUser::select('advertisers_users.*')
            ->where('is_elite', true)
            ->where('status', 'active')
            ->leftJoin('advertiser_categories', 'advertiser_categories.advertiser_id', 'advertisers_users.id');;


        //Filter country code
        if (isset($data['countryCode']) && $data['countryCode']) {
            $advertisers = $advertisers->where(function ($q) use ($data) {
                return $q->where('advertisers_users.country_code', $data['countryCode']);
            });
        }

        $advertisers = Geography::applyUserLocationFilter($advertisers, $data);


        // Filter categories (expand parents to children)
        $advertisers = CategoriesFilter::applyFeedAdvertiserCategoryFilter($advertisers, $data, null);

        //get the advertisers
        $advertisers = $advertisers->orderBy('advertisers_users.is_elite', 'desc')
            ->groupBy('advertisers_users.id')
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
            'governorateId',
            'cityId',
            'categoryId',
        ]);

        $this->apiValidate($data, [
            'keyword' => 'nullable|string|min:3',
            'countryCode' => 'nullable|string|exists:countries,code',
            'governorateId' => 'nullable|string|exists:governorates,id',
            'cityId' => 'nullable|string|exists:cities,id',
            'categoryId' => 'nullable|string|exists:categories,id',
        ]);

        //get advertisers
        $advertisers = AdvertiserUser::select('advertisers_users.*')
            ->where('status', 'active')
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

        $advertisers = Geography::applyUserLocationFilter($advertisers, $data);


        // Filter categories (expand parents to children)
        $advertisers = CategoriesFilter::applyFeedAdvertiserCategoryFilter($advertisers, $data, null);

        //get the posts
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
            'governorateId',
            'cityId',
            'categoryId',
            'isGetAllCategories'
        ]);

        $this->apiValidate($data, [
            'categoryId' => 'nullable|string|exists:categories,id',
            'countryCode' => 'nullable|string|exists:countries,code',
            'governorateId' => 'nullable|string|exists:governorates,id',
            'cityId' => 'nullable|string|exists:cities,id',
            'isGetAllCategories' => ['nullable'],
        ]);

        //get all elite advertisers
        $advertisers = AdvertiserUser::select('advertisers_users.*')
            ->where('status', 'active')
            ->leftJoin('advertiser_categories', 'advertiser_categories.advertiser_id', 'advertisers_users.id');

        //Filter country code
        if (isset($data['countryCode']) && $data['countryCode']) {
            $advertisers = $advertisers->where(function ($q) use ($data) {
                return $q->where('advertisers_users.country_code', $data['countryCode']);
            });
        }

        $advertisers = Geography::applyUserLocationFilter($advertisers, $data);


        // Filter categories (expand parents to children)
        $advertisers = CategoriesFilter::applyFeedAdvertiserCategoryFilter($advertisers, $data, null);

        //get the advertisers
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
            return $this->apiBadRequestResponse(__('api/guests/advertisers/advertisers.wrong-id'));
        }

        //return error if account is closed
        if ($advertiser->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/guests/advertisers/advertisers.status-closed'));
        } elseif ($advertiser->status === 'banned') //return error if account is banned
        {
            return $this->apiBadRequestResponse(__('api/guests/advertisers/advertisers.status-banned'));
        }/* else if ($advertiser->profile_privacy !== 'public') {
            return $this->apiBadRequestResponse(__('api/guests/advertisers/advertisers.profile-privacy', ['privacy' => $advertiser->profile_pricacy]));
        }*/


        return $this->apiResponse(AdvertisersResource::make($advertiser));
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

        //return error if account is closed
        if ($advertiser->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.status-closed'));
        } elseif ($advertiser->status === 'banned') //return error if account is banned
        {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.status-banned'));
        }

        $raters = $advertiser->rating()
            ->whereHasMorph('user', '*', function ($q) {
                return $q->where('status', 'active');
            })
            ->where('status', 'approved')
            ->paginate($limit);

        return $this->apiPaginateResponse(AdvertisersRatersResource::collection($raters));
    }
}
