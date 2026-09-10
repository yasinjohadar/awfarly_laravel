<?php

namespace App\Http\Controllers\Admins\System\WhatsApp;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class SystemWhatsAppController extends Controller
{
    /**
     * @return Application|Factory|View|void
     */
    public function settings()
    {
        if (!Auth::guard('admin')->user()->can('whatsapp.inquiry')) {
            return abort(404);
        }

        return view('admin.pages.system.whatsapp.settings');
    }

    /**
     * @return Application|Factory|View|void
     */
    public function instances()
    {
        if (!Auth::guard('admin')->user()->can('whatsapp.inquiry')) {
            return abort(404);
        }

        return view('admin.pages.system.whatsapp.instances');
    }

    /**
     * @return Application|Factory|View|void
     */
    public function send()
    {
        if (!Auth::guard('admin')->user()->can('whatsapp.inquiry')) {
            return abort(404);
        }

        return view('admin.pages.system.whatsapp.send');
    }

    /**
     * @return Application|Factory|View|void
     */
    public function groups()
    {
        if (!Auth::guard('admin')->user()->can('whatsapp.inquiry')) {
            return abort(404);
        }

        return view('admin.pages.system.whatsapp.groups');
    }

    /**
     * @return Application|Factory|View|void
     */
    public function templates()
    {
        if (!Auth::guard('admin')->user()->can('whatsapp.inquiry')) {
            return abort(404);
        }

        return view('admin.pages.system.whatsapp.templates');
    }
}
