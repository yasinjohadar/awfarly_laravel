<?php

namespace App\Http\Controllers\API\Advertisers\Advertisements;

use App\Helpers\Geography\Geography;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Advertisers\Advertisements\AdvertisementsResource;
use App\Models\Advertisements\Advertisement;
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
        $limit = ($request->has('limit') && $request->get('limit') > 0) ? $request->get('limit') : Settings::Get('advertisements.pagination.limit', 10);

        $data = $request->only(array_merge(['type', 'categoryId', 'countryCode', 'isRandom'], Geography::locationFilterFields()));

        $this->apiValidate($data, array_merge([
            'type' => ['nullable', 'in:any,mobile,website'],
            'categoryId' => ['nullable', 'exists:categories,id'],
        ], Geography::advertisementFilterRules()));

        $advertisements = Advertisement::where(function ($query) {
            return $query->where('ends_at', '>', now())
                ->orWhereNull('ends_at');
        })
            ->where(function ($query) {
                return $query->where('users', 'advertisers')
                    ->orWhere('users', 'any');
            })
            ->where('is_active', true)
            ->whereHas('post');

        if (isset($data['type']) && $data['type']) {
            $advertisements = $advertisements->where(function ($query) use ($data) {
                return $query->where('type', $data['type'])
                    ->orWhere('type', 'any');
            });
        }

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

        if (Geography::hasExplicitLocationFilter($data) || !empty($data['countryCode'])) {
            $advertisements = Geography::applyAdvertisementLocationFilter($advertisements, $data);
        } else {
            $prefs = Geography::preferredLocationIds(Auth::guard('advertiser-api')->user());
            if (!empty($prefs['governorates']) || !empty($prefs['cities'])) {
                $advertisements = Geography::applyPreferredAdvertisementLocationFilter(
                    $advertisements,
                    Auth::guard('advertiser-api')->user()
                );
            } else {
                $advertisements = Geography::applyAdvertisementLocationFilter($advertisements, $data);
            }
        }

        if (isset($data['isRandom']) && $data['isRandom']) {
            $advertisements = $advertisements->inRandomOrder();
        }

        $advertisements = $advertisements->orderBy('created_at', 'desc')
            ->paginate($limit);

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
                        'post_id' => $advertisement->post->id,
                    ]);
            }
        }

        return $this->apiPaginateResponse(AdvertisementsResource::collection($advertisements));
    }
}
