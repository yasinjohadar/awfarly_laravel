<?php

namespace App\Http\Controllers\Admins\Community\Proposals;

use App\Helpers\Admins\AdminLogs;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class CommunityProposalsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function index()
    {
        if (!Auth::guard('admin')->user()->can('proposals.inquiry')) {
            //send toastr alert with error
            return abort(404);
        }

        AdminLogs::log('inquiry', 'proposals');

        return view('admin.pages.community.proposals.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function show($id)
    {
        if (!Auth::guard('admin')->user()->can('proposals.inquiry')) {
            return abort(404);
        }

        //Log Action
        AdminLogs::log('inquiry', 'proposals');


        return view('admin.pages.community.proposals.index', ['filter_id' => $id]);
    }

    /**
     * @return Application|Factory|View|void
     */
    public function reportedProposals()
    {
        //check if user is allowed to do this action or not
        if (!Auth::guard('admin')->user()->can('proposals.reported')) {
            return abort(404);
        }
        return view('admin.pages.community.proposals.reports.index');
    }
}
