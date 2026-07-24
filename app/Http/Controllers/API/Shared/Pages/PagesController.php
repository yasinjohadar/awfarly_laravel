<?php

namespace App\Http\Controllers\API\Shared\Pages;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shared\Pages\PagesResource;
use App\Models\Pages\Page;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

class PagesController extends Controller
{
    /**
     * @param $slug
     * @return Application|ResponseFactory|Response
     */
    public function getPageBySlug($slug)
    {
        $page = Page::where('slug', $slug)
            ->orWhere('id', $slug)
            ->first();

        if (!$page) {
            return $this->apiBadRequestResponse(__('api/shared/pages/pages.not-found'));
        }
        return $this->apiResponse(PagesResource::make($page));
    }

    /**
     * @return Application|ResponseFactory|Response
     */
    public function getAllPages()
    {
        $page = Page::where('is_active', true)
            ->get();

        return $this->apiResponse(PagesResource::collection($page));
    }
}
