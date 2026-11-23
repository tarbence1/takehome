<?php

declare(strict_types=1);

namespace ADS\TakeHome\Controllers;

use ADS\TakeHome\Cities\CityRepositoryInterface;
use ADS\TakeHome\Framework\HttpRequest;
use ADS\TakeHome\Framework\HttpResponseInterface;
use ADS\TakeHome\Framework\JsonHttpResponse;
use ADS\TakeHome\Http\CityWeatherQuery;
use ADS\TakeHome\Http\JsonErrorResponse;
use ADS\TakeHome\Http\RegionWeatherQuery;
use ADS\TakeHome\Validation\ValidationException;
use ADS\TakeHome\Weather\CityWeatherReport;
use ADS\TakeHome\Weather\PaginationInfo;
use ADS\TakeHome\Weather\RegionWeatherReport;
use ADS\TakeHome\Weather\WeatherRepositoryInterface;
use ADS\TakeHome\Weather\WeatherSeries;

final readonly class WeatherController
{
    public function __construct(
        private CityRepositoryInterface $cities,
        private WeatherRepositoryInterface $weather,
    ) {
    }

    public function getCityWeather(HttpRequest $request): HttpResponseInterface
    {
        try {
            $query = CityWeatherQuery::fromRequest($request);
        } catch (ValidationException $e) {
            return JsonErrorResponse::badRequest($e->getMessage());
        }

        $city = $this->cities->findCity($query->cityId);
        if ($city === null) {
            return JsonErrorResponse::notFound("a(z) {$query->cityId} azonosítójú város nem található");
        }

        $statistics = $this->weather->getDailyWeatherStatistics($query->cityId, $query->range);
        $pagination = $query->pagination->describe($statistics->totalDays);
        if ($pagination->isPageBeyondEnd()) {
            return self::pageBeyondEnd($pagination);
        }

        return new JsonHttpResponse(new CityWeatherReport($city, new WeatherSeries(
            $query->range,
            $statistics->summary,
            $pagination,
            $pagination->isEmpty() ? [] : $this->weather->findDailyWeatherPage($query->cityId, $query->range, $query->pagination),
        )));
    }

    public function getRegionWeather(HttpRequest $request): HttpResponseInterface
    {
        try {
            $query = RegionWeatherQuery::fromRequest($request);
        } catch (ValidationException $e) {
            return JsonErrorResponse::badRequest($e->getMessage());
        }

        $cities = $this->cities->findCitiesInRegion($query->region);
        if ($cities === []) {
            return JsonErrorResponse::notFound("a(z) '{$query->region}' régió nem található");
        }

        $region = $cities[0]->region;

        $statistics = $this->weather->getRegionDailyAveragesStatistics($region, $query->range);
        $pagination = $query->pagination->describe($statistics->totalDays);
        if ($pagination->isPageBeyondEnd()) {
            return self::pageBeyondEnd($pagination);
        }

        return new JsonHttpResponse(new RegionWeatherReport($region, $cities, new WeatherSeries(
            $query->range,
            $statistics->summary,
            $pagination,
            $pagination->isEmpty() ? [] : $this->weather->findRegionDailyAveragesPage($region, $query->range, $query->pagination),
        )));
    }

    /** A statisztikából tudjuk, hogy a kért oldal nem létezik, ezért az oldal lekérdezése el sem indul. */
    private static function pageBeyondEnd(PaginationInfo $pagination): HttpResponseInterface
    {
        return JsonErrorResponse::badRequest(
            "a(z) {$pagination->page}. oldal nem létezik, összesen {$pagination->totalPages()} oldal van",
        );
    }
}
