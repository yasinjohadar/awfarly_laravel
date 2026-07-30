<?php

namespace App\Http\Controllers\API\Customers\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Categories\CategoriesResource;
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
     * @return Application|ResponseFactory|Response
     */
    public function getUserCategories()
    {
        $categories = Auth::guard('customer-api')->user()
            ->categories()
            ->pluck('category_id')
            ->toArray();

        $categories = Category::whereIn('id',$categories)
            ->orderBy('order')
            ->get();
        return $this->apiResponse(CategoriesResource::collection($categories));
    }

    /**
     * @param $id
     * @return Application|ResponseFactory|Response
     */
    public function getCategoryById($id)
    {
        $category = Category::where('id', $id)
            ->first();


        //return error if country wasn't found
        if (!$category) {
            return $this->apiBadRequestResponse(__('api/customers/categories/categories.wrong-id'));
        }

        return $this->apiResponse(CategoriesResource::make($category));
    }

    public function deleteAdvertiserCategories(Request $request)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
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

            //add categories foreach one
            foreach ($data['categories'] as $category) {
                Auth::guard('customer-api')->user()
                    ->categories()
                    ->where('category_id',$category)
                    ->delete();
            }
            //get user categories
            $categories = Auth::guard('customer-api')->user()
                ->categories()
                ->get();

        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/categories/categories.something-wrong'));
        }

        DB::commit();
        return $this->apiResponse([
            'message' => __('api/customers/categories/categories.added'),
            'data' => CustomerCategoriesResource::collection($categories),
        ]);

    }
    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function addAdvertiserCategories(Request $request)
    {
        if (Auth::guard('customer-api')->user()->status === 'inactive') {
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
            Auth::guard('customer-api')->user()
                ->categories()
                ->delete();

            //add categories foreach one
            foreach (($data['categories'] ?? []) as $category) {
                Auth::guard('customer-api')->user()
                    ->categories()
                    ->updateOrCreate([
                        'category_id' => $category
                    ]);
            }
            //get user categories
            $categories = Auth::guard('customer-api')->user()
                ->categories()
                ->get();

        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/customers/categories/categories.something-wrong'));
        }

        DB::commit();
        return $this->apiResponse([
            'message' => __('api/customers/categories/categories.added'),
            'data' => CustomerCategoriesResource::collection($categories),
        ]);
    }
}
