<?php

namespace App\Http\Controllers\Admins\Subscriptions\Requests;

use App\Http\Controllers\Controller;
use App\Models\Subscriptions\Packages\PackageSubscriptionRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /**
     * Stream a subscription request's uploaded payment receipt (image or PDF)
     * for admin review. Not publicly accessible.
     *
     * @return StreamedResponse
     */
    public function receipt(PackageSubscriptionRequest $subscriptionRequest)
    {
        if (!Auth::guard('admin')->user()->can('payments.inquiry')) {
            return abort(404);
        }

        if (!$subscriptionRequest->receipt || !Storage::disk('local')->exists($subscriptionRequest->receipt)) {
            return abort(404);
        }

        return Storage::disk('local')->response($subscriptionRequest->receipt);
    }
}
