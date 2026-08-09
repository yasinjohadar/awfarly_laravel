<?php

namespace App\Http\Controllers\API\Guests\Currencies;

use App\Http\Controllers\Controller;
use App\Http\Resources\Currencies\CurrenciesResource;
use App\Models\Currencies\Currency;

class CurrenciesController extends Controller
{
    public function getCurrencies()
    {
        $currencies = Currency::where('is_active', true)
            ->where('is_visible', true)
            ->orderBy('order')
            ->get();

        return $this->apiResponse(CurrenciesResource::collection($currencies));
    }
}
