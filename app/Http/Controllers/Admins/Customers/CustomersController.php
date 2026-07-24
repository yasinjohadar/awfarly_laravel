<?php

namespace App\Http\Controllers\Admins\Customers;

use App\Helpers\Admins\AdminLogs;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        if(!Auth::guard('admin')->user()->can('customers.inquiry')){
            return abort(404);
        }

        //Log Action
        AdminLogs::log('inquiry', 'customers');

        return view('admin.pages.customers.index');
    }
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function show($id)
    {
        if (!Auth::guard('admin')->user()->can('customers.inquiry')) {
            return abort(404);
        }

        //Log Action
        AdminLogs::log('inquiry', 'customers');

        //set active number filters
        $activeNumberFilters = [
            '1' => [
                'start' => $id,
                'end' => $id,
            ]
        ];

        return view('admin.pages.customers.index', ['activeNumberFilters' => $activeNumberFilters ?? []]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        if(!Auth::guard('admin')->user()->can('customers.add')){
            return abort(404);
        }
        return view('admin.pages.customers.create');
    }

    /**
     * @return Application|Factory|View|void
     */
    public function reportedCustomers()
    {
        //check if user is allowed to do this action or not
        if (!Auth::guard('admin')->user()->can('customers.inquiry')) {
            return abort(404);
        }
        return view('admin.pages.customers.reports.index');
    }
}
