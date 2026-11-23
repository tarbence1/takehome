<?php

declare(strict_types=1);

namespace ADS\TakeHome\Weather;

use DateTimeImmutable;
use JsonSerializable;

/**
 * Egy nap mérései; a hiányzó mérés `null`.
 */
final readonly class DailyWeather implements JsonSerializable
{
    public function __construct(
        public DateTimeImmutable $date,
        public ?float $minTemp,
        public ?float $maxTemp,
        public ?float $avgTemp,
        public ?float $precipitation,
    ) {
    }

    /** @return array{date: string, minTemp: ?float, maxTemp: ?float, avgTemp: ?float, precipitation: ?float} */
    public function jsonSerialize(): array
    {
        return [
            'date' => $this->date->format(DateRange::FORMAT),
            'minTemp' => $this->minTemp,
            'maxTemp' => $this->maxTemp,
            'avgTemp' => $this->avgTemp,
            'precipitation' => $this->precipitation,
        ];
    }
}
