<?php

namespace App\Http\Controllers\API\Guests\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Categories\CategoriesResource;
use App\Models\Categories\Category;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoriesController extends Controller
{
    /**
     * @return Application|ResponseFactory|Response
     */
    public function getCategories(Request $request)
    {
        $categories = Category::whereNull('parent_category_id')
            /*->whereHas('childCategories')*/
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
            return $this->apiBadRequestResponse(__('api/guests/categories/categories.wrong-id'));
        }

        return $this->apiResponse(CategoriesResource::make($category));
    }
}
