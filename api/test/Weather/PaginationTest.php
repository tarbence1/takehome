<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Weather;

use ADS\TakeHome\Validation\ValidationException;
use ADS\TakeHome\Weather\Pagination;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaginationTest extends TestCase
{
    #[Test]
    public function pageSizeIsOneHundred(): void
    {
        $this->assertSame(100, Pagination::PAGE_SIZE);
    }

    #[Test]
    public function offsetSkipsPreviousPages(): void
    {
        $this->assertSame(0, (new Pagination(1))->offset());
        $this->assertSame(200, (new Pagination(3))->offset());
    }

    #[Test]
    #[DataProvider('invalidPages')]
    public function rejectsPageBelowOne(int $page): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Pagination($page);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function invalidPages(): array
    {
        return ['zero' => [0], 'negative' => [-3], 'offset would overflow' => [Pagination::maxPage() + 1]];
    }

    #[Test]
    public function offsetOfMaxPageStaysAnInteger(): void
    {
        $this->assertIsInt((new Pagination(Pagination::maxPage()))->offset());
    }

    #[Test]
    public function fromRequestedPageRejectsOutOfRangeAsValidationError(): void
    {
        $this->expectException(ValidationException::class);

        Pagination::fromRequestedPage(Pagination::maxPage() + 1);
    }

    #[Test]
    public function describeReportsPageSizeAndTotals(): void
    {
        $info = (new Pagination(2))->describe(250);

        $this->assertSame(2, $info->page);
        $this->assertSame(Pagination::PAGE_SIZE, $info->pageSize);
        $this->assertSame(250, $info->totalItems);
        $this->assertSame(3, $info->totalPages());
    }
}
