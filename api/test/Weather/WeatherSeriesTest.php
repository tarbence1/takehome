<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Weather;

use ADS\TakeHome\Weather\ColumnSummary;
use ADS\TakeHome\Weather\DailyWeather;
use ADS\TakeHome\Weather\DateRange;
use ADS\TakeHome\Weather\PaginationInfo;
use ADS\TakeHome\Weather\WeatherSeries;
use ADS\TakeHome\Weather\WeatherSummary;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WeatherSeriesTest extends TestCase
{
    #[Test]
    public function serializesPeriodUnitsSummaryPaginationAndDays(): void
    {
        $days = [
            new DailyWeather(new DateTimeImmutable('2023-01-01'), 4.2, 11.0, 7.6, 0.0),
            new DailyWeather(new DateTimeImmutable('2023-01-02'), null, 1.5, null, null),
        ];
        $series = new WeatherSeries(
            DateRange::fromStrings('2023-01-01', '2023-01-02'),
            new WeatherSummary(
                new ColumnSummary(4.2, [4.2]),
                new ColumnSummary(6.25, []),
                new ColumnSummary(7.6, []),
                new ColumnSummary(0.0, []),
            ),
            new PaginationInfo(1, 100, 2),
            $days,
        );

        $column = static fn (string $median, string $modes = ''): string => '{"median":' . $median . ',"modes":[' . $modes . ']}';
        $this->assertSame(
            '{"from":"2023-01-01","to":"2023-01-02",'
            . '"units":{"temperature":"°C","precipitation":"mm"},'
            . '"summary":{"minTemp":' . $column('4.2', '4.2') . ',"maxTemp":' . $column('6.25') . ','
            . '"avgTemp":' . $column('7.6') . ',"precipitation":' . $column('0') . '},'
            . '"pagination":{"page":1,"pageSize":100,"totalItems":2,"totalPages":1},'
            . '"days":[{"date":"2023-01-01","minTemp":4.2,"maxTemp":11,"avgTemp":7.6,"precipitation":0},'
            . '{"date":"2023-01-02","minTemp":null,"maxTemp":1.5,"avgTemp":null,"precipitation":null}]}',
            json_encode($series, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        );
    }
}
