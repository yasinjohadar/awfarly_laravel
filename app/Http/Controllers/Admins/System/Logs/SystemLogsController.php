<?php

namespace App\Http\Controllers\Admins\System\Logs;

use App\Helpers\Admins\AdminLogs;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class SystemLogsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function index()
    {
        //Handle permissions
        if (!Auth::guard("admin")->user()->can("logs.inquiry")) {
            return abort(404);
        }

        //Log Action
        AdminLogs::log('inquiry', 'logs');

        //return with view
        return view('admin.pages.system.logs.index');
    }
}
