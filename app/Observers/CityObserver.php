<?php

namespace App\Observers;

use App\Helpers\Geography\LocationTree;
use App\Models\Countries\Cities\City;

class CityObserver
{
    /**
     * Drop the cached governorate/city map whenever a city is created, moved to
     * another governorate, or removed. Location-interest filtering answers from
     * that one cache entry (LocationTree), so a write has to invalidate it or
     * feeds keep matching against the old shape until the TTL expires.
     *
     * @param City $city
     * @return void
     */
    public function saved(City $city): void
    {
        LocationTree::flush();
    }

    /**
     * @param City $city
     * @return void
     */
    public function deleted(City $city): void
    {
        LocationTree::flush();
    }
}
