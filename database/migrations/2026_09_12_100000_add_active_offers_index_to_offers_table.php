<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveOffersIndexToOffersTable extends Migration
{
    /**
     * Offer::scopeWithinAdvertiserActiveLimit() runs a correlated subquery that
     * counts an advertiser's newer active offers, filtering on advertiser_id +
     * status + expires_at and ordering by created_at. The table only had the
     * FK index on advertiser_id, which left that subquery scanning every offer
     * the advertiser ever published, on every feed row.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->index(
                ['advertiser_id', 'status', 'expires_at', 'created_at'],
                'offers_active_per_advertiser_index'
            );
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropIndex('offers_active_per_advertiser_index');
        });
    }
}
