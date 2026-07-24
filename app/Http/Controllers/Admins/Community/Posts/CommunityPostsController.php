<?php

namespace App\Http\Controllers\Admins\Community\Posts;

use App\Helpers\Admins\AdminLogs;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class CommunityPostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function index()
    {
        //check if user is allowed to do this action or not
        if (!Auth::guard('admin')->user()->can('comments.inquiry')) {
            return abort(404);
        }

        AdminLogs::log('inquiry', 'posts');

        return view('admin.pages.community.posts.index');
    }


    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function show($id)
    {
        if (!Auth::guard('admin')->user()->can('posts.inquiry')) {
            return abort(404);
        }

        //Log Action
        AdminLogs::log('inquiry', 'posts');


        return view('admin.pages.community.posts.index', ['filter_id' => $id]);
    }

    /**
     * @return Application|Factory|View|void
     */
    public function reportedPosts()
    {
        //check if user is allowed to do this action or not
        if (!Auth::guard('admin')->user()->can('posts.reported')) {
            return abort(404);
        }
        return view('admin.pages.community.posts.reports.index');
    }
}
