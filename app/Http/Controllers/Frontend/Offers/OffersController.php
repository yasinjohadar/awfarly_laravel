<?php

namespace App\Http\Controllers\Frontend\Offers;

use App\Http\Controllers\Controller;
use App\Models\Offers\Offer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OffersController extends Controller
{
    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function index($id)
    {
        Offer::findOrFail($id);

        return view('frontend.pages.offer.offer', ['offer_id' => $id]);
    }
}
