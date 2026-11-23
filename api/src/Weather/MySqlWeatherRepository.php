<?php

declare(strict_types=1);

namespace ADS\TakeHome\Weather;

use ADS\TakeHome\Framework\MySQL;

/**
 * Az időjárási adatok olvasása a `weather` adatbázisból; a lapozás SQL-ben fut, a statisztika a
 * teljes tartomány napjaiból PHP-ben készül.
 */
final readonly class MySqlWeatherRepository implements WeatherRepositoryInterface
{
    public function __construct(private MySQL $db)
    {
    }

    public function findDailyWeatherPage(int $cityId, DateRange $range, Pagination $pagination): array
    {
        return $this->queryPage(
            $this->citySeriesSql(),
            [":cityId" => $cityId] + $this->rangeParams($range),
            $pagination,
        );
    }

    public function findRegionDailyAveragesPage(string $region, DateRange $range, Pagination $pagination): array
    {
        return $this->queryPage(
            $this->regionSeriesSql(),
            $this->rangeParams($range),
            $pagination,
            [":region" => $region],
        );
    }

    public function getDailyWeatherStatistics(int $cityId, DateRange $range): WeatherStatistics
    {
        return $this->queryStatistics(
            $this->citySeriesSql(),
            [":cityId" => $cityId] + $this->rangeParams($range),
        );
    }

    public function getRegionDailyAveragesStatistics(string $region, DateRange $range): WeatherStatistics
    {
        return $this->queryStatistics(
            $this->regionSeriesSql(),
            $this->rangeParams($range),
            [":region" => $region],
        );
    }

    /**
     * A város napi sorozata: `date, minTemp, maxTemp, avgTemp, precipitation`.
     * A három mérés külön táblában van; a maximum hőmérséklet adja az alapot,
     * a másik kettő LEFT JOIN, hogy a hiányzó sor ne dobja ki a napot.
     */
    private function citySeriesSql(): string
    {
        $minTemp = $this->measurement('mn.`min_temp`');
        $maxTemp = $this->measurement('mx.`max_temp`');

        return "SELECT
                mx.`date` AS `date`,
                {$minTemp} AS `minTemp`,
                {$maxTemp} AS `maxTemp`,
                {$this->dailyMean($minTemp, $maxTemp)} AS `avgTemp`,
                {$this->measurement('pr.`precipitation`')} AS `precipitation`
            FROM
                weather.maximum_temperatures AS mx
                LEFT JOIN weather.minimum_temperatures AS mn
                    ON mn.`city_id` = mx.`city_id` AND mn.`date` = mx.`date`
                LEFT JOIN weather.precipitation AS pr
                    ON pr.`city_id` = mx.`city_id` AND pr.`date` = mx.`date`
            WHERE
                mx.`city_id` = :cityId
                AND mx.`date` BETWEEN :startDate AND :endDate";
    }

    /**
     * A régió napi sorozata, ugyanazokkal az oszlopokkal, mint a városé. Városonként először
     * a mérés kerül DECIMAL-ra, és csak utána az AVG (pontos tizedes átlag, nincs float zaj);
     * az átlaghőmérséklet az már kerekített régiós min és max átlaga. A hiányzó mérés NULL,
     * ezért az AVG kihagyja: a nap átlagát csak azok a városok adják, ahol van adat; ha
     * egyiknél sincs, az eredmény NULL.
     */
    private function regionSeriesSql(): string
    {
        $minTemp = $this->regionAverage($this->measurement('mn.`min_temp`'));
        $maxTemp = $this->regionAverage($this->measurement('mx.`max_temp`'));

        return "SELECT
                mx.`date` AS `date`,
                {$minTemp} AS `minTemp`,
                {$maxTemp} AS `maxTemp`,
                {$this->dailyMean($minTemp, $maxTemp)} AS `avgTemp`,
                {$this->regionAverage($this->measurement('pr.`precipitation`'))} AS `precipitation`
            FROM
                weather.maximum_temperatures AS mx
                JOIN weather.cities AS c
                    ON c.`id` = mx.`city_id`
                LEFT JOIN weather.minimum_temperatures AS mn
                    ON mn.`city_id` = mx.`city_id` AND mn.`date` = mx.`date`
                LEFT JOIN weather.precipitation AS pr
                    ON pr.`city_id` = mx.`city_id` AND pr.`date` = mx.`date`
            WHERE
                c.`region` = :region
                AND mx.`date` BETWEEN :startDate AND :endDate
            GROUP BY
                mx.`date`";
    }

    /** Egy mérés oszlopa: a hiányzó-érték jelölő NULL, a többi PRECISION tizedesjegyű DECIMAL. */
    private function measurement(string $column): string
    {
        return $this->decimal("NULLIF({$column}, :missingValue)");
    }

    /** A napi átlaghőmérséklet a (már kerekített) min és max átlaga; NULL, ha bármelyik hiányzik. */
    private function dailyMean(string $minTemp, string $maxTemp): string
    {
        return $this->decimal("({$minTemp} + {$maxTemp}) / 2");
    }

    private function regionAverage(string $measurement): string
    {
        return $this->decimal("AVG({$measurement})");
    }

    private function decimal(string $expression): string
    {
        return sprintf('CAST(%s AS DECIMAL(10, %d))', $expression, WeatherRowMapper::PRECISION);
    }

    /**
     * @param array<string, scalar|null> $params
     * @param array<string, scalar|null> $userParams Felhasználói szöveg (pl. `:region`). A queryAssoc
     * a paramétereket sorban, szövegcserével helyettesíti be, ezért ezek kerülnek utolsónak: így a
     * bennük lévő esetleges ":startDate" jellegű részletek már nem cserélődnek le.
     *
     * @return list<DailyWeather>
     */
    private function queryPage(string $seriesSql, array $params, Pagination $pagination, array $userParams = []): array
    {
        $rows = $this->db->queryAssoc(
            "{$seriesSql}
            ORDER BY
                mx.`date`
            LIMIT :pageLimit OFFSET :pageOffset",
            $params + [
                ":pageLimit" => Pagination::PAGE_SIZE,
                ":pageOffset" => $pagination->offset(),
            ] + $userParams,
        );

        return array_map(WeatherRowMapper::toDailyWeather(...), $rows);
    }

    /**
     * A teljes tartomány napjai egy lekérdezéssel; a medián és a módusz PHP-ben számolódik.
     *
     * @param array<string, scalar|null> $params
     * @param array<string, scalar|null> $userParams Felhasználói szöveg (pl. `:region`).
     */
    private function queryStatistics(string $seriesSql, array $params, array $userParams = []): WeatherStatistics
    {
        $days = array_map(
            WeatherRowMapper::toDailyWeather(...),
            $this->db->queryAssoc($seriesSql, $params + $userParams),
        );

        return new WeatherStatistics(count($days), WeatherSummary::fromDays($days));
    }

    /**
     * @return array<string, scalar|null>
     */
    private function rangeParams(DateRange $range): array
    {
        return [
            ":startDate" => WeatherRowMapper::toDbDate($range->from),
            ":endDate" => WeatherRowMapper::toDbDate($range->to),
            ":missingValue" => WeatherRowMapper::MISSING_VALUE,
        ];
    }
}
