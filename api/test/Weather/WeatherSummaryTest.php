<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Weather;

use ADS\TakeHome\Weather\ColumnSummary;
use ADS\TakeHome\Weather\DailyWeather;
use ADS\TakeHome\Weather\WeatherSummary;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WeatherSummaryTest extends TestCase
{
    #[Test]
    public function serializesEachColumnWithMedianAndModes(): void
    {
        $summary = new WeatherSummary(
            new ColumnSummary(1.0, [1.0]),
            new ColumnSummary(5.0, [4.5, 5.0]),
            new ColumnSummary(2.5, []),
            new ColumnSummary(null, []),
        );

        $this->assertSame(
            '{"minTemp":{"median":1,"modes":[1]},'
            . '"maxTemp":{"median":5,"modes":[4.5,5]},'
            . '"avgTemp":{"median":2.5,"modes":[]},'
            . '"precipitation":{"median":null,"modes":[]}}',
            json_encode($summary),
        );
    }

    #[Test]
    public function fromDaysSummarizesEachColumnSkippingMissingMeasurements(): void
    {
        $summary = WeatherSummary::fromDays([
            new DailyWeather(new DateTimeImmutable('2023-12-01'), 1.0, 5.0, 3.0, 0.0),
            new DailyWeather(new DateTimeImmutable('2023-12-02'), 1.0, null, null, 0.0),
            new DailyWeather(new DateTimeImmutable('2023-12-03'), 3.0, 7.0, 5.0, null),
        ]);

        $this->assertEquals(
            new WeatherSummary(
                new ColumnSummary(1.0, [1.0]),
                new ColumnSummary(6.0, []),
                new ColumnSummary(4.0, []),
                new ColumnSummary(0.0, [0.0]),
            ),
            $summary,
        );
    }
}
