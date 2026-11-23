<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Weather;

use ADS\TakeHome\Cities\City;
use ADS\TakeHome\Weather\CityWeatherReport;
use ADS\TakeHome\Weather\ColumnSummary;
use ADS\TakeHome\Weather\DailyWeather;
use ADS\TakeHome\Weather\DateRange;
use ADS\TakeHome\Weather\PaginationInfo;
use ADS\TakeHome\Weather\WeatherSeries;
use ADS\TakeHome\Weather\WeatherSummary;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CityWeatherReportTest extends TestCase
{
    #[Test]
    public function reportSerializesToContractShape(): void
    {
        $days = [new DailyWeather(new DateTimeImmutable('2023-01-01'), 4.2, 11.0, 7.6, 0.0)];
        $report = new CityWeatherReport(
            new City(1, 'Budapest', 'Közép-Magyarország'),
            new WeatherSeries(
                DateRange::fromStrings('2023-01-01', '2023-01-01'),
                new WeatherSummary(
                    new ColumnSummary(4.2, []),
                    new ColumnSummary(11.0, []),
                    new ColumnSummary(7.6, []),
                    new ColumnSummary(0.0, []),
                ),
                new PaginationInfo(1, 100, 1),
                $days,
            ),
        );

        $this->assertSame(
            '{"city":{"id":1,"name":"Budapest","region":"Közép-Magyarország"},'
            . '"from":"2023-01-01","to":"2023-01-01",'
            . '"units":{"temperature":"°C","precipitation":"mm"},'
            . '"summary":{"minTemp":{"median":4.2,"modes":[]},"maxTemp":{"median":11,"modes":[]},'
            . '"avgTemp":{"median":7.6,"modes":[]},"precipitation":{"median":0,"modes":[]}},'
            . '"pagination":{"page":1,"pageSize":100,"totalItems":1,"totalPages":1},'
            . '"days":[{"date":"2023-01-01","minTemp":4.2,"maxTemp":11,"avgTemp":7.6,"precipitation":0}]}',
            json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        );
    }

    #[Test]
    public function missingMeasurementsSerializeToNull(): void
    {
        $day = new DailyWeather(new DateTimeImmutable('2023-01-01'), null, 1.5, null, null);

        $this->assertSame(
            '{"date":"2023-01-01","minTemp":null,"maxTemp":1.5,"avgTemp":null,"precipitation":null}',
            json_encode($day),
        );
    }
}
