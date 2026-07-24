<?php

namespace App\Http\Controllers\API\Advertisers\Modals;

use App\Models\Modals\Modal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Guests\Modal\ModalCollection;

class ModalController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $modal = Modal::where(function ($q) {
            $q->where('start_at', '<=', now())
                ->where('end_at', '>=', now());
        })->where(function ($q) {
            $q->where('recipients_type', 'all_advertisers')
                ->orWhere('recipients_type', 'all_users');
        })
            ->latest()
            ->first();

        return $this->apiResponse(ModalCollection::make($modal));
    }
}
