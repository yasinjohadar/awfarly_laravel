<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCategoryLookupIndexesToInterestPivots extends Migration
{
    /**
     * The notification fan-out reads these pivots the other way round from
     * everything else: `whereIn('category_id', $ids)->pluck(user_id)`.
     *
     * The unique pairs added by 2026_08_16_130100 are user-leading, so they are
     * no help there, and the standalone FK index on category_id finds the rows
     * but then goes back to the table for every user id. Since interest matching
     * became a branch walk (CategoryTree::branchIds) that id set is larger than
     * it used to be, so make the lookup covering.
     *
     * @return void
     */
    public function up()
    {
        $this->addIndex('customer_categories', ['category_id', 'customer_id'], 'cus_categories_category_lookup');
        $this->addIndex('advertiser_interests', ['category_id', 'advertiser_id'], 'adv_interests_category_lookup');
    }

    /**
     * @return void
     */
    public function down()
    {
        $this->dropIndex('customer_categories', 'cus_categories_category_lookup');
        $this->dropIndex('advertiser_interests', 'adv_interests_category_lookup');
    }

    /**
     * Guarded on both the table and the index: advertiser_interests is a recent
     * table, and this project has already been bitten once by MySQL reshuffling
     * indexes underneath a migration (see 2026_08_16_130100::down()).
     *
     * @param string $table
     * @param string[] $columns
     * @param string $name
     * @return void
     */
    private function addIndex(string $table, array $columns, string $name): void
    {
        if (!Schema::hasTable($table) || $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
            $blueprint->index($columns, $name);
        });
    }

    /**
     * @param string $table
     * @param string $name
     * @return void
     */
    private function dropIndex(string $table, string $name): void
    {
        if (!Schema::hasTable($table) || !$this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($name) {
            $blueprint->dropIndex($name);
        });
    }

    /**
     * @param string $table
     * @param string $name
     * @return bool
     */
    private function indexExists(string $table, string $name): bool
    {
        return !empty(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$name]));
    }
}
