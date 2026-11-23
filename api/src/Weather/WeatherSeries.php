<?php

declare(strict_types=1);

namespace ADS\TakeHome\Weather;

use JsonSerializable;

/**
 * Egy időszak egy oldalnyi napi mérése mértékegységekkel és a teljes időszak statisztikájával; a városi és a régiós válasz közös része.
 */
final readonly class WeatherSeries implements JsonSerializable
{
    /**
     * @param list<DailyWeather> $days
     */
    public function __construct(
        public DateRange $range,
        public WeatherSummary $summary,
        public PaginationInfo $pagination,
        public array $days,
    ) {
    }

    /**
     * @return array{
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
            'from' => $this->range->from->format(DateRange::FORMAT),
            'to' => $this->range->to->format(DateRange::FORMAT),
            'units' => [
                'temperature' => Units::TEMPERATURE,
                'precipitation' => Units::PRECIPITATION,
            ],
            'summary' => $this->summary,
            'pagination' => $this->pagination,
            'days' => $this->days,
        ];
    }
}
