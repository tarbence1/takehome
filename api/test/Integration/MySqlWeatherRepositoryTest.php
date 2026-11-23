<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Integration;

use ADS\TakeHome\Framework\MySQL;
use ADS\TakeHome\Weather\DateRange;
use ADS\TakeHome\Weather\MySqlWeatherRepository;
use ADS\TakeHome\Weather\Pagination;
use PDOException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * A valódi adatbázison futó teszt: a lapozás és a statisztika SQL-je a betöltött adatokkal.
 * Ha az adatbázis nem érhető el (pl. a konténeren kívül), a tesztek kimaradnak.
 */
#[Group('integration')]
final class MySqlWeatherRepositoryTest extends TestCase
{
    private MySqlWeatherRepository $repository;

    protected function setUp(): void
    {
        try {
            $this->repository = new MySqlWeatherRepository(new MySQL('db', 'weather', 'root', 'root'));
        } catch (PDOException $e) {
            $this->markTestSkipped('Az adatbázis nem érhető el: ' . $e->getMessage());
        }
    }

    #[Test]
    public function budapestDecemberStatistics(): void
    {
        $statistics = $this->repository->getDailyWeatherStatistics(1, DateRange::fromStrings('2023-12-01', '2023-12-31'));

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
        $statistics = $this->repository->getDailyWeatherStatistics(1, DateRange::fromStrings('2023-12-01', '2023-12-02'));

        $this->assertSame(2, $statistics->totalDays);
        $this->assertSame(0.35, $statistics->summary->minTemp->median);
        $this->assertSame(3.35, $statistics->summary->maxTemp->median);
    }

    #[Test]
    public function firstPageHasHundredDaysWithSqlComputedAverage(): void
    {
        $days = $this->repository->findDailyWeatherPage(1, $this->year2023(), new Pagination(1));

        $this->assertCount(100, $days);
        $this->assertSame('2023-01-01', $days[0]->date->format('Y-m-d'));
        $this->assertSame(3.4, $days[0]->minTemp);
        $this->assertSame(8.5, $days[0]->maxTemp);
        $this->assertSame(5.95, $days[0]->avgTemp);
    }

    #[Test]
    public function lastPageHoldsTheRemainingDays(): void
    {
        $days = $this->repository->findDailyWeatherPage(1, $this->year2023(), new Pagination(4));

        $this->assertCount(65, $days);
        $this->assertSame('2023-12-31', $days[64]->date->format('Y-m-d'));
    }

    #[Test]
    public function regionAverageSkipsCitiesWithMissingMeasurement(): void
    {
        $range = DateRange::fromStrings('1938-11-01', '1938-11-01');

        $days = $this->repository->findRegionDailyAveragesPage('Dunántúl', $range, new Pagination(1));
        $statistics = $this->repository->getRegionDailyAveragesStatistics('Dunántúl', $range);

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

        $days = $this->repository->findDailyWeatherPage(3, $range, new Pagination(1));

        $this->assertCount(1, $days);
        $this->assertNull($days[0]->precipitation);
    }

    #[Test]
    public function singleCityRegionStatisticsEqualTheCitys(): void
    {
        $range = $this->year2023();

        $this->assertEquals(
            $this->repository->getDailyWeatherStatistics(1, $range),
            $this->repository->getRegionDailyAveragesStatistics('Közép-Magyarország', $range),
        );
    }

    private function year2023(): DateRange
    {
        return DateRange::fromStrings('2023-01-01', '2023-12-31');
    }
}
