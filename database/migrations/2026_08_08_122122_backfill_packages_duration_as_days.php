<?php

use App\Models\Subscriptions\Packages\Package;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BackfillPackagesDurationAsDays extends Migration
{
    /**
     * `packages.duration` used to mean "count of the subscription_type's own unit"
     * (e.g. 1 for both monthly and yearly). It is now always a day count, so every
     * existing row needs its duration recomputed from its subscription_type.
     *
     * @return void
     */
    public function up()
    {
        foreach (DB::table('packages')->select('id', 'subscription_type')->get() as $package) {
            DB::table('packages')
                ->where('id', $package->id)
                ->update(['duration' => Package::durationDaysForType($package->subscription_type)]);
        }
    }

    /**
     * Data-only migration; the previous per-unit values aren't recoverable.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
