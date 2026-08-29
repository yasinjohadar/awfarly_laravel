<?php

namespace App\Http\Controllers\API\Advertisers\Community\Offers;

use App\Helpers\Advertisers\OfferLimits;
use App\Helpers\Files;
use App\Helpers\Filter;
use App\Helpers\Categories\CategoriesFilter;
use App\Helpers\Geography\Geography;
use App\Helpers\Notifications;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Advertisers\Community\Offers\CommunityOffersResource;
use App\Http\Resources\Advertisers\Community\Offers\Reports\ReportedOffersResource;
use App\Models\Offers\Offer;
use App\Models\Offers\Ratings\OfferRatings;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\Categories\AdvertiserCategories;
use Carbon\Carbon;
use Exception;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\Video\X264;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Image\Image;

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
        $hidden_advertisers = Auth::guard('advertiser-api')->user()
            ->hiddenAdvertisers()
            ->pluck('advertiser_id');

        $data = $request->only([
            'countryCode',
            'governorateId',
            'cityId',
            'categoryId',
            'isGetAllCategories',
            'isShowMyOffersOnly',
        ]);

        $this->apiValidate($data, [
            'countryCode' => 'nullable|string|exists:countries,code',
            'governorateId' => 'nullable|string|exists:governorates,id',
            'cityId' => 'nullable|string|exists:cities,id',
            'categoryId' => 'nullable|string|exists:categories,id',
            'isGetAllCategories' => ['nullable'],
            'isShowMyOffersOnly' => ['nullable', 'boolean'],
        ]);

        if (isset($data['isShowMyOffersOnly']) && $data['isShowMyOffersOnly']) {
            $offers = Auth::guard('advertiser-api')->user()
                ->offers()
                ->select('offers.*')
                ->join('advertisers_users', 'advertisers_users.id', 'offers.advertiser_id')
                ->leftJoin('categories', 'categories.id', 'offers.category_id');
        } else {
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
            //get offers and paginate it
            $offers = Offer::select('offers.*')
                ->join('advertisers_users', function ($q) use ($hidden_advertisers, $followed_advertisers, $blocks) {
                    return $q->on('advertisers_users.id', 'offers.advertiser_id')
                        ->where(function ($query) use ($followed_advertisers, $blocks) {
                            return $query->where('advertisers_users.profile_privacy', 'public')
                                ->orWhere(function ($q) use ($followed_advertisers) {
                                    return $q->where('advertisers_users.profile_privacy', 'followers')
                                        ->whereIn('advertisers_users.id', $followed_advertisers);
                                })
                                ->orWhere('advertisers_users.id', Auth::guard('advertiser-api')->id());
                        })
                        ->whereNotIn('advertisers_users.id', $blocks)
                        ->where('advertisers_users.status', 'active')
                        ->whereNotIn('offers.advertiser_id', $hidden_advertisers);
                })
                ->leftJoin('categories', 'categories.id', 'offers.category_id')
                ->where('offers.status', 'approved')
                ->where('offers.expires_at', '>', now());
        }

        //"My offers" means every offer I created — it must not be narrowed by
        //the discovery filters (my interests, my preferred locations). Only an
        //explicit filter the user picks from the dropdowns still applies.
        $isMine = isset($data['isShowMyOffersOnly']) && $data['isShowMyOffersOnly'];

        //Filter country code
        if (isset($data['countryCode']) && $data['countryCode']) {
            $offers = $offers->where(function ($q) use ($data) {
                return $q->where('advertisers_users.country_code', $data['countryCode']);
            });
        }

        $offers = Geography::applyUserLocationFilter($offers, $data);

        if (!$isMine && !Geography::hasExplicitLocationFilter($data)) {
            $offers = Geography::applyPreferredUserLocationFilter(
                $offers,
                Auth::guard('advertiser-api')->user()
            );
        }


        if ($isMine) {
            //only an explicitly chosen category narrows my own offers
            if (!empty($data['categoryId'])) {
                $offers = $offers->whereIn(
                    'offers.category_id',
                    CategoriesFilter::expandCategoryIds([(int) $data['categoryId']])
                );
            }
        } else {
            // Filter categories (expand parents to children; apply interests by default)
            $offers = CategoriesFilter::applyFeedCategoryFilter(
                $offers,
                $data,
                Auth::guard('advertiser-api')->user(),
                'offers.category_id'
            );
        }

        $offers = $offers->orderByRaw('DATE(offers.created_at) desc')
            ->orderBy('advertisers_users.is_elite', 'desc')
            ->orderBy('offers.created_at', 'desc')
            ->groupBy('offers.id')
            ->paginate($limit);

        //add offer view or update it
        foreach ($offers as $offer) {
            $view_added = Auth::guard('advertiser-api')->user()
                ->viewedOffers()
                ->where('offer_id', $offer->id)
                ->first();

            if (!$view_added) {
                $offer->views_count += 1;
                $offer->save();
                Auth::guard('advertiser-api')->user()
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
            ->first();

        //return error if offer wasn't found
        if (!$offer) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/offers/offers.wrong-id'));
        }

        if ($offer->advertiser->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }
        $block = Auth::guard('advertiser-api')->user()
            ->block()
            ->where('blocked_type', AdvertiserUser::class)
            ->where('blocked_id', $offer->advertiser_id)
            ->exists();

        $blocked = Auth::guard('advertiser-api')->user()
            ->blocked()
            ->where('blocker_type', AdvertiserUser::class)
            ->where('blocker_id', $offer->advertiser_id)
            ->exists();

        if ($block || $blocked) {
            return $this->apiBadRequestResponse(__('api/advertisers/advertisers/advertisers.unavailable'));
        }
        //add offer view or update it
        $view_added = Auth::guard('advertiser-api')->user()
            ->viewedOffers()
            ->where('offer_id', $offer->id)
            ->first();

        if (!$view_added) {
            $offer->views_count += 1;
            $offer->save();
            Auth::guard('advertiser-api')->user()
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
            'isGetAllCategories'
        ]);

        $this->apiValidate($data, [
            'keyword' => 'nullable|string|min:3',
            'countryCode' => 'nullable|string|exists:countries,code',
            'governorateId' => 'nullable|string|exists:governorates,id',
            'cityId' => 'nullable|string|exists:cities,id',
            'categoryId' => 'nullable|string|exists:categories,id',
            'isGetAllCategories' => ['nullable'],
        ]);

        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('offers.pagination.limit', 10);

        //get hidden advertisers
        $hidden_advertisers = Auth::guard('advertiser-api')->user()
            ->hiddenAdvertisers()
            ->pluck('advertiser_id');

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
        //get offers
        $offers = Offer::select([
            'offers.*'
        ])
            ->join('advertisers_users', function ($q) use ($hidden_advertisers, $followed_advertisers, $blocks) {
                return $q->on('advertisers_users.id', 'offers.advertiser_id')
                    ->where(function ($query) use ($followed_advertisers) {
                        return $query->where('advertisers_users.profile_privacy', 'public')
                            ->orWhere(function ($q) use ($followed_advertisers) {
                                return $q->where('advertisers_users.profile_privacy', 'followers')
                                    ->whereIn('advertisers_users.id', $followed_advertisers);
                            })
                            ->orWhere('advertisers_users.id', Auth::guard('advertiser-api')->id());
                    })
                    ->whereNotIn('advertisers_users.id', $blocks)
                    ->where('advertisers_users.status', 'active')
                    ->whereNotIn('offers.advertiser_id', $hidden_advertisers);
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
                Auth::guard('advertiser-api')->user()
            );
        }


        // Filter categories (expand parents to children; apply interests by default)
        $offers = CategoriesFilter::applyFeedCategoryFilter(
            $offers,
            $data,
            Auth::guard('advertiser-api')->user(),
            'offers.category_id'
        );

        //get the offers
        $offers = $offers->orderByRaw('DATE(offers.created_at) desc')
            ->orderBy('advertisers_users.is_elite', 'desc')
            ->orderBy('offers.created_at', 'desc')
            ->groupBy('offers.id')
            ->paginate($limit);

        //add offer view or update it
        foreach ($offers as $offer) {
            $view_added = Auth::guard('advertiser-api')->user()
                ->viewedOffers()
                ->where('offer_id', $offer->id)
                ->first();
            if (!$view_added) {
                $offer->views_count += 1;
                $offer->save();
                Auth::guard('advertiser-api')->user()
                    ->viewedOffers()
                    ->create([
                        'offer_id' => $offer->id
                    ]);
            }
        }

        return $this->apiPaginateResponse(CommunityOffersResource::collection($offers));
    }

    /**
     * get user offers
     * @return Application|ResponseFactory|Response
     */
    public function getUserOffers(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('offers.pagination.limit', 10);

        //get user offers
        $offers = Auth::guard('advertiser-api')->user()
            ->offers()
            ->where('offers.expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        //add offer view or update it
        foreach ($offers as $offer) {
            $view_added = Auth::guard('advertiser-api')->user()
                ->viewedOffers()
                ->where('offer_id', $offer->id)
                ->first();

            if (!$view_added) {
                $offer->views_count += 1;
                $offer->save();
                Auth::guard('advertiser-api')->user()
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
            return $this->apiBadRequestResponse(__('api/advertisers/community/offers/offers.wrong-username'));
        }
        //get followed advertisers
        $followed_advertisers = Auth::guard('advertiser-api')->user()
            ->followed()
            ->where('followed_type', AdvertiserUser::class)
            ->where('status', 'approved')
            ->pluck('followed_id');

        //cap to the advertiser's currently allowed active-offer count
        $cappedOfferIds = OfferLimits::cappedActiveOfferIds($advertiser);

        //get offers
        $offers = $advertiser->offers()
            ->join('advertisers_users', function ($q) use ($followed_advertisers) {
                return $q->on('advertisers_users.id', 'offers.advertiser_id')
                    ->where(function ($query) use ($followed_advertisers) {
                        return $query->where('advertisers_users.profile_privacy', 'public')
                            ->orWhere(function ($q) use ($followed_advertisers) {
                                return $q->where('advertisers_users.profile_privacy', 'followers')
                                    ->whereIn('advertisers_users.id', $followed_advertisers);
                            })
                            ->orWhere('advertisers_users.id', Auth::guard('advertiser-api')->id());
                    });
            })
            ->where('offers.status', 'approved')
            ->where('offers.expires_at', '>', now())
            ->whereIn('offers.id', $cappedOfferIds)
            ->orderBy('offers.created_at', 'desc')
            ->paginate($limit);

        //add offer view or update it
        foreach ($offers as $offer) {
            $view_added = Auth::guard('advertiser-api')->user()
                ->viewedOffers()
                ->where('offer_id', $offer->id)
                ->first();

            if (!$view_added) {
                $offer->views_count += 1;
                $offer->save();
                Auth::guard('advertiser-api')->user()
                    ->viewedOffers()
                    ->create([
                        'offer_id' => $offer->id
                    ]);
            }
        }

        return $this->apiPaginateResponse(CommunityOffersResource::collection($offers));
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function addOffer(Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        if (!Auth::guard('advertiser-api')->user()->is_profile_completed) {
            return $this->apiBadRequestResponse(__('api/auth/auth.profile-uncompleted'));
        }

        //Set data
        $data = $request->only([
            'categoryId',
            'content',
            'salePercentage',
            'advertisementUrl',
            'expiresIn',
            'media',
            'amount',
            'currency',
        ]);

        //Validate course id
        $this->apiValidate($data, [
            'categoryId' => ['nullable', 'exists:categories,id'],
            'content' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (trim($value) === '') {
                        $fail(__('api/advertisers/community/offers/offers.content-empty'));
                    }
                },
            ],
            'currency' => ['required', 'string'],
            'amount'    => ['required', 'numeric','min:0'],
            'salePercentage' => ['required', 'integer', 'min:0', 'max:100'],
            'advertisementUrl' => ['nullable', 'url'],
            'expiresIn' => ['required', 'integer', 'min:1', 'max:30'],
            'media' => ['nullable', 'max:5'],
            'media.*.file' => ['nullable', 'mimes:jpg,jpeg,png,bmp,gif,mp4,mov,ogg,qt,avi,wmv,flv,ts,3gp', 'max:100000'],
            'media.*.startAt' => ['nullable', 'integer'],
            'media.*.endAt' => ['nullable', 'integer', 'gt:media.*.startAt'],
        ]);
        $advertiser = Auth::guard('advertiser-api')->user();

        //get auto approve status in settings
        $auto_approve = Settings::Get('offers.default.auto.approve', false);

        //set expires at if auto approve is enabled
        if ($auto_approve) {
            $data['expires_at'] = Carbon::now()->addDays($data['expiresIn']);
            $data['status'] = 'approved';
        }

        DB::beginTransaction();
        try {
            //lock the advertiser row to serialize concurrent offer-creation attempts
            //and evaluate limits against a fresh, up-to-date state
            $lockedAdvertiser = AdvertiserUser::where('id', $advertiser->id)
                ->lockForUpdate()
                ->first();

            $limits = OfferLimits::evaluate($lockedAdvertiser);

            if ($limits['reason'] === 'active') {
                DB::rollBack();
                return $this->apiBadRequestResponse(__('api/advertisers/community/offers/offers.exceeded-limit', [
                    'count' => $limits['activeLimit'],
                ]));
            }

            if ($limits['reason'] === 'monthly') {
                DB::rollBack();
                return $this->apiBadRequestResponse(__('api/advertisers/community/offers/offers.exceeded-monthly-limit', [
                    'count' => $limits['monthlyLimit'],
                ]));
            }

            //fallback to advertiser's own category when none was provided
            if (isset($data['categoryId']) && $data['categoryId']) {
                $category_id = $data['categoryId'];
            } else {
                $user_categories = AdvertiserCategories::where('advertiser_id', $lockedAdvertiser->id)
                    ->first();

                $category_id = $user_categories->category_id ?? null;
            }

            //create offer
            $offer = $lockedAdvertiser
                ->offers()
                ->create([
                    'category_id' => $category_id,
                    'content' => $data['content'] ? Filter::RemoveHtml($data['content']) : null,
                    'sale_percentage' => $data['salePercentage'] ?? null,
                    'advertisement_url' => !empty($data['advertisementUrl']) ? Filter::RemoveHtml($data['advertisementUrl']) : null,
                    'expires_at' => $data['expires_at'] ?? null,
                    'expires_in' => $data['expiresIn'] ?? null,
                    'amount' => $data['amount'] ?? null,
                    'currency' => $data['currency'] ?? null,
                    'status' => $data['status'] ?? 'pending',
                ]);
            //upload media
            if ($request->hasFile('media.*.file')) {
                foreach ($request->media as $index => $media) {
                    $mime_type = $media['file']->getMimeType();
                    $file = $media['file'];
                    if (strstr($mime_type, "video/")) {
                        $filename = pathinfo($file->hashName(), PATHINFO_FILENAME) . ".mp4";
                        $ffmpeg = FFMpeg::create();
                        $video = $ffmpeg->open($file);
                        $video_stream = $video->getStreams()
                            ->videos()
                            ->first()
                            ->getDimensions();
                        $file_width = $video_stream->getWidth();
                        $file_height = $video_stream->getHeight();
                        if ((isset($media['startAt']) && $media['startAt'] != null) && (isset($media['endAt']) && $media['endAt'] != null)) {
                            $start_at = $media['startAt'];
                            $duration = $media['endAt'] - $start_at;
                            $video->filters()->clip(TimeCode::fromSeconds($start_at), TimeCode::fromSeconds($duration));
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $file = storage_path("app/uploads/$filename");
                        } elseif ($file_height > 480 && $file_height < $file_width) {
                            $video->filters()->resize(new Dimension(640, 480), ResizeFilter::RESIZEMODE_SCALE_WIDTH);
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $video_dimensions = $video->getFFProbe()
                                ->streams(storage_path("app/uploads/$filename"))
                                ->videos()
                                ->first()
                                ->getDimensions();
                            $file_width = $video_dimensions->getWidth();
                            $file_height = $video_dimensions->getHeight();
                            $file = storage_path("app/uploads/$filename");
                        } elseif ($file_width > 480 && $file_height > $file_width) {
                            $video->filters()->resize(new Dimension(480, 640), ResizeFilter::RESIZEMODE_SCALE_HEIGHT);
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $video_dimensions = $video->getFFProbe()
                                ->streams(storage_path("app/uploads/$filename"))
                                ->videos()
                                ->first()
                                ->getDimensions();
                            $file_width = $video_dimensions->getWidth();
                            $file_height = $video_dimensions->getHeight();
                            $file = storage_path("app/uploads/$filename");
                        } else {
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $video_dimensions = $video->getFFProbe()
                                ->streams(storage_path("app/uploads/$filename"))
                                ->videos()
                                ->first()
                                ->getDimensions();
                            $file_width = $video_dimensions->getWidth();
                            $file_height = $video_dimensions->getHeight();
                            $file = storage_path("app/uploads/$filename");
                        }
                    } else if (strstr($mime_type, 'image/')) {
                        $temp_image = Files::uploadTempImage($request, 'uploads/media', "media.{$index}.file");
                        $file = storage_path("app/$temp_image");
                        $file_width = Image::load($file)->getWidth();
                        $file_height = Image::load($file)->getHeight();
                    } else {
                        $file_width = null;
                        $file_height = null;
                    }
                    $offer->addMedia($file)
                        ->withCustomProperties(['width' => $file_width, 'height' => $file_height])
                        ->toMediaCollection('offers');
                }
            }

            if ($auto_approve) {
                Notifications::notifyInterestedUsersForOffer($offer);
            }
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            //bad-request responses (exceeded-limit) throw this by design; let it propagate
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/offers/offers.something-wrong'));
        }
        DB::commit();

        return $this->apiResponse([
            'message' => __('api/advertisers/community/offers/offers.offer-added'),
            'data' => CommunityOffersResource::make($offer),
        ]);
    }

    /**
     * delete offer
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function deleteOffer($id)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }
        //get offer
        $offer = Auth::guard('advertiser-api')->user()
            ->offers()
            ->where('id', $id)
            ->first();

        //return error if the user doesn't have permission to delete this offer
        if (!$offer) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/offers/offers.no-permission'));
        }

        DB::beginTransaction();
        try {

            DB::table('notifications')
                ->whereJsonContains('data->customProperties->offerId', $offer->id)
                ->delete();

            //soft-delete the offer
            $offer->delete();

        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/offers/offers.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/community/offers/offers.offer-deleted'),
        ]);
    }

    /**
     * edit offer
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function editOffer(Request $request, $id)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $offer = Auth::guard('advertiser-api')->user()
            ->offers()
            ->where('id', $id)
            ->first();

        //return error if the user doesn't have permission to delete this offer
        if (!$offer) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/offers/offers.no-permission'));
        }

        //check whether there is files to be deleted or not
        if ($request->has('deleteMedia') && $request->get('deleteMedia') != null) {
            //get media will be deleted
            $media_count = $offer->getMedia('offers')
                ->whereIn('id', $request->get('deleteMedia'))
                ->count();

            //check if the media to be deleted count belongs to this offer and increase max count
            $add_max_count = (sizeof($request->get('deleteMedia')) == $media_count) ? $media_count : 0;
        } else {
            $add_max_count = 0;
        }

        //check whether the edit have new photos or not to set the maximum allowed images.
        if ($request->has('media')) {
            $max_count = $add_max_count + 5 - $offer->getMedia('offers')->count();
        } else {
            $max_count = 0;
        }

        //Set data
        $data = $request->only([
            'categoryId',
            'content',
            'salePercentage',
            'advertisementUrl',
            'expiresIn',
            'media',
            'deleteMedia',
            'amount',
            'currency',
        ]);

        //Validate course id
        $this->apiValidate($data, [
            'categoryId' => ['nullable', 'exists:categories,id'],
            'content' => ['required', 'string'],
            'salePercentage' => ['required'],
            'advertisementUrl' => ['nullable'],
            'currency' => ['required', 'string'],
            'amount'    => ['required', 'numeric','min:0'],
            'expiresIn' => ['required'],
            'media' => ['nullable', "max:$max_count"],
            'media.*.file' => ['nullable', 'mimes:jpg,jpeg,png,bmp,gif,mp4,mov,ogg,qt,avi,wmv,flv,ts,3gp', 'max:100000'],
            'media.*.startAt' => ['nullable', 'integer'],
            'media.*.endAt' => ['nullable', 'integer', 'gt:media.*.startAt'],
            'deleteMedia' => ['nullable', 'array'],
            'deleteMedia.*' => ['nullable', 'exists:media,id'],
        ]);

        DB::beginTransaction();
        try {
            if ($offer->status === 'approved' && $offer->expires_in !== $data['expiresIn']) {
                $data['expires_at'] = Carbon::now()->addDays($data['expiresIn']);
            }
            //delete old media
            if ($request->has('deleteMedia') && $request->get('deleteMedia') != null) {
                foreach ($request->get('deleteMedia') as $media) {
                    $offer->getMedia('offers')
                        ->where('id', $media)
                        ->first()
                        ->delete();
                }
            }
            //upload media
            if ($request->hasFile('media.*.file')) {
                foreach ($request->media as $index => $media) {
                    $mime_type = $media['file']->getMimeType();
                    $file = $media['file'];
                    if (strstr($mime_type, "video/")) {
                        $filename = pathinfo($file->hashName(), PATHINFO_FILENAME) . ".mp4";
                        $ffmpeg = FFMpeg::create();
                        $video = $ffmpeg->open($file);
                        $video_stream = $video->getStreams()
                            ->videos()
                            ->first()
                            ->getDimensions();
                        $file_width = $video_stream->getWidth();
                        $file_height = $video_stream->getHeight();
                        if ((isset($media['startAt']) && $media['startAt'] != null) && (isset($media['endAt']) && $media['endAt'] != null)) {
                            $start_at = $media['startAt'];
                            $duration = $media['endAt'] - $start_at;
                            $video->filters()->clip(TimeCode::fromSeconds($start_at), TimeCode::fromSeconds($duration));
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $file = storage_path("app/uploads/$filename");
                        } elseif ($file_height > 480 && $file_height < $file_width) {
                            $video->filters()->resize(new Dimension(640, 480), ResizeFilter::RESIZEMODE_SCALE_WIDTH);
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $video_dimensions = $video->getFFProbe()
                                ->streams(storage_path("app/uploads/$filename"))
                                ->videos()
                                ->first()
                                ->getDimensions();
                            $file_width = $video_dimensions->getWidth();
                            $file_height = $video_dimensions->getHeight();
                            $file = storage_path("app/uploads/$filename");
                        } elseif ($file_width > 480 && $file_height > $file_width) {
                            $video->filters()->resize(new Dimension(480, 640), ResizeFilter::RESIZEMODE_SCALE_HEIGHT);
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $video_dimensions = $video->getFFProbe()
                                ->streams(storage_path("app/uploads/$filename"))
                                ->videos()
                                ->first()
                                ->getDimensions();
                            $file_width = $video_dimensions->getWidth();
                            $file_height = $video_dimensions->getHeight();
                            $file = storage_path("app/uploads/$filename");
                        } else {
                            $video->save(new X264(), storage_path("app/uploads/$filename"));
                            $video_dimensions = $video->getFFProbe()
                                ->streams(storage_path("app/uploads/$filename"))
                                ->videos()
                                ->first()
                                ->getDimensions();
                            $file_width = $video_dimensions->getWidth();
                            $file_height = $video_dimensions->getHeight();
                            $file = storage_path("app/uploads/$filename");
                        }
                    } else if (strstr($mime_type, 'image/')) {
                        $temp_image = Files::uploadTempImage($request, 'uploads/media', "media.{$index}.file");
                        $file = storage_path("app/$temp_image");
                        $file_width = Image::load($file)->getWidth();
                        $file_height = Image::load($file)->getHeight();
                    } else {
                        $file_width = null;
                        $file_height = null;
                    }
                    $offer->addMedia($file)
                        ->withCustomProperties(['width' => $file_width, 'height' => $file_height])
                        ->toMediaCollection('offers');
                }
            }

            //update the offer
            $offer->update([
                'category_id' => $data['categoryId'] ?? null,
                'content' => $data['content'] ? Filter::RemoveHtml($data['content']) : null,
                'sale_percentage' => $data['salePercentage'] ?? null,
                'advertisement_url' => isset($data['advertisementUrl']) ? Filter::RemoveHtml($data['advertisementUrl']) : null,
                'expires_at' => $data['expires_at'] ?? null,
                'expires_in' => $data['expiresIn'] ?? null,
                'amount' => $data['amount'] ?? null,
                'currency' => $data['currency'] ?? null,
                'status'        =>  'pending'
            ]);

        } catch (Exception $e) {
            //roll back
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/offers/offers.something-wrong'));
        }
        //commit
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/community/offers/offers.offer-edited'),
            'data' => CommunityOffersResource::make($offer),
        ]);
    }

    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function toggleLikeOffer($id, Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }
        //get offer by id
        $offer = Offer::where('id', $id)
            ->first();

        //return error if offer wasn't found
        if (!$offer) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/offers/offers.wrong-id'));
        }

        if ($offer->advertiser->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        DB::beginTransaction();
        try {
            $like = Auth::guard('advertiser-api')->user()
                ->offersLikes()
                ->where('offer_id', $id)
                ->first();

            if ($request->has('isLiked')) {
                if ($request->get('isLiked')) {
                    if (!$like) {
                        Auth::guard('advertiser-api')->user()
                            ->offersLikes()
                            ->create([
                                'offer_id' => $id
                            ]);
                        $offer->likes_count += 1;
                    }
                    $isLiked = true;
                    $type = __('api/advertisers/community/offers/offers.liked');
                } else {
                    if ($like) {
                        $like->delete();
                        $offer->likes_count -= 1;
                    }
                    $isLiked = false;
                    $type = __('api/advertisers/community/offers/offers.disliked');
                }
            } else {
                if ($like) {
                    $like->delete();
                    $offer->likes_count -= 1;
                    $isLiked = false;
                    $type = __('api/advertisers/community/offers/offers.disliked');
                } else {
                    Auth::guard('advertiser-api')->user()
                        ->offersLikes()
                        ->create([
                            'offer_id' => $id
                        ]);
                    $isLiked = true;
                    $offer->likes_count += 1;
                    $type = __('api/advertisers/community/offers/offers.liked');
                }

            }
            $offer->save();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/offers/offers.something-wrong'));
        }
        DB::commit();

        if ($isLiked && $offer->advertiser_id != Auth::guard('customer-api')->id()) {
            $customProperties = [
                'offerId' => $offer->id,
                'userId' => Auth::guard('advertiser-api')->id(),
                'userType' => 'advertiser',
            ];
            $advertiser = $offer->advertiser;

            $advertiser->notifications()
                ->whereJsonContains('data->customProperties->offerId', $offer->id)
                ->whereJsonContains('data->action', 'like')
                ->delete();

            Notifications::sendForCommunity($offer->advertiser, 'offers', 'offers.like', 'like', $customProperties);
        }
        return $this->apiResponse([
            'message' => __('api/advertisers/community/offers/offers.like-toggle', ['toggle' => $type]),
            'data' => [
                'isLiked' => $isLiked,
            ]
        ]);
    }

    /**
     * @param $id
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function reportOffer($id, Request $request)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $user = Auth::guard('advertiser-api')->user();
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
        //check if user already reported
        $report = $offer->reports()
            ->where('user_type', AdvertiserUser::class)
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
            return $this->apiExceptionResponse(__('api/advertisers/community/offers/offers.something-wrong'));
        }
        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/community/offers/offers.reports.report-added'),
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
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        $user = Auth::guard('advertiser-api')->user();

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
            return $this->apiBadRequestResponse(__('api/advertisers/community/offers/offers.reports.no-report'));
        }

        try {
            //create report
            $report->update([
                'reason' => $data['reason'] ? Filter::RemoveHtml($data['reason']) : null,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/community/offers/offers.something-wrong'));
        }
        DB::commit();

        return $this->apiResponse([
            'message' => __('api/advertisers/community/offers/offers.reports.report-edited'),
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

        $user = Auth::guard('advertiser-api')->user();

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
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
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
        $already_rated = Auth::guard('advertiser-api')->user()
            ->offersRated()
            ->where('offer_id', $data['offerId'])
            ->exists();

        //return error if already rated
        if ($already_rated) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/offers/offers.rating.already-rated'));
        }

        //get user by id
        $offer = Offer::withTrashed()
            ->where('id', $data['offerId'])
            ->first();

        if (!$offer) {
            return $this->apiBadRequestResponse(__('api/advertisers/community/offers/offers.wrong-id'));
        }

        if ($offer->advertiser->status === 'banned') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-banned'));
        }

        DB::beginTransaction();
        try {
            //check whether rate should be auto approved or not
            $auto_approve = Settings::Get('offers.rate.auto.approve', true);

            //add the rate
            Auth::guard('advertiser-api')->user()
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
            return $this->apiExceptionResponse(__('api/advertisers/community/offers/offers.rating.something-wrong'));
        }
        DB::commit();

        if ($auto_approve) {
            $customProperties = [
                'offerId' => $id,
                'userId' => Auth::guard('advertiser-api')->id(),
                'userType' => 'advertiser',
            ];

            Notifications::sendForCommunity($offer->advertiser, 'offers.rates', 'offers.rates.offer_rate', 'add', $customProperties);
        }
        return $this->apiResponse([
            'message' => __('api/advertisers/community/offers/offers.rating.rated-successfully'),
            'data' => [
                'rate' => $offer->rate,
                'isRateApproved' => $auto_approve,
            ]
        ]);
    }

}
