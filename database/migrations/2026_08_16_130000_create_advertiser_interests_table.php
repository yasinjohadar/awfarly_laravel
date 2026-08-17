<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAdvertiserInterestsTable extends Migration
{
    /**
     * Splits the two meanings `advertiser_categories` was carrying at once:
     *
     *   advertiser_categories -> the advertiser's OWN business categories: what
     *                            they publish under. Drives the default
     *                            category of a new post/offer and the
     *                            advertiser-level feed match.
     *   advertiser_interests  -> the categories the advertiser FOLLOWS, which
     *                            decide what appears in their own feed.
     *
     * Because one table served both, editing interests silently changed which
     * row was `->first()` and therefore the default category of every future
     * post.
     *
     * NOTE: `category_id` references `categories`. This is deliberately NOT the
     * `interests` table dropped by 2026_08_10_104336 — there is one taxonomy.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertiser_interests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();

            $table->unique(['advertiser_id', 'category_id'], 'adv_interests_unique');

            $table->foreign('advertiser_id')
                ->on('advertisers_users')
                ->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('category_id')
                ->on('categories')
                ->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        //Backfill by COPYING, not splitting. Existing rows serve both roles at
        //once, so every one of them is equally an own-category and an interest.
        //Leaving interests empty would be actively harmful: applyFeedCategoryFilter
        //returns the query unfiltered when the interest set is empty, so every
        //advertiser would flip from a filtered feed to an unfiltered one on
        //deploy day. Copying makes day-1 behaviour identical to day-0.
        $rows = DB::table('advertiser_categories')
            ->select('advertiser_id', 'category_id', 'created_at', 'updated_at')
            ->orderBy('id')
            ->get();

        foreach ($rows->chunk(200) as $chunk) {
            DB::table('advertiser_interests')->insertOrIgnore(
                $chunk->map(static function ($row) {
                    return [
                        'advertiser_id' => $row->advertiser_id,
                        'category_id' => $row->category_id,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ];
                })->all()
            );
        }
    }

    /**
     * Dropping the table restores the previous single-meaning behaviour: the
     * own-category rows are untouched and reading interests falls back to them.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertiser_interests');
    }
}
