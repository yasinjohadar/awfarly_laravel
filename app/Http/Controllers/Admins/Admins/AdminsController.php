<?php

namespace App\Http\Controllers\Admins\Admins;

use App\Helpers\Admins\AdminLogs;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class AdminsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function index()
    {
        if (!Auth::guard('admin')->user()->can('admins.inquiry')) {
            return abort(404);
        }
    //Log Action
        AdminLogs::log('inquiry', 'admins');

        return view('admin.pages.admins.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        if (!Auth::guard('admin')->user()->can('admins.add')) {
            return abort(404);
        }
        return view('admin.pages.admins.create');
    }
}
