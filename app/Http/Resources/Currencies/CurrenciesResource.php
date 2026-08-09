<?php

namespace App\Http\Resources\Currencies;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class CurrenciesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $language_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        return [
            'code' => $this->code,
            'name' => $this->{$language_column},
            'symbol' => $this->symbol,
            'exchangeRate' => (float) $this->exchange_rate,
            'isBase' => (bool) $this->is_base,
        ];
    }
}
