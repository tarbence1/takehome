<?php

declare(strict_types=1);

namespace ADS\TakeHome\Weather;

use ADS\TakeHome\Cities\City;
use JsonSerializable;

/**
 * A `GET /weather/city` válasz tartalma.
 */
final readonly class CityWeatherReport implements JsonSerializable
{
    public function __construct(
        public City $city,
        public WeatherSeries $series,
    ) {
    }

    /**
     * @return array{
     *     city: City,
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
        return ['city' => $this->city] + $this->series->jsonSerialize();
    }
}
