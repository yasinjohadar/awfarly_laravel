<?php

namespace App\Http\Controllers\API\Guests\Community\Offers;

use App\Helpers\Filter;
use App\Helpers\Geography\Geography;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Guests\Community\Offers\CommunityOffersResource;
use App\Http\Resources\Guests\Community\Offers\Reports\ReportedOffersResource;
use App\Models\Offers\Offer;
use App\Models\Users\Advertisers\AdvertiserUser;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CommunityOffersController extends Controller
{
    /**
     * @return Application|ResponseFactory|Response
     */
    public function getAllOffers(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('offers.pagination.limit', 10);

        $data = $request->only([
            'countryCode',
            'governorateId',
            'cityId',
            'categoryId',
        ]);

        $this->apiValidate($data, [
            'countryCode' => 'nullable|string|exists:countries,code',
            'governorateId' => 'nullable|string|exists:governorates,id',
            'cityId' => 'nullable|string|exists:cities,id',
            'categoryId' => 'nullable|string|exists:categories,id',
        ]);

        //get offers and paginate it
        $offers = Offer::select('offers.*')
            ->join('advertisers_users', function ($q) {
                return $q->on('advertisers_users.id', 'offers.advertiser_id')
                    ->where('advertisers_users.status', 'active')
                    ->where('advertisers_users.profile_privacy', 'public');
            })
            ->leftJoin('categories', 'categories.id', 'offers.category_id')
            ->where('offers.status', 'approved')
            ->where('offers.expires_at', '>', now());

        //Filter country code
        if (isset($data['countryCode'])) {
            $offers = $offers->where(function ($q) use ($data) {
                return $q->where('advertisers_users.country_code', $data['countryCode']);
            });
        }

        $offers = Geography::applyUserLocationFilter($offers, $data);


        //Filter city
        if (isset($data['categoryId'])) {
            $offers = $offers->where(function ($q) use ($data) {
                return $q->where('offers.category_id', $data['categoryId']);
            });
        }

        $offers = $offers->orderBy('advertisers_users.is_elite', 'desc')
            ->orderBy('offers.id', 'desc')
            ->groupBy('offers.id')
            ->paginate($limit);

        return $this->apiPaginateResponse(CommunityOffersResource::collection($offers));
    }

    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function getOfferById($id)
    {
        //get offer by id
        $offer = Offer::where('id', $id)
            ->first();

        //return error if offer wasn't found
        if (!$offer) {
            return $this->apiBadRequestResponse(__('api/customers/community/offers/offers.wrong-id'));
        }

        if ($offer->advertiser->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        if ($offer->advertiser->profile_privacy !== 'public') {
            return $this->apiBadRequestResponse(__('api/guests/community/offers/offers.user-permission'));
        }

        return $this->apiResponse(CommunityOffersResource::make($offer));
    }


    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function search(Request $request)
    {
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

        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('offers.pagination.limit', 10);

        //get hidden advertisers

        //get offers
        $offers = Offer::select([
            'offers.*'
        ])
            ->join('advertisers_users', function ($q) {
                return $q->on('advertisers_users.id', 'offers.advertiser_id')
                    ->where('advertisers_users.status', 'active')
                    ->where('advertisers_users.profile_privacy', 'public');
            })
            ->leftJoin('categories', 'categories.id', 'offers.category_id')
            ->where('offers.status', 'approved')
            ->where('offers.expires_at', '>', now());

        //filter keyword
        if (isset($data['keyword']) && !!trim($data['keyword'])) {
            $data['keyword'] = trim($data['keyword']);
            $offers = $offers->where(function ($q) use ($data) {
                return $q->where('offers.content', 'like', "%{$data['keyword']}%")
                    ->orWhere('advertisers_users.username', 'like', "%{$data['keyword']}%");
            });
        }

        //Filter country code
        if (isset($data['countryCode'])) {
            $offers = $offers->where(function ($q) use ($data) {
                return $q->where('advertisers_users.country_code', $data['countryCode']);
            });
        }

        $offers = Geography::applyUserLocationFilter($offers, $data);


        //Filter city
        if (isset($data['categoryId'])) {
            $offers = $offers->where(function ($q) use ($data) {
                return $q->where('offers.category_id', $data['categoryId']);
            });
        }

        //get the offers
        $offers = $offers->orderBy('advertisers_users.is_elite', 'desc')
            ->orderBy('offers.created_at')
            ->groupBy('offers.id')
            ->paginate($limit);

        return $this->apiPaginateResponse(CommunityOffersResource::collection($offers));
    }

    /**
     * get user offers by username
     * @param $username
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getOffersByUsername($username, Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('offers.pagination.limit', 10);

        //get advertiser that has this username
        $advertiser = AdvertiserUser::where('username', $username)
            ->where('status', 'active')
            ->first();

        //return error if user wasn't found
        if (!$advertiser) {
            return $this->apiBadRequestResponse(__('api/guests/community/offers/offers.wrong-username'));
        }

        if ($advertiser->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        if ($advertiser->profile_privacy !== 'public') {
            return $this->apiBadRequestResponse(__('api/guests/community/offers/offers.user-permission'));
        }
        //get offers
        $offers = $advertiser->offers()
            ->where('status', 'approved')
            ->where('offers.expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return $this->apiPaginateResponse(CommunityOffersResource::collection($offers));
    }

    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function reportOffer($id, Request $request)
    {
        //set data
        $data = $request->only([
            'reason',
            'type',
        ]);

        $data['offerId'] = $id;

        //validate data
        $this->apiValidate($data, [
            'offerId' => ['required', 'exists:offers,id'],
            'type' => ['nullable', 'in:Sexually Inappropriate,Abusive Content,Misleading or Scam,Offensive,Violence,Prohibited Content,Spam,False News,Other'],
            'reason' => ['nullable'],
        ]);

        //get offer
        $offer = Offer::where('id', $data['offerId'])
            ->first();

        //return error if offer wasn't found
        if (!$offer) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/offers/offers.wrong-id'));
        }

        if ($offer->advertiser->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }
        try {
            //create report
            $report = $offer->reports()
                ->create([
                    'reason' => $data['reason'] ? Filter::RemoveHtml($data['reason']) : null,
                    'type' => $data['type'] ?? null,
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/offers/offers.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/community/offers/offers.reports.report-added'),
            'data' => ReportedOffersResource::make($report),
        ]);
    }

}
