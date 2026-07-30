<?php

namespace App\Http\Controllers\Admins\Subscriptions\Requests;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class SubscriptionRequestsController extends Controller
{
    /**
     * @return Application|Factory|View|void
     */
    public function index()
    {
        if (!Auth::guard('admin')->user()->can('payments.inquiry')) {
            return abort(404);
        }

        return view('admin.pages.subscriptions.requests.index');
    }
}
