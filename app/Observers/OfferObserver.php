<?php

namespace App\Observers;

use App\Helpers\Notifications;
use App\Models\Offers\Offer;

class OfferObserver
{
    /**
     * Notify the offer's advertiser whenever an admin approves or declines
     * (re-)review of their offer. See PostObserver::updated() for the exact
     * same reasoning.
     *
     * @param Offer $offer
     * @return void
     */
    public function updated(Offer $offer): void
    {
        if ($offer->wasChanged('status') && in_array($offer->status, ['approved', 'unapproved'])) {
            Notifications::notifyOwnerOfferStatusChanged($offer);
        }
    }
}
