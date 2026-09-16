<?php

namespace Tests\Unit;

use App\Helpers\Categories\CategoryTree;
use PHPUnit\Framework\TestCase;

/**
 * The interest visibility rule, pinned.
 *
 * Content is visible to a user iff the content's category and one of the user's
 * interests lie on one root-to-leaf path. The case that keeps getting broken by
 * a well-meaning refactor is the sibling case, so it gets its own test.
 *
 * Runs without a database: CategoryTree::fake() injects the edge list directly.
 */
class CategoryTreeTest extends TestCase
{
    /**
     * Programming(1)
     *   |- WebDev(2)
     *   |    `- Laravel(5)
     *   |- MobileDev(3)
     *   `- AI(4)
     *
     * Design(6)
     *   `- UIUX(7)
     */
    private const TREE = [
        1 => null,
        2 => 1,
        3 => 1,
        4 => 1,
        5 => 2,
        6 => null,
        7 => 6,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        CategoryTree::fake(self::TREE);
    }

    protected function tearDown(): void
    {
        CategoryTree::fake([]);

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

    /** Interest on a parent reaches everything filed underneath it. */
    public function test_parent_interest_sees_the_whole_subtree(): void
    {
        $this->assertIdsEqual([1, 2, 3, 4, 5], CategoryTree::branchIds([1]));
    }

    /** Interest on a child reaches itself, its own subtree, and parent-level content. */
    public function test_child_interest_sees_itself_its_subtree_and_its_ancestors(): void
    {
        $ids = CategoryTree::branchIds([2]);

        $this->assertIdsEqual([1, 2, 5], $ids);
        $this->assertContains(1, $ids, 'parent-level content must reach a child-interested user');
        $this->assertContains(5, $ids, 'a deeper child of the picked category must be included');
    }

    /** The whole point: siblings never match. */
    public function test_child_interest_never_reaches_a_sibling(): void
    {
        $ids = CategoryTree::branchIds([2]);

        $this->assertNotContains(3, $ids, 'MobileDev is a sibling of WebDev, not a match');
        $this->assertNotContains(4, $ids, 'AI is a sibling of WebDev, not a match');
    }

    /**
     * The regression test for the one refactor that silently breaks the rule.
     *
     * Writing branchIds() as "walk up, then expand the result down" produces
     * {1,2,3,4,5} here, because descendants(ancestors(WebDev)) is every child of
     * Programming. The two walks must both start from the seeds and only from
     * the seeds.
     */
    public function test_ancestors_are_not_re_expanded_downward(): void
    {
        $ids = CategoryTree::branchIds([2, 4]);

        $this->assertIdsEqual([1, 2, 4, 5], $ids);
        $this->assertNotContains(3, $ids, 'MobileDev was not picked and is nobody\'s ancestor here');
    }

    /** Ancestors are walked all the way up, not one level. */
    public function test_ancestors_are_walked_to_the_root(): void
    {
        $this->assertIdsEqual([1, 2, 5], CategoryTree::branchIds([5]));
    }

    /** Two unrelated roots stay unrelated. */
    public function test_separate_roots_do_not_bleed_into_each_other(): void
    {
        $ids = CategoryTree::branchIds([2]);

        $this->assertNotContains(6, $ids);
        $this->assertNotContains(7, $ids);
    }

    /** subtreeIds() is downward only - the browse set. */
    public function test_subtree_is_downward_only_and_full_depth(): void
    {
        $this->assertIdsEqual([1, 2, 3, 4, 5], CategoryTree::subtreeIds([1]));
        $this->assertIdsEqual([2, 5], CategoryTree::subtreeIds([2]), 'browsing a child must not pull in its parent');
    }

    /** Empty / junk input yields nothing, so callers keep their fail-open path. */
    public function test_empty_and_invalid_input_yields_nothing(): void
    {
        $this->assertSame([], CategoryTree::branchIds([]));
        $this->assertSame([], CategoryTree::branchIds([0]));
        $this->assertSame([], CategoryTree::branchIds(['not-an-id']));
        $this->assertSame([], CategoryTree::subtreeIds([]));
    }

    /** An id that is not in the tree at all is still itself. */
    public function test_unknown_id_returns_only_itself(): void
    {
        $this->assertIdsEqual([999], CategoryTree::branchIds([999]));
    }

    /** A two-node cycle terminates instead of hanging. */
    public function test_a_cycle_terminates(): void
    {
        CategoryTree::fake([1 => 2, 2 => 1]);

        $this->assertIdsEqual([1, 2], CategoryTree::branchIds([1]));
        $this->assertIdsEqual([1, 2], CategoryTree::subtreeIds([2]));
    }

    /** A row that is its own parent is treated as a root. */
    public function test_self_parent_is_treated_as_a_root(): void
    {
        CategoryTree::fake([7 => 7, 8 => 7]);

        $this->assertIdsEqual([7, 8], CategoryTree::branchIds([7]));
        $this->assertIdsEqual([7, 8], CategoryTree::branchIds([8]));
    }

    /** A row pointing at a parent that no longer exists is treated as a root. */
    public function test_orphan_is_treated_as_a_root(): void
    {
        CategoryTree::fake([9 => 999]);

        $this->assertIdsEqual([9], CategoryTree::branchIds([9]));
    }

    /** The product owner's worked example, verbatim. */
    public function test_the_specified_visibility_matrix(): void
    {
        $cases = [
            //[interest ids, content category id, visible?]
            [[1], 1, true],
            [[1], 2, true],
            [[1], 3, true],
            [[1], 4, true],

            [[2], 1, true],
            [[2], 2, true],
            [[2], 3, false],
            [[2], 4, false],

            [[3], 1, true],
            [[3], 2, false],
            [[3], 3, true],
            [[3], 4, false],

            [[2, 4], 1, true],
            [[2, 4], 2, true],
            [[2, 4], 3, false],
            [[2, 4], 4, true],

            [[1, 2], 3, true],
        ];

        foreach ($cases as [$interests, $categoryId, $expected]) {
            $visible = in_array($categoryId, CategoryTree::branchIds($interests), true);

            $this->assertSame($expected, $visible, sprintf(
                'interests [%s] + content category %d should be %s',
                implode(',', $interests),
                $categoryId,
                $expected ? 'VISIBLE' : 'HIDDEN'
            ));
        }
    }

    /**
     * The rule is symmetric, so the notification fan-out (which asks the same
     * question from the content end) can never disagree with the feed.
     */
    public function test_the_match_is_symmetric_between_feed_and_fan_out(): void
    {
        foreach (array_keys(self::TREE) as $interest) {
            foreach (array_keys(self::TREE) as $categoryId) {
                $feedSeesIt = in_array($categoryId, CategoryTree::branchIds([$interest]), true);
                $fanOutReachesThem = in_array($interest, CategoryTree::branchIds([$categoryId]), true);

                $this->assertSame($feedSeesIt, $fanOutReachesThem, sprintf(
                    'interest %d vs content category %d disagree between feed and fan-out',
                    $interest,
                    $categoryId
                ));
            }
        }
    }
}
