<?php

namespace App\Http\Controllers\Admins\Community\Offers\Comments;

use App\Helpers\Admins\AdminLogs;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class CommunityOffersCommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function index()
    {
        if (!Auth::guard('admin')->user()->can('comments.inquiry')) {
            //send toastr alert with error
            return abort(404);
        }

        AdminLogs::log('inquiry', 'comments');

        return view('admin.pages.community.offers.comments.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function show($id)
    {
        if (!Auth::guard('admin')->user()->can('comments.inquiry')) {
            //send toastr alert with error
            return abort(404);
        }

        AdminLogs::log('inquiry', 'comments');

        return view('admin.pages.community.offers.comments.index', ['filter_id' => $id]);
    }

    /**
     * @return Application|Factory|View|void
     */
    public function reportedComments()
    {
        //check if user is allowed to do this action or not
        if (!Auth::guard('admin')->user()->can('comments.reported')) {
            return abort(404);
        }
        return view('admin.pages.community.offers.comments.reports.index');
    }
}
