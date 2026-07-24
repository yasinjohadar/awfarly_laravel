<?php

namespace App\Http\Controllers\Admins\Advertisers;

use App\Helpers\Admins\AdminLogs;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class AdvertisersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function index()
    {
        if (!Auth::guard('admin')->user()->can('advertisers.inquiry')) {
            return abort(404);
        }

        //Log Action
        AdminLogs::log('inquiry', 'advertisers');

        return view('admin.pages.advertisers.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function show($id)
    {
        if (!Auth::guard('admin')->user()->can('advertisers.inquiry')) {
            return abort(404);
        }

        //Log Action
        AdminLogs::log('inquiry', 'advertisers');

        //set active number filters
        $activeNumberFilters = [
            '1' => [
                'start' => $id,
                'end' => $id,
            ]
        ];

        return view('admin.pages.advertisers.index', ['activeNumberFilters' => $activeNumberFilters ?? []]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|void
     */
    public function create()
    {
        if (!Auth::guard('admin')->user()->can('advertisers.add')) {
            return abort(404);
        }
        return view('admin.pages.advertisers.create');
    }

    /**
     * @return Application|Factory|View|void
     */
    public function getRatings()
    {
        if (!Auth::guard('admin')->user()->can('ratings.inquiry')) {
            return abort(404);
        }
        return view('admin.pages.advertisers.ratings.index');
    }

    /**
     * @return Application|Factory|View|void
     */
    public function reportedAdvertisers()
    {
        //check if user is allowed to do this action or not
        if (!Auth::guard('admin')->user()->can('advertisers.inquiry')) {
            return abort(404);
        }
        return view('admin.pages.advertisers.reports.index');
    }
}
