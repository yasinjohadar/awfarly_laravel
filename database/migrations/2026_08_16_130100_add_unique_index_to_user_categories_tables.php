<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexToUserCategoriesTables extends Migration
{
    /**
     * These pivots had no unique constraint, which is why every writer resorted
     * to "delete them all, then re-insert" — a pattern that also destroyed the
     * row ordering the default post category depends on.
     *
     * A unique pair makes real sync semantics (add the missing, remove only the
     * removed) possible, and makes insertOrIgnore safe.
     *
     * @return void
     */
    public function up()
    {
        //deduplicate defensively: the index cannot be created over duplicates,
        //and another environment may have them even though this one does not
        $this->removeDuplicates('advertiser_categories', 'advertiser_id');
        $this->removeDuplicates('customer_categories', 'customer_id');

        Schema::table('advertiser_categories', function (Blueprint $table) {
            $table->unique(['advertiser_id', 'category_id'], 'adv_categories_unique');
        });

        Schema::table('customer_categories', function (Blueprint $table) {
            $table->unique(['customer_id', 'category_id'], 'cus_categories_unique');
        });
    }

    /**
     * MySQL silently drops the standalone FK index on the user column when the
     * composite unique is added, because the composite covers that column as
     * its leftmost part. The foreign key then depends on the composite, and
     * dropping it fails with "needed in a foreign key constraint".
     *
     * So restore a standalone index on the user column first, which gives the
     * foreign key something else to lean on, and only then drop the composite.
     *
     * @return void
     */
    public function down()
    {
        $this->dropCompositeUnique('advertiser_categories', 'advertiser_id', 'adv_categories_unique');
        $this->dropCompositeUnique('customer_categories', 'customer_id', 'cus_categories_unique');
    }

    /**
     * @param string $table
     * @param string $userColumn
     * @param string $indexName
     * @return void
     */
    private function dropCompositeUnique(string $table, string $userColumn, string $indexName): void
    {
        $fallbackIndex = "{$table}_{$userColumn}_fallback";

        DB::statement("CREATE INDEX `{$fallbackIndex}` ON `{$table}` (`{$userColumn}`)");

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropUnique($indexName);
        });
    }

    /**
     * Keep the lowest id of each (user, category) pair — the same row the
     * existing `->first()` default-category lookup would have picked.
     *
     * @param string $table
     * @param string $userColumn
     * @return void
     */
    private function removeDuplicates(string $table, string $userColumn): void
    {
        $keep = DB::table($table)
            ->selectRaw('MIN(id) as id')
            ->groupBy($userColumn, 'category_id')
            ->pluck('id');

        DB::table($table)->whereNotIn('id', $keep)->delete();
    }
}
