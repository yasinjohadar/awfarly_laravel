<?php

namespace App\Http\Controllers\API\Customers\Community\Offers;

use App\Helpers\Filter;
use App\Helpers\Categories\CategoriesFilter;
use App\Helpers\Geography\Geography;
use App\Helpers\Interests\InterestsFilter;
use App\Helpers\Notifications;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Customers\Community\Offers\CommunityOffersResource;
use App\Http\Resources\Customers\Community\Offers\Reports\ReportedOffersResource;
use App\Models\Offers\Offer;
use App\Models\Offers\Ratings\OfferRatings;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
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

        //get hidden advertisers
        $hidden_advertisers = Auth::guard('customer-api')->user()
            ->hiddenAdvertisers()
            ->pluck('advertiser_id')
            ->toArray();

        //get followed advertisers
        $followed_advertisers = Auth::guard('customer-api')->user()
            ->followed()
            ->where('followed_type', AdvertiserUser::class)
            ->where('status', 'approved')
            ->pluck('followed_id');

        $data = $request->only([
            'countryCode',
            'governorateId',
            'cityId',
            'categoryId',
            'isGetAllCategories',
            'interestId',
            'isGetAllInterests'
        ]);

        $this->apiValidate($data, [
            'countryCode' => 'nullable|string|exists:countries,code',
            'governorateId' => 'nullable|string|exists:governorates,id',
            'cityId' => 'nullable|string|exists:cities,id',
            'categoryId' => 'nullable|string|exists:categories,id',
            'isGetAllCategories' => ['nullable'],
            'interestId' => 'nullable|string|exists:interests,id',
            'isGetAllInterests' => ['nullable'],
        ]);

        $blocked_advertisers = Auth::guard('customer-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers = Auth::guard('customer-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $blocks = array_unique([...$blockers, ...$blocked_advertisers, ...$hidden_advertisers]);
        //get offers and paginate it
        $offers = Offer::select('offers.*')
            ->join('advertisers_users', function ($q) use ($blocks, $followed_advertisers) {
                return $q->on('advertisers_users.id', 'offers.advertiser_id')
                    ->where(function ($query) use ($followed_advertisers) {
                        return $query->where('advertisers_users.profile_privacy', 'public')
                            ->orWhere(function ($q) use ($followed_advertisers) {
                                return $q->where('advertisers_users.profile_privacy', 'followers')
                                    ->whereIn('advertisers_users.id', $followed_advertisers);
                            });
                    })
                    ->where('advertisers_users.status', 'active')
                    ->whereNotIn('advertiser_id', $blocks);
            })
            ->leftJoin('categories', 'categories.id', 'offers.category_id')
            ->where('offers.status', 'approved')
            ->where('offers.expires_at', '>', now());

        //Filter country code
        if (isset($data['countryCode']) && $data['countryCode']) {
            $offers = $offers->where(function ($q) use ($data) {
                return $q->where('advertisers_users.country_code', $data['countryCode']);
            });
        }

        $offers = Geography::applyUserLocationFilter($offers, $data);

        if (!Geography::hasExplicitLocationFilter($data)) {
            $offers = Geography::applyPreferredUserLocationFilter(
                $offers,
                Auth::guard('customer-api')->user()
            );
        }


        // Filter categories (expand parents to children; apply interests by default)
        $offers = CategoriesFilter::applyFeedCategoryFilter(
            $offers,
            $data,
            Auth::guard('customer-api')->user(),
            'offers.category_id'
        );

        // Filter by interests (matches advertisers whose own selected interests overlap with the customer's)
        $offers = InterestsFilter::applyFeedInterestFilter(
            $offers,
            $data,
            Auth::guard('customer-api')->user(),
            'advertisers_users'
        );

        $offers = $offers->orderBy('advertisers_users.is_elite', 'desc')
            ->orderBy('offers.id', 'desc')
            ->groupBy('offers.id')
            ->paginate($limit);

        //add offer view or update it
        foreach ($offers as $offer) {
            $view_added = Auth::guard('customer-api')->user()
                ->viewedOffers()
                ->where('offer_id', $offer->id)
                ->first();

            if (!$view_added) {
                $offer->views_count += 1;
                $offer->save();
                Auth::guard('customer-api')->user()
                    ->viewedOffers()
                    ->create([
                        'offer_id' => $offer->id
                    ]);
            }
        }

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
            ->join('advertisers_users', function ($q) {
                return $q->on('advertisers_users.id', 'offers.advertiser_id')
                    ->where('advertisers_users.status', 'active');
            })
            ->first();

        //return error if offer wasn't found
        if (!$offer) {
            return $this->apiBadRequestResponse(__('api/customers/community/offers/offers.wrong-id'));
        }
        $block = Auth::guard('customer-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->where('blocked_id', $offer->advertiser_id)
            ->exists();

        $blocked = Auth::guard('customer-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->where('blocker_id', $offer->advertiser_id)
            ->exists();

        if ($block || $blocked) {
            return $this->apiBadRequestResponse(__('api/customers/advertisers/advertisers.unavailable'));
        }
        //add offer view or update it
        $view_added = Auth::guard('customer-api')->user()
            ->viewedOffers()
            ->where('offer_id', $offer->id)
            ->first();

        if (!$view_added) {
            $offer->views_count += 1;
            $offer->save();
            Auth::guard('customer-api')->user()
                ->viewedOffers()
                ->create([
                    'offer_id' => $offer->id
                ]);
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
            'isGetAllCategories',
            'interestId',
            'isGetAllInterests',
        ]);

        $this->apiValidate($data, [
            'keyword' => 'nullable|string|min:3',
            'countryCode' => 'nullable|string|exists:countries,code',
            'governorateId' => 'nullable|string|exists:governorates,id',
            'cityId' => 'nullable|string|exists:cities,id',
            'categoryId' => 'nullable|string|exists:categories,id',
            'isGetAllCategories' => ['nullable'],
            'interestId' => 'nullable|string|exists:interests,id',
            'isGetAllInterests' => ['nullable'],
        ]);

        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('offers.pagination.limit', 10);

        //get hidden advertisers
        $hidden_advertisers = Auth::guard('customer-api')->user()
            ->hiddenAdvertisers()
            ->pluck('advertiser_id')
            ->toArray();

        $blocked_advertisers = Auth::guard('customer-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers = Auth::guard('customer-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $blocks = array_unique([...$blockers, ...$blocked_advertisers, ...$hidden_advertisers]);

        //get followed advertisers
        $followed_advertisers = Auth::guard('customer-api')->user()
            ->followed()
            ->where('followed_type', AdvertiserUser::class)
            ->where('status', 'approved')
            ->pluck('followed_id');


        //get offers
        $offers = Offer::select([
            'offers.*'
        ])
            ->join('advertisers_users', function ($q) use ($blocks, $followed_advertisers) {
                return $q->on('advertisers_users.id', 'offers.advertiser_id')
                    ->where(function ($query) use ($followed_advertisers) {
                        return $query->where('advertisers_users.profile_privacy', 'public')
                            ->orWhere(function ($q) use ($followed_advertisers) {
                                return $q->where('advertisers_users.profile_privacy', 'followers')
                                    ->whereIn('advertisers_users.id', $followed_advertisers);
                            });
                    })
                    ->where('advertisers_users.status', 'active')
                    ->whereNotIn('advertiser_id', $blocks);
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
        if (isset($data['countryCode']) && $data['countryCode']) {
            $offers = $offers->where(function ($q) use ($data) {
                return $q->where('advertisers_users.country_code', $data['countryCode']);
            });
        }

        $offers = Geography::applyUserLocationFilter($offers, $data);

        if (!Geography::hasExplicitLocationFilter($data)) {
            $offers = Geography::applyPreferredUserLocationFilter(
                $offers,
                Auth::guard('customer-api')->user()
            );
        }


        // Filter categories (expand parents to children; apply interests by default)
        $offers = CategoriesFilter::applyFeedCategoryFilter(
            $offers,
            $data,
            Auth::guard('customer-api')->user(),
            'offers.category_id'
        );

        // Filter by interests (matches advertisers whose own selected interests overlap with the customer's)
        $offers = InterestsFilter::applyFeedInterestFilter(
            $offers,
            $data,
            Auth::guard('customer-api')->user(),
            'advertisers_users'
        );

        //get the offers
        $offers = $offers->orderBy('advertisers_users.is_elite', 'desc')
            ->orderBy('offers.created_at')
            ->groupBy('offers.id')
            ->paginate($limit);

        //add offer view or update it
        foreach ($offers as $offer) {
            $view_added = Auth::guard('customer-api')->user()
                ->viewedOffers()
                ->where('offer_id', $offer->id)
                ->first();
            if (!$view_added) {
                $offer->views_count += 1;
                $offer->save();
                Auth::guard('customer-api')->user()
                    ->viewedOffers()
                    ->create([
                        'offer_id' => $offer->id
                    ]);
            }
        }


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
            return $this->apiBadRequestResponse(__('api/customers/community/offers/offers.wrong-username'));
        }

        if ($advertiser->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        //get followed advertisers
        $followed_advertisers = Auth::guard('customer-api')->user()
            ->followed()
            ->where('followed_type', AdvertiserUser::class)
            ->where('status', 'approved')
            ->pluck('followed_id');

        $blocked_advertisers = Auth::guard('customer-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->pluck('blocked_id')
            ->toArray();

        $blockers = Auth::guard('customer-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->pluck('blocker_id')
            ->toArray();

        $blocks = array_unique([...$blockers, ...$blocked_advertisers]);
        //get offers
        $offers = $advertiser->offers()
            ->join('advertisers_users', function ($q) use ($followed_advertisers, $blocks) {
                return $q->on('advertisers_users.id', 'offers.advertiser_id')
                    ->where(function ($query) use ($followed_advertisers) {
                        return $query->where('advertisers_users.profile_privacy', 'public')
                            ->orWhere(function ($q) use ($followed_advertisers) {
                                return $q->where('advertisers_users.profile_privacy', 'followers')
                                    ->whereIn('advertisers_users.id', $followed_advertisers);
                            });
                    })
                    ->whereNotIn('offers.advertiser_id', $blocks);
            })
            ->where('offers.status', 'approved')
            ->where('offers.expires_at', '>', now())
            ->orderBy('offers.created_at', 'desc')
            ->paginate($limit);

        //add offer view or update it
        foreach ($offers as $offer) {
            $view_added = Auth::guard('customer-api')->user()
                ->viewedOffers()
                ->where('offer_id', $offer->id)
                ->first();

            if (!$view_added) {
                $offer->views_count += 1;
                $offer->save();
                Auth::guard('customer-api')->user()
                    ->viewedOffers()
                    ->create([
                        'offer_id' => $offer->id
                    ]);
            }
        }

        return $this->apiPaginateResponse(CommunityOffersResource::collection($offers));
    }

    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function toggleLikeOffer($id, Request $request)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

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

        DB::beginTransaction();
        try {
            $like = Auth::guard('customer-api')->user()
                ->offersLikes()
                ->where('offer_id', $id)
                ->first();

            if ($request->has('isLiked')) {
                if ($request->get('isLiked')) {
                    if (!$like) {
                        Auth::guard('customer-api')->user()
                            ->offersLikes()
                            ->create([
                                'offer_id' => $id
                            ]);
                        $offer->likes_count += 1;
                    }
                    $isLiked = true;
                    $type = __('api/customers/community/offers/offers.liked');
                } else {
                    if ($like) {
                        $like->delete();
                        $offer->likes_count -= 1;
                    }
                    $isLiked = false;
                    $type = __('api/customers/community/offers/offers.disliked');
                }
            } else {
                if ($like) {
                    $like->delete();
                    $offer->likes_count -= 1;
                    $isLiked = false;
                    $type = __('api/customers/community/offers/offers.disliked');
                } else {
                    Auth::guard('customer-api')->user()
                        ->offersLikes()
                        ->create([
                            'offer_id' => $id
                        ]);
                    $offer->likes_count += 1;
                    $isLiked = true;
                    $type = __('api/customers/community/offers/offers.liked');
                }

            }
            $offer->save();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/community/offers/offers.something-wrong'));
        }
        DB::commit();

        if ($isLiked) {
            $customProperties = [
                'offerId' => $offer->id,
                'userId' => Auth::guard('customer-api')->id(),
                'userType' => 'customer',
            ];
            $advertiser = $offer->advertiser;

            $advertiser->notifications()
                ->whereJsonContains('data->customProperties->offerId', $offer->id)
                ->whereJsonContains('data->action', 'like')
                ->delete();

            Notifications::sendForCommunity($offer->advertiser, 'offers', 'offers.like', 'like', $customProperties);
        }
        return $this->apiResponse([
            'message' => __('api/customers/community/offers/offers.like-toggle', ['toggle' => $type]),
            'data' => [
                'isLiked' => $isLiked,
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function reportOffer(Request $request)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $user = Auth::guard('customer-api')->user();
        //set data
        $data = $request->only([
            'offerId',
            'type',
            'reason',
        ]);

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
            return $this->apiBadRequestResponse(__('api/customers/community/offers/offers.wrong-id'));
        }

        if ($offer->advertiser->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        //check if user already reported
        $report = $offer->reports()
            ->where('user_type', CustomerUser::class)
            ->where('user_id', $user->id)
            ->first();
        try {
            //create report
            if (!$report) {
                $report = $offer->reports()
                    ->create([
                        'user_type' => AdvertiserUser::class,
                        'user_id' => $user->id,
                        'type' => $data['type'] ?? null,
                        'reason' => $data['reason'] ? Filter::RemoveHtml($data['reason']) : null,
                    ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/community/offers/offers.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/customers/community/offers/offers.reports.report-added'),
            'data' => ReportedOffersResource::make($report),
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function editOfferReport(Request $request, $id)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $user = Auth::guard('customer-api')->user();

        //set data
        $data = $request->only([
            'reason',
        ]);

        //validate data
        $this->apiValidate($data, [
            'reason' => ['nullable'],
        ]);

        //check if user already reported
        $report = $user->reports()
            ->where('id', $id)
            ->first();

        //return error if offer wasn't found
        if (!$report) {
            return $this->apiBadRequestResponse(__('api/customers/community/offers/offers.reports.no-report'));
        }

        try {
            //create report
            $report->update([
                'reason' => $data['reason'] ? Filter::RemoveHtml($data['reason']) : null,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/community/offers/offers.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/customers/community/offers/offers.reports.report-edited'),
            'data' => ReportedOffersResource::make($report),
        ]);
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getReportedOffers(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('reported.offers.pagination.limit', 10);

        $user = Auth::guard('customer-api')->user();

        $reports = $user->reports()
            ->where('reported_type', Offer::class)
            ->paginate($limit);

        return $this->apiResponse(ReportedOffersResource::collection($reports));

    }

    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function rateOffers($id, Request $request)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //set data
        $data = $request->all();

        $data['offerId'] = $id;

        $this->apiValidate($data, [
            'offerId' => ['required', 'exists:offers,id'],
            'comment' => ['nullable'],
            'rate' => ['required', 'numeric', 'max:5']
        ]);

        //check whether user already rated this offer or not
        $already_rated = Auth::guard('customer-api')->user()
            ->offersRated()
            ->where('offer_id', $data['offerId'])
            ->exists();

        //return error if already rated
        if ($already_rated) {
            return $this->apiBadRequestResponse(__('api/customers/community/offers/offers.rating.already-rated'));
        }

        //get user by id
        $offer = Offer::withTrashed()
            ->where('id', $data['offerId'])
            ->first();

        if ($offer->advertiser->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        DB::beginTransaction();
        try {
            //check whether rate should be auto approved or not
            $auto_approve = Settings::Get('offers.rate.auto.approve', true);

            //add the rate
            Auth::guard('customer-api')->user()
                ->offersRated()
                ->create([
                    'offer_id' => $data['offerId'],
                    'comment' => $data['comment'],
                    'rate' => $data['rate'],
                    'status' => $auto_approve ? 'approved' : 'pending',
                ]);
            //update the rate if it's auto approve
            if ($auto_approve) {
                //get average rate
                $average_rate = OfferRatings::where('offer_id', $data['offerId'])
                    ->avg('rate');

                //update offer rate
                $offer->update([
                    'rate' => $average_rate,
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/community/offers/offers.rating.something-wrong'));
        }
        DB::commit();


        if ($auto_approve) {
            $customProperties = [
                'offerId' => $id,
                'userId' => Auth::guard('customer-api')->id(),
                'userType' => 'customer',
            ];

            Notifications::sendForCommunity($offer->advertiser, 'offers.rates', 'offers.rates.offer_rate', 'add', $customProperties);
        }
        return $this->apiResponse([
            'message' => __('api/customers/community/offers/offers.rating.rated-successfully'),
            'data' => [
                'rate' => $offer->rate,
                'isRateApproved' => $auto_approve,
            ]
        ]);
    }
}
