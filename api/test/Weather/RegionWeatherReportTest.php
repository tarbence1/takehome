<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Weather;

use ADS\TakeHome\Cities\City;
use ADS\TakeHome\Weather\ColumnSummary;
use ADS\TakeHome\Weather\DailyWeather;
use ADS\TakeHome\Weather\DateRange;
use ADS\TakeHome\Weather\PaginationInfo;
use ADS\TakeHome\Weather\RegionWeatherReport;
use ADS\TakeHome\Weather\WeatherSeries;
use ADS\TakeHome\Weather\WeatherSummary;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RegionWeatherReportTest extends TestCase
{
    #[Test]
    public function serializesToContractShape(): void
    {
        $days = [new DailyWeather(new DateTimeImmutable('2023-01-01'), 1.23, 6.5, 3.87, null)];
        $report = new RegionWeatherReport(
            'Dunántúl',
            [new City(3, 'Keszthely', 'Dunántúl'), new City(7, 'Pécs', 'Dunántúl')],
            new WeatherSeries(
                DateRange::fromStrings('2023-01-01', '2023-01-01'),
                new WeatherSummary(
                    new ColumnSummary(1.23, []),
                    new ColumnSummary(6.5, []),
                    new ColumnSummary(3.87, []),
                    new ColumnSummary(null, []),
                ),
                new PaginationInfo(1, 100, 1),
                $days,
            ),
        );

        $this->assertSame(
            '{"region":"Dunántúl",'
            . '"cities":[{"id":3,"name":"Keszthely","region":"Dunántúl"},'
            . '{"id":7,"name":"Pécs","region":"Dunántúl"}],'
            . '"from":"2023-01-01","to":"2023-01-01",'
            . '"units":{"temperature":"°C","precipitation":"mm"},'
            . '"summary":{"minTemp":{"median":1.23,"modes":[]},"maxTemp":{"median":6.5,"modes":[]},'
            . '"avgTemp":{"median":3.87,"modes":[]},"precipitation":{"median":null,"modes":[]}},'
            . '"pagination":{"page":1,"pageSize":100,"totalItems":1,"totalPages":1},'
            . '"days":[{"date":"2023-01-01","minTemp":1.23,"maxTemp":6.5,"avgTemp":3.87,"precipitation":null}]}',
            json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        );
    }
}
