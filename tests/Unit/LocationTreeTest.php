<?php

namespace Tests\Unit;

use App\Helpers\Geography\LocationTree;
use PHPUnit\Framework\TestCase;

/**
 * The governorate/city hierarchy, and the visibility rule expressed over it.
 *
 * LocationTree itself only answers "which governorate is this city in" and the
 * reverse; the rule lives in Geography, where it becomes SQL. So the matrix test
 * at the bottom re-states the rule in plain PHP against the same tree, which is
 * what pins the intent: two cities in one governorate are siblings and never
 * match, and a city interest reaches governorate-LEVEL content only.
 *
 * Runs without a database: LocationTree::fake() injects the edge list directly.
 */
class LocationTreeTest extends TestCase
{
    /**
     * RifDimashq(2) -> Douma(20), Harasta(21), Jobar(22)
     * Damascus(1)   -> Mazzeh(10)
     */
    private const CITY_TO_GOVERNORATE = [
        10 => 1,
        20 => 2,
        21 => 2,
        22 => 2,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        LocationTree::fake(self::CITY_TO_GOVERNORATE);
    }

    protected function tearDown(): void
    {
        LocationTree::fake([]);

        parent::tearDown();
    }

    /**
     * @param int[] $expected
     * @param int[] $actual
     */
    private function assertIdsEqual(array $expected, array $actual, string $message = ''): void
    {
        sort($expected);
        sort($actual);

        $this->assertSame($expected, $actual, $message);
    }

    public function test_a_city_resolves_to_its_governorate(): void
    {
        $this->assertIdsEqual([2], LocationTree::governorateIdsOfCities([20]));
        $this->assertIdsEqual([2], LocationTree::governorateIdsOfCities([20, 21]), 'two cities of one governorate collapse to one id');
        $this->assertIdsEqual([1, 2], LocationTree::governorateIdsOfCities([10, 20]));
    }

    public function test_a_governorate_resolves_to_its_cities(): void
    {
        $this->assertIdsEqual([20, 21, 22], LocationTree::cityIdsOfGovernorates([2]));
        $this->assertIdsEqual([10], LocationTree::cityIdsOfGovernorates([1]));
        $this->assertIdsEqual([10, 20, 21, 22], LocationTree::cityIdsOfGovernorates([1, 2]));
    }

    public function test_unknown_and_invalid_ids_yield_nothing(): void
    {
        $this->assertSame([], LocationTree::governorateIdsOfCities([]));
        $this->assertSame([], LocationTree::governorateIdsOfCities([0]));
        $this->assertSame([], LocationTree::governorateIdsOfCities([999]));
        $this->assertSame([], LocationTree::cityIdsOfGovernorates([]));
        $this->assertSame([], LocationTree::cityIdsOfGovernorates([999]));
    }

    public function test_a_city_without_a_governorate_is_left_out_of_both_directions(): void
    {
        LocationTree::fake([30 => null, 31 => 0, 32 => 3]);

        $this->assertSame([], LocationTree::governorateIdsOfCities([30]));
        $this->assertSame([], LocationTree::governorateIdsOfCities([31]));
        $this->assertIdsEqual([3], LocationTree::governorateIdsOfCities([32]));
        $this->assertIdsEqual([32], LocationTree::cityIdsOfGovernorates([3]));
    }

    /**
     * The rule, re-stated over the same tree.
     *
     * A viewer picks governorates and/or cities. Content sits at a (governorate,
     * city) pair, where a null city means "filed at the governorate's level".
     *
     * @param int[] $pickedGovernorates
     * @param int[] $pickedCities
     */
    private function isVisible(array $pickedGovernorates, array $pickedCities, int $governorate, ?int $city): bool
    {
        //a picked governorate matches anything inside it, city or not
        if (in_array($governorate, $pickedGovernorates, true)) {
            return true;
        }

        //a picked city matches only itself...
        if ($city !== null) {
            return in_array($city, $pickedCities, true);
        }

        //...and, where the content names no city, the governorate it sits in
        return in_array($governorate, LocationTree::governorateIdsOfCities($pickedCities), true);
    }

