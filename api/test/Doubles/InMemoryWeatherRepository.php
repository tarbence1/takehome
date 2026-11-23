<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Doubles;

use ADS\TakeHome\Weather\ColumnSummary;
use ADS\TakeHome\Weather\DailyWeather;
use ADS\TakeHome\Weather\DateRange;
use ADS\TakeHome\Weather\Pagination;
use ADS\TakeHome\Weather\WeatherRepositoryInterface;
use ADS\TakeHome\Weather\WeatherStatistics;
use ADS\TakeHome\Weather\WeatherSummary;

/**
 * Memóriabeli időjárástároló teszteléshez; a napokat a tartományra szűri, dátum szerint rendezi és lapozza.
 * A statisztika medián/módusz része nem számolódik: a teszt által megadott összegzést adja vissza, a napszám a tartományból jön.
 */
final class InMemoryWeatherRepository implements WeatherRepositoryInterface
{
    private readonly WeatherSummary $summary;

    /** @var int A lapozott lekérdezések hívásainak száma (a teszt ellenőrzi). */
    public int $pageQueries = 0;

    /**
     * @param array<int, list<DailyWeather>> $days Napi mérések városazonosító szerint.
     * @param array<string, list<DailyWeather>> $regionAverages Előre kiszámolt napi átlagok régió szerint.
     * @param ?WeatherSummary $summary A statisztikák összegzése; alapértelmezés: minden oszlop üres.
     */
    public function __construct(
        private readonly array $days = [],
        private readonly array $regionAverages = [],
        ?WeatherSummary $summary = null,
    ) {
        $empty = new ColumnSummary(null, []);
        $this->summary = $summary ?? new WeatherSummary($empty, $empty, $empty, $empty);
    }

    public function findDailyWeatherPage(int $cityId, DateRange $range, Pagination $pagination): array
    {
        $this->pageQueries++;
        return self::page(self::inRange($this->days[$cityId] ?? [], $range), $pagination);
    }

    public function findRegionDailyAveragesPage(string $region, DateRange $range, Pagination $pagination): array
    {
        $this->pageQueries++;
        return self::page(self::inRange($this->regionAverages[$region] ?? [], $range), $pagination);
    }

    public function getDailyWeatherStatistics(int $cityId, DateRange $range): WeatherStatistics
    {
        return new WeatherStatistics(count(self::inRange($this->days[$cityId] ?? [], $range)), $this->summary);
    }

    public function getRegionDailyAveragesStatistics(string $region, DateRange $range): WeatherStatistics
    {
        return new WeatherStatistics(count(self::inRange($this->regionAverages[$region] ?? [], $range)), $this->summary);
    }

    /**
     * @param list<DailyWeather> $days
     * @return list<DailyWeather>
     */
    private static function inRange(array $days, DateRange $range): array
    {
        $inRange = array_values(array_filter(
            $days,
            static fn (DailyWeather $day): bool => $day->date >= $range->from && $day->date <= $range->to,
        ));
        usort($inRange, static fn (DailyWeather $a, DailyWeather $b): int => $a->date <=> $b->date);
        return $inRange;
    }

    /**
     * @param list<DailyWeather> $days
     * @return list<DailyWeather>
     */
    private static function page(array $days, Pagination $pagination): array
    {
        return array_slice($days, $pagination->offset(), Pagination::PAGE_SIZE);
    }
}
