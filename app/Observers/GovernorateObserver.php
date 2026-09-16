<?php

namespace App\Observers;

use App\Helpers\Geography\LocationTree;
use App\Models\Countries\Governorates\Governorate;

class GovernorateObserver
{
    /**
     * Not redundant with CityObserver: cities.governorate_id cascades on delete,
     * so removing a governorate deletes every city under it inside the database,
     * without firing a single City model event. Without this the cached map
     * would keep handing out cities that no longer exist.
     *
     * @param Governorate $governorate
     * @return void
     */
    public function deleted(Governorate $governorate): void
    {
        LocationTree::flush();
    }

    /**
     * @param Governorate $governorate
     * @return void
     */
    public function saved(Governorate $governorate): void
    {
        LocationTree::flush();
    }
}
