<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Weather;

use ADS\TakeHome\Validation\ValidationException;
use ADS\TakeHome\Weather\DateRange;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DateRangeTest extends TestCase
{
    #[Test]
    public function parsesValidRange(): void
    {
        $range = DateRange::fromStrings('2023-01-01', '2023-01-31');

        $this->assertSame('2023-01-01', $range->from->format('Y-m-d'));
        $this->assertSame('2023-01-31', $range->to->format('Y-m-d'));
    }

    #[Test]
    public function allowsSingleDay(): void
    {
        $range = DateRange::fromStrings('2023-05-05', '2023-05-05');

        $this->assertSame('2023-05-05', $range->from->format('Y-m-d'));
        $this->assertSame('2023-05-05', $range->to->format('Y-m-d'));
    }

    #[Test]
    public function allowsFullDatasetRange(): void
    {
        $range = DateRange::fromStrings('1901-01-01', '2023-12-31');

        $this->assertSame('1901-01-01', $range->from->format('Y-m-d'));
        $this->assertSame('2023-12-31', $range->to->format('Y-m-d'));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidRanges(): array
    {
        return [
            'from after to' => ['2023-02-01', '2023-01-01'],
            'nonexistent calendar date' => ['2023-02-30', '2023-03-01'],
            'missing zero padding' => ['2023-1-1', '2023-01-02'],
            'not a date' => ['abc', '2023-01-01'],
            'datetime instead of date' => ['2023-01-01T00:00', '2023-01-02'],
            'invalid to' => ['2023-01-01', '2023-13-01'],
        ];
    }

    #[Test]
    #[DataProvider('invalidRanges')]
    public function rejectsInvalid(string $from, string $to): void
    {
        $this->expectException(ValidationException::class);
        DateRange::fromStrings($from, $to);
    }
}
