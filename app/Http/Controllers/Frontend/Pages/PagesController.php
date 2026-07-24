<?php

namespace App\Http\Controllers\Frontend\Pages;

use App\Http\Controllers\Controller;
use App\Models\Pages\Page;
use Illuminate\Support\Facades\App;

class PagesController extends Controller
{
    public function index($id, $slug)
    {
        $page = Page::findOrFail($id);

        //get language column to show
        $title = App::currentLocale() === 'ar' ? 'title_ar' : 'title_en';
        $contents = App::currentLocale() === 'ar' ? 'contents_ar' : 'contents_en';
        $page = [
            'id' => $page->id,
            'title' => $page->{$title},
            'content' => $page->{$contents},
            'slug' => $page->slug,
        ];


        return view('frontend.pages.pages.index', ['page' => $page, 'slug' => $slug]);
    }
}
