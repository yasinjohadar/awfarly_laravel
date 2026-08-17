<?php

namespace App\Http\Controllers\API\Advertisers\Categories;

use App\Helpers\Categories\CategoriesFilter;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Categories\CategoriesResource;
use App\Http\Resources\Users\Advertisers\Categories\AdvertiserCategoriesResource;
use App\Http\Resources\Users\Customers\Categories\CustomerCategoriesResource;
use App\Models\Categories\Category;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{

    /**
     * @return Application|ResponseFactory|Response
     */
    public function getCategories(Request $request)
    {
        $categories = Category::whereNull('parent_category_id')
            /*->whereHas('childCategories')*/
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return $this->apiResponse(CategoriesResource::collection($categories));
    }

    /**
     * Remove categories from the advertiser's INTERESTS (what their feed is
     * filtered by). Their own business categories are untouched — see
     * deleteOwnCategories().
     *
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function deleteAdvertiserCategories(Request $request)
    {
        return $this->detachFrom($request, 'interests');
    }

    /**
     * @return Application|ResponseFactory|Response
     */
    public function getUserCategories()
    {
        return $this->listFrom('interests');
    }

    /**
     * The advertiser's OWN business categories: what they publish under.
     *
     * @return Application|ResponseFactory|Response
     */
    public function getOwnCategories()
    {
        return $this->listFrom('categories');
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function setOwnCategories(Request $request)
    {
        return $this->syncInto($request, 'categories');
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function deleteOwnCategories(Request $request)
    {
        return $this->detachFrom($request, 'categories');
    }

    /**
     * @param string $relation
     * @return Application|ResponseFactory|Response
     */
    private function listFrom(string $relation)
    {
        $ids = Auth::guard('advertiser-api')->user()
            ->{$relation}()
            ->pluck('category_id')
            ->toArray();

        $categories = Category::whereIn('id', $ids)
            ->orderBy('order')
            ->get();

        return $this->apiResponse([
            'data' => CategoriesResource::collection($categories)
        ]);
    }

    /**
     * @param Request $request
     * @param string $relation
     * @return Application|ResponseFactory|Response
     */
    private function detachFrom(Request $request, string $relation)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //get data
        $data = $request->all();

        //validate categories
        $this->apiValidate($data, [
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
        ]);

        DB::beginTransaction();
        try {
            Auth::guard('advertiser-api')->user()
                ->{$relation}()
                ->whereIn('category_id', (array) ($data['categories'] ?? []))
                ->delete();

            $categories = Auth::guard('advertiser-api')->user()
                ->{$relation}()
                ->get();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/categories/categories.something-wrong'));
        }

        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/categories/categories.deleted'),
            'data' => AdvertiserCategoriesResource::collection($categories),
        ]);
    }

    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function getCategoryById($id)
    {
        $category = Category::where('id', $id)
            ->where('is_active', true)
            ->first();


        //return error if country wasn't found
        if (!$category) {
            return $this->apiBadRequestResponse(__('api/advertisers/categories/categories.wrong-id'));
        }

        return $this->apiResponse(CategoriesResource::make($category));
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function addAdvertiserCategories(Request $request)
    {
        return $this->syncInto($request, 'interests');
    }

    /**
     * @param Request $request
     * @param string $relation
     * @return Application|ResponseFactory|Response
     */
    private function syncInto(Request $request, string $relation)
    {
        if (Auth::guard('advertiser-api')->user()->status === 'inactive') {
            return $this->apiBadRequestResponse(__('api/auth/auth.account-closed'));
        }

        //get data
        $data = $request->all();

        //validate categories
        $this->apiValidate($data, [
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
        ]);

        $requested = (array) ($data['categories'] ?? []);
        $max_categories = Settings::Get('max.user.categories.interests', 200);

        //check the INCOMING size, not the stored one: the old check compared the
        //current count and so locked a user at the limit out of ever changing
        //their selection, even to a smaller one
        if (count($requested) > $max_categories) {
            return $this->apiExceptionResponse(__('api/advertisers/categories/categories.exceeded-limit'));
        }

        DB::beginTransaction();
        try {
            CategoriesFilter::syncCategories(
                Auth::guard('advertiser-api')->user()->{$relation}(),
                $requested
            );

            $categories = Auth::guard('advertiser-api')->user()
                ->{$relation}()
                ->get();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/advertisers/categories/categories.something-wrong'));
        }

        DB::commit();
        return $this->apiResponse([
            'message' => __('api/advertisers/categories/categories.added'),
            'data' => AdvertiserCategoriesResource::collection($categories),
        ]);
    }
}
