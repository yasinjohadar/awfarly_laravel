<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RepairUsersGovernorateFromCity extends Migration
{
    /**
     * Profile updates from the app used to send `cityId` without `governorateId`,
     * so `city_id` moved while `governorate_id` stayed behind. Any such user then
     * failed Geography::validateCityBelongsToGovernorate and could no longer add
     * a post at all.
     *
     * The city is the value the user actually chose most recently, so the city's
     * own governorate wins. Every change is journaled so down() can undo it.
     */
    private const BACKUP_TABLE = 'user_governorate_repair_backups';

    private const USER_TABLES = ['advertisers_users', 'customers_users'];

    /**
     * @return void
     */
    public function up()
    {
        Schema::create(self::BACKUP_TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('user_table', 64);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('old_governorate_id')->nullable();
            $table->unsignedBigInteger('new_governorate_id');
            $table->unsignedBigInteger('city_id');
            $table->timestamp('repaired_at')->useCurrent();

            $table->unique(['user_table', 'user_id']);
        });

        foreach (self::USER_TABLES as $userTable) {
            if (!Schema::hasTable($userTable)) {
                continue;
            }

            //rows whose governorate_id is missing, or does not own their city_id.
            //soft-deleted rows are included on purpose, they can be restored
            $broken = DB::table($userTable)
                ->join('cities', 'cities.id', '=', "{$userTable}.city_id")
                ->where(function ($query) use ($userTable) {
                    $query->whereNull("{$userTable}.governorate_id")
                        ->orWhereColumn(
                            "{$userTable}.governorate_id",
                            '!=',
                            'cities.governorate_id'
                        );
                })
                ->select([
                    "{$userTable}.id as user_id",
                    "{$userTable}.governorate_id as old_governorate_id",
                    "{$userTable}.city_id as city_id",
                    'cities.governorate_id as new_governorate_id',
                ])
                ->get();

            foreach ($broken as $row) {
                DB::table(self::BACKUP_TABLE)->insert([
                    'user_table' => $userTable,
                    'user_id' => $row->user_id,
                    'old_governorate_id' => $row->old_governorate_id,
                    'new_governorate_id' => $row->new_governorate_id,
                    'city_id' => $row->city_id,
                    'repaired_at' => now(),
                ]);

                //DB::table bypasses soft-delete scopes and leaves updated_at
                //alone, so the repair stays invisible to "recently edited" sorts
                DB::table($userTable)
                    ->where('id', $row->user_id)
                    ->update(['governorate_id' => $row->new_governorate_id]);
            }
        }
    }

    /**
     * Restore the exact pre-repair governorate_id for every row this migration
     * changed, then drop the journal. Rows edited by a human after the repair
     * (governorate_id no longer equal to the repaired value) are left alone.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable(self::BACKUP_TABLE)) {
            return;
        }

        foreach (DB::table(self::BACKUP_TABLE)->orderBy('id')->get() as $backup) {
            if (!Schema::hasTable($backup->user_table)) {
                continue;
            }

            DB::table($backup->user_table)
                ->where('id', $backup->user_id)
                ->where('governorate_id', $backup->new_governorate_id)
                ->update(['governorate_id' => $backup->old_governorate_id]);
        }

        Schema::dropIfExists(self::BACKUP_TABLE);
    }
}
