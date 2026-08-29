<?php

namespace App\Http\Controllers\Admins\System\Firebase;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class SystemFirebaseController extends Controller
{
    /**
     * Display the Firebase credentials/test-notification page.
     *
     * @return Application|Factory|View|void
     */
    public function index()
    {
        if (!Auth::guard('admin')->user()->can('settings.edit')) {
            return abort(404);
        }

        return view('admin.pages.system.firebase.index');
    }
}
