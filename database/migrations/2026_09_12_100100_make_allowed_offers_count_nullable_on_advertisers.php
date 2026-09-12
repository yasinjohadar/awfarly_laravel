<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeAllowedOffersCountNullableOnAdvertisers extends Migration
{
    /**
     * OfferLimits::activeLimit() is written to treat a null allowed_offers_count
     * as "fall through to the global setting", but the column was created NOT NULL
     * with a default of 10, so that branch could never run and the admin form's
     * "leave blank to use the default" behaviour would fail under strict mode.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advertisers_users', function (Blueprint $table) {
            $table->integer('allowed_offers_count')->nullable()->default(null)->change();
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('advertisers_users', function (Blueprint $table) {
            $table->integer('allowed_offers_count')->nullable(false)->default(10)->change();
        });
    }
}
