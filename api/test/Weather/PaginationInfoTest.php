<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Weather;

use ADS\TakeHome\Weather\PaginationInfo;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaginationInfoTest extends TestCase
{
    #[Test]
    public function isEmptyOnlyWithoutItems(): void
    {
        $this->assertTrue((new PaginationInfo(1, 100, 0))->isEmpty());
        $this->assertFalse((new PaginationInfo(1, 100, 1))->isEmpty());
    }

    #[Test]
    #[DataProvider('totals')]
    public function totalPagesRoundsUp(int $totalItems, int $expectedPages): void
    {
        $this->assertSame($expectedPages, (new PaginationInfo(1, 100, $totalItems))->totalPages());
    }

    /**
     * @return array<string, array{int, int}>
     */
    public static function totals(): array
    {
        return [
            'empty' => [0, 0],
            'single item' => [1, 1],
            'exactly one page' => [100, 1],
            'one over' => [101, 2],
            'a year' => [365, 4],
        ];
    }

    #[Test]
    public function serializesToContractShape(): void
    {
        $this->assertSame(
            '{"page":4,"pageSize":100,"totalItems":365,"totalPages":4}',
            json_encode(new PaginationInfo(4, 100, 365)),
        );
    }

    #[Test]
    #[DataProvider('pagesBeyondEnd')]
    public function detectsPageBeyondEnd(int $page, int $totalItems, bool $expected): void
    {
        $this->assertSame($expected, (new PaginationInfo($page, 100, $totalItems))->isPageBeyondEnd());
    }

    /**
     * @return array<string, array{int, int, bool}>
     */
    public static function pagesBeyondEnd(): array
    {
        return [
            'last page' => [4, 365, false],
            'one past the end' => [5, 365, true],
            'first page of empty series' => [1, 0, false],
            'later page of empty series' => [2, 0, false],
        ];
    }
}
