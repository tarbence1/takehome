<?php

declare(strict_types=1);

namespace ADS\TakeHome\Weather;

use ADS\TakeHome\Cities\City;
use JsonSerializable;

/**
 * A `GET /weather/region` válasz tartalma.
 */
final readonly class RegionWeatherReport implements JsonSerializable
{
    /**
     * @param list<City> $cities
     */
    public function __construct(
        public string $region,
        public array $cities,
        public WeatherSeries $series,
    ) {
    }

    /**
     * @return array{
     *     region: string,
     *     cities: list<City>,
     *     from: string,
     *     to: string,
     *     units: array{temperature: string, precipitation: string},
     *     summary: WeatherSummary,
     *     pagination: PaginationInfo,
     *     days: list<DailyWeather>
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'region' => $this->region,
            'cities' => $this->cities,
        ] + $this->series->jsonSerialize();
    }
}