    /** The specified matrix, translated to governorates and cities. */
    public function test_the_specified_visibility_matrix(): void
    {
        $cases = [
            //[picked governorates, picked cities, content governorate, content city, visible?]

            //picked a governorate -> everything inside it
            [[2], [], 2, 20, true],
            [[2], [], 2, 21, true],
            [[2], [], 2, null, true],
            [[2], [], 1, 10, false],

            //picked a city -> itself, and governorate-level content
            [[], [20], 2, 20, true],
            [[], [20], 2, null, true],

            //...but never a sibling city
            [[], [20], 2, 21, false],
            [[], [20], 2, 22, false],

            //...and nothing in another governorate
            [[], [20], 1, 10, false],
            [[], [20], 1, null, false],

            //several cities picked
            [[], [20, 22], 2, 20, true],
            [[], [20, 22], 2, 22, true],
            [[], [20, 22], 2, 21, false],
            [[], [20, 22], 2, null, true],

            //a governorate and one of its cities together
            [[2], [20], 2, 21, true],
            [[2], [20], 2, null, true],

            //cities in two different governorates
            [[], [10, 20], 1, 10, true],
            [[], [10, 20], 2, 21, false],
            [[], [10, 20], 1, null, true],
            [[], [10, 20], 2, null, true],
        ];

        foreach ($cases as [$governorates, $cities, $contentGovernorate, $contentCity, $expected]) {
            $visible = $this->isVisible($governorates, $cities, $contentGovernorate, $contentCity);

            $this->assertSame($expected, $visible, sprintf(
                'picked governorates [%s] + cities [%s] vs content (governorate %d, city %s) should be %s',
                implode(',', $governorates),
                implode(',', $cities),
                $contentGovernorate,
                $contentCity === null ? 'none' : (string) $contentCity,
                $expected ? 'VISIBLE' : 'HIDDEN'
            ));
        }
    }

    /**
     * The sibling case is the one that a naive "just add the governorate of my
     * city" implementation breaks, so it gets its own assertion.
     */
    public function test_deriving_a_governorate_never_opens_up_sibling_cities(): void
    {
        $this->assertFalse($this->isVisible([], [20], 2, 21), 'Harasta is a sibling of Douma, not a match');
        $this->assertTrue($this->isVisible([], [20], 2, null), 'governorate-level content still reaches a city follower');
        $this->assertTrue($this->isVisible([2], [], 2, 21), 'picking the governorate outright does reach Harasta');
    }

    /**
     * The rule is symmetric, so the notification fan-out - which asks the same
     * question from the content end - can never disagree with the feed.
     */
    public function test_the_match_is_symmetric_between_feed_and_fan_out(): void
    {
        $governorates = [1, 2];
        $cities = array_keys(self::CITY_TO_GOVERNORATE);

        //every possible piece of content: a (governorate, city) pair, or a
        //governorate on its own
        $contents = [];
        foreach ($cities as $city) {
            $contents[] = [self::CITY_TO_GOVERNORATE[$city], $city];
        }
        foreach ($governorates as $governorate) {
            $contents[] = [$governorate, null];
        }

        foreach ($contents as [$contentGovernorate, $contentCity]) {
            //who does the fan-out reach for this content?
            $reachedGovernorates = [$contentGovernorate];
            $reachedCities = $contentCity !== null
                ? [$contentCity]
                : LocationTree::cityIdsOfGovernorates([$contentGovernorate]);

            //every single-interest viewer must agree with that
            foreach ($governorates as $governorate) {
                $this->assertSame(
                    in_array($governorate, $reachedGovernorates, true),
                    $this->isVisible([$governorate], [], $contentGovernorate, $contentCity),
                    sprintf('governorate follower %d disagrees about content (%d, %s)', $governorate, $contentGovernorate, $contentCity ?? 'none')
                );
            }

            foreach ($cities as $city) {
                $this->assertSame(
                    in_array($city, $reachedCities, true),
                    $this->isVisible([], [$city], $contentGovernorate, $contentCity),
                    sprintf('city follower %d disagrees about content (%d, %s)', $city, $contentGovernorate, $contentCity ?? 'none')
                );
            }
        }
    }
}
