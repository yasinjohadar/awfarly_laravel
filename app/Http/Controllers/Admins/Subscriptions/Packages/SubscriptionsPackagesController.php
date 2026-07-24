<?php

namespace App\Http\Controllers\Admins\Subscriptions\Packages;

use App\Helpers\Admins\AdminLogs;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class SubscriptionsPackagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function index()
    {
        if (!Auth::guard('admin')->user()->can('packages.inquiry')) {
            return abort(404);
        }

        return view('admin.pages.subscriptions.packages.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function show($id)
    {
        if (!Auth::guard('admin')->user()->can('packages.inquiry')) {
            return abort(404);
        }

        //Log Action
        AdminLogs::log('inquiry', 'packages');


        return view('admin.pages.subscriptions.packages.index', ['filter_id' => $id]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|void
     */
    public function create()
    {
        if (!Auth::guard('admin')->user()->can('packages.add')) {
            return abort(404);
        }

        return view('admin.pages.subscriptions.packages.create');
    }
}
