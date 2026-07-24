<?php

namespace App\Http\Controllers\Frontend\ContactUs;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    /**
     * @param string $type
     * @return Application|Factory|View|void
     */
    public function index(string $type = 'Enquiry')
    {
        if ($type && !in_array($type, ['Enquiry', 'Complaint', 'Suggestion', 'Payments', 'Technical support', 'In-app advertising', 'Report a problem'])) {
            return abort(404);
        }
        return view('frontend.pages.contact-us.index', ['type' => $type]);
    }
}
