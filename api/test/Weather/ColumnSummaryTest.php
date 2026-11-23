<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Weather;

use ADS\TakeHome\Weather\ColumnSummary;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ColumnSummaryTest extends TestCase
{
    #[Test]
    public function medianOfOddCountIsTheMiddleValue(): void
    {
        $this->assertSame(2.0, ColumnSummary::fromValues([3.0, 1.0, 2.0])->median);
    }

    #[Test]
    public function medianOfEvenCountIsAverageOfMiddleTwoWithoutFloatNoise(): void
    {
        $this->assertSame(0.35, ColumnSummary::fromValues([0.4, 0.1, 0.3, 0.9])->median);
    }

    #[Test]
    public function emptyColumnHasNullMedianAndNoModes(): void
    {
        $summary = ColumnSummary::fromValues([]);

        $this->assertNull($summary->median);
        $this->assertSame([], $summary->modes);
    }

    #[Test]
    public function modesAreAllMostFrequentValuesInAscendingOrder(): void
    {
        $this->assertSame([-1.5, 2.3], ColumnSummary::fromValues([2.3, 0.0, -1.5, 2.3, -1.5])->modes);
    }

    #[Test]
    public function noModeWhenEveryValueOccursOnce(): void
    {
        $this->assertSame([], ColumnSummary::fromValues([1.0, 2.0, 3.0])->modes);
    }
}
