<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Integration;

use ADS\TakeHome\Framework\MySQL;
use ADS\TakeHome\Weather\DateRange;
use ADS\TakeHome\Weather\MySqlWeatherRepository;
use ADS\TakeHome\Weather\Pagination;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * A valódi adatbázison futó teszt: a lapozás és a statisztika SQL-je a betöltött adatokkal.
 * A kapcsolat adatai a `phpunit.xml` `DB_*` környezeti változóiból jönnek; ha az adatbázis
 * nem érhető el, a tesztek hibával állnak le, nem maradnak ki csendben.
 */
#[Group('integration')]
final class MySqlWeatherRepositoryTest extends TestCase
{
    private static MySqlWeatherRepository $repository;

    /** Egyetlen, a teszteknél közös kapcsolat; a tesztek csak olvasnak. */
    public static function setUpBeforeClass(): void
    {
        self::$repository = new MySqlWeatherRepository(new MySQL(
            self::env('DB_HOST'),
            self::env('DB_NAME'),
            self::env('DB_USER'),
            self::env('DB_PASSWORD'),
            (int) self::env('DB_PORT'),
        ));
    }

    #[Test]
    public function budapestDecemberStatistics(): void
    {
        $statistics = self::$repository->getDailyWeatherStatistics(1, DateRange::fromStrings('2023-12-01', '2023-12-31'));

        $this->assertSame(31, $statistics->totalDays);
        $summary = $statistics->summary;
        $this->assertSame(0.5, $summary->minTemp->median);
        $this->assertSame(6.8, $summary->maxTemp->median);
        $this->assertSame(3.9, $summary->avgTemp->median);
        $this->assertSame(0.0, $summary->precipitation->median);
        $this->assertSame([0.4], $summary->minTemp->modes);
        $this->assertSame([2.3, 4.4, 6.6, 7.6], $summary->maxTemp->modes);
        $this->assertSame([1.5, 5.25, 6.05], $summary->avgTemp->modes);
        $this->assertSame([0.0], $summary->precipitation->modes);
    }

    #[Test]
    public function medianOfEvenCountIsAverageOfMiddleTwo(): void
    {
        $statistics = self::$repository->getDailyWeatherStatistics(1, DateRange::fromStrings('2023-12-01', '2023-12-02'));

        $this->assertSame(2, $statistics->totalDays);
        $this->assertSame(0.35, $statistics->summary->minTemp->median);
        $this->assertSame(3.35, $statistics->summary->maxTemp->median);
    }

    #[Test]
    public function firstPageHasHundredDaysWithSqlComputedAverage(): void
    {
        $days = self::$repository->findDailyWeatherPage(1, $this->year2023(), new Pagination(1));

        $this->assertCount(100, $days);
        $this->assertSame('2023-01-01', $days[0]->date->format('Y-m-d'));
        $this->assertSame(3.4, $days[0]->minTemp);
        $this->assertSame(8.5, $days[0]->maxTemp);
        $this->assertSame(5.95, $days[0]->avgTemp);
    }

    #[Test]
    public function lastPageHoldsTheRemainingDays(): void
    {
        $days = self::$repository->findDailyWeatherPage(1, $this->year2023(), new Pagination(4));

        $this->assertCount(65, $days);
        $this->assertSame('2023-12-31', $days[64]->date->format('Y-m-d'));
    }

    #[Test]
    public function regionAverageSkipsCitiesWithMissingMeasurement(): void
    {
        $range = DateRange::fromStrings('1938-11-01', '1938-11-01');

        $days = self::$repository->findRegionDailyAveragesPage('Dunántúl', $range, new Pagination(1));
        $statistics = self::$repository->getRegionDailyAveragesStatistics('Dunántúl', $range);

        $this->assertCount(1, $days);
        $this->assertSame(0.0, $days[0]->precipitation);
        $this->assertSame(3.05, $days[0]->minTemp);
        $this->assertSame(12.23, $days[0]->maxTemp);
        $this->assertSame(7.64, $days[0]->avgTemp);
        $this->assertSame(1, $statistics->totalDays);
    }

    #[Test]
    public function cityMissingMeasurementIsNull(): void
    {
        $range = DateRange::fromStrings('1938-11-01', '1938-11-01');

        $days = self::$repository->findDailyWeatherPage(3, $range, new Pagination(1));

        $this->assertCount(1, $days);
        $this->assertNull($days[0]->precipitation);
    }

    #[Test]
    public function singleCityRegionStatisticsEqualTheCitys(): void
    {
        $range = $this->year2023();

        $this->assertEquals(
            self::$repository->getDailyWeatherStatistics(1, $range),
            self::$repository->getRegionDailyAveragesStatistics('Közép-Magyarország', $range),
        );
    }

    private function year2023(): DateRange
    {
        return DateRange::fromStrings('2023-01-01', '2023-12-31');
    }

    private static function env(string $name): string
    {
        $value = getenv($name);
        if ($value === false) {
            throw new RuntimeException("Hiányzó környezeti változó: {$name} (ld. phpunit.xml)");
        }
        return $value;
    }
}
