<?php

namespace Tests\Unit;

use App\Helpers\Advertisers\OfferLimits;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class OfferLimitsDecisionTest extends TestCase
{
    public function test_blocks_when_active_limit_reached()
    {
        $this->assertSame('active', $this->decide(5, 5, 1, 30));
    }

    public function test_blocks_when_monthly_limit_reached()
    {
        $this->assertSame('monthly', $this->decide(2, 10, 30, 30));
    }

    public function test_allows_when_under_both_limits()
    {
        $this->assertNull($this->decide(2, 10, 5, 30));
    }

    public function test_active_limit_checked_before_monthly()
    {
        $this->assertSame('active', $this->decide(10, 10, 50, 30));
    }

    private function decide(int $activeCount, int $activeLimit, int $monthlyCount, int $monthlyLimit): ?string
    {
        if ($activeCount >= $activeLimit) {
            return 'active';
        }
        if ($monthlyCount >= $monthlyLimit) {
            return 'monthly';
        }

        return null;
    }
}
