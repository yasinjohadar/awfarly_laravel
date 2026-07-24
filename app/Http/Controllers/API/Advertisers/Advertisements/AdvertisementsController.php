<?php

namespace App\Http\Controllers\API\Advertisers\Advertisements;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Advertisers\Advertisements\AdvertisementsResource;
use App\Models\Advertisements\Advertisement;
use App\Models\Countries\Cities\City;
use App\Models\Users\Advertisers\AdvertiserUser;
use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdvertisementsController extends Controller
{
    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function getAdvertisements(Request $request)
    {
        //get limit
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('advertisements.pagination.limit', 10);

        $data = $request->all();

        $this->apiValidate($data, [
            'type' => ['nullable', 'in:any,mobile,website'],
            'categoryId' => ['nullable', 'exists:categories,id'],
            'countryCode' => ['nullable', 'exists:countries,code'],
            'cityId' => ['nullable', 'exists:cities,id'],
        ]);

        //get advertisements
        $advertisements = Advertisement::where(function ($query) {
            return $query->where('ends_at', '>', now())
                ->orWhereNull('ends_at');
        })
            ->where(function ($query) {
                return $query->where('users', "advertisers")
                    ->orWhere('users', 'any');
            })
            ->where('is_active', true)
            ->whereHas('post');


        //set type if existed
        if (isset($data['type']) && $data['type']) {
            $advertisements = $advertisements->where(function ($query) use ($data) {
                return $query->where('type', $data['type'])
                    ->orWhere('type', 'any');
            });
        }

        //set category id if existed
        if ($request->has('categoryId')) {
            if (!is_null($data['categoryId'])) {
                $advertisements = $advertisements->whereJsonContains('categories', $request->get('categoryId'));
            } else {
                $advertisements = $advertisements->where(function ($query) {
                    return $query->whereNull('categories')
                        ->OrWhere('categories', '');
                });
            }
        }

        if (isset($data['countryCode']) && $data['countryCode'] && (!isset($data['cityId']) || !$data['cityId'])) {
            $cities = City::where('country_code', $data['countryCode'])
                ->get()
                ->map(function ($city) {
                    return "{$city->id}";
                })
                ->toArray();

            $advertisements = $advertisements->where(function ($query) use ($cities) {
                foreach ($cities as $city) {
                    $query->whereJsonContains('cities', $city);
                }
            });
        }

        //set city id if existed
        if (isset($data['cityId']) && $data['cityId']) {
            $advertisements = $advertisements->where(function ($query) use ($data) {
                $query->whereJsonContains('cities', $data['cityId']);
            });
        }

        if ((!isset($data['countryCode']) || !$data['countryCode']) && (!isset($data['cityId']) || !$data['cityId'])) {
            $advertisements = $advertisements->where(function ($query) {
                $query->whereNull('cities');
            });
        }

        //get whether it's random order or not
        if (isset($data['isRandom']) && $data['isRandom']) {
            $advertisements = $advertisements
                ->inRandomOrder();
        }

        //paginate
        $advertisements = $advertisements->orderBy('created_at', 'desc')
            ->paginate($limit);

        //add post view or update it
        foreach ($advertisements as $advertisement) {
            $view_added = Auth::guard('advertiser-api')->user()
                ->viewedPosts()
                ->where('post_id', $advertisement->post->id)
                ->first();

            if (!$view_added) {
                $advertisement->post->views_count += 1;
                $advertisement->post->save();
                Auth::guard('advertiser-api')->user()
                    ->viewedPosts()
                    ->create([
                        'post_id' => $advertisement->post->id
                    ]);
            }
        }
        return $this->apiPaginateResponse(AdvertisementsResource::collection($advertisements));
    }
}
