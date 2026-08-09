<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangePackagesCurrencyToString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE packages MODIFY currency VARCHAR(10) NOT NULL DEFAULT 'SAR'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE packages MODIFY currency ENUM('SAR','USD') NOT NULL DEFAULT 'SAR'");
    }
}
