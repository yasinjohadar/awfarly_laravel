<?php

namespace App\Http\Controllers\Admins\Community\Offers;

use App\Helpers\Admins\AdminLogs;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class CommunityOffersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function index()
    {
        if (!Auth::guard('admin')->user()->can('offers.inquiry')) {
            //send toastr alert with error
            return abort(404);
        }

        AdminLogs::log('inquiry', 'offers');

        return view('admin.pages.community.offers.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function show($id)
    {
        if (!Auth::guard('admin')->user()->can('offers.inquiry')) {
            return abort(404);
        }

        //Log Action
        AdminLogs::log('inquiry', 'offers');


        return view('admin.pages.community.offers.index', ['filter_id' => $id]);
    }

    /**
     * @return Application|Factory|View|void
     */
    public function reportedOffers()
    {
        //check if user is allowed to do this action or not
        if (!Auth::guard('admin')->user()->can('offers.reported')) {
            return abort(404);
        }
        return view('admin.pages.community.offers.reports.index');
    }

    /**
     * @return Application|Factory|View|void
     */
    public function getRatings()
    {
        if (!Auth::guard('admin')->user()->can('ratings.inquiry')) {
            return abort(404);
        }
        return view('admin.pages.community.offers.ratings.index');
    }
}
