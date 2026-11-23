<?php

declare(strict_types=1);

namespace ADS\TakeHome\Weather;

/**
 * Az időjárási adatok forrása; a tárolási módot elrejti a domain elől.
 */
interface WeatherRepositoryInterface
{
    /**
     * A város napi méréseinek egy oldala a zárt tartományban, dátum szerint növekvő sorrendben.
     * A tartományon túli oldal üres.
     *
     * @return list<DailyWeather>
     */
    public function findDailyWeatherPage(int $cityId, DateRange $range, Pagination $pagination): array;

    /**
     * A régió városai napi átlagának egy oldala a zárt tartományban, dátum szerint növekvő sorrendben.
     * A hiányzó mérés kimarad az átlagból; ha egy napon egyik városnak sincs adata egy mezőre, az `null`.
     * A tartományon túli oldal üres.
     *
     * @return list<DailyWeather>
     */
    public function findRegionDailyAveragesPage(string $region, DateRange $range, Pagination $pagination): array;

    /** A város teljes (nem csak az aktuális oldalnyi) tartományának napszáma, mediánja és módusza. */
    public function getDailyWeatherStatistics(int $cityId, DateRange $range): WeatherStatistics;

    /** A régió napi átlagainak teljes tartományra vonatkozó napszáma, mediánja és módusza. */
    public function getRegionDailyAveragesStatistics(string $region, DateRange $range): WeatherStatistics;
}
