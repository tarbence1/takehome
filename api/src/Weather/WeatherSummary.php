<?php

declare(strict_types=1);

namespace ADS\TakeHome\Weather;

use JsonSerializable;

/**
 * Egy időszak oszloponkénti statisztikája (medián és módusz).
 */
final readonly class WeatherSummary implements JsonSerializable
{
    public function __construct(
        public ColumnSummary $minTemp,
        public ColumnSummary $maxTemp,
        public ColumnSummary $avgTemp,
        public ColumnSummary $precipitation,
    ) {
    }

    /**
     * Oszloponként a napok méréseiből számolt statisztika; a hiányzó (`null`) mérés kimarad.
     *
     * @param list<DailyWeather> $days
     */
    public static function fromDays(array $days): self
    {
        $column = static fn (string $property): ColumnSummary => ColumnSummary::fromValues(
            array_values(array_filter(
                array_column($days, $property),
                static fn (?float $value): bool => $value !== null,
            )),
        );

        return new self(
            $column('minTemp'),
            $column('maxTemp'),
            $column('avgTemp'),
            $column('precipitation'),
        );
    }

    /** @return array{minTemp: ColumnSummary, maxTemp: ColumnSummary, avgTemp: ColumnSummary, precipitation: ColumnSummary} */
    public function jsonSerialize(): array
    {
        return [
            'minTemp' => $this->minTemp,
            'maxTemp' => $this->maxTemp,
            'avgTemp' => $this->avgTemp,
            'precipitation' => $this->precipitation,
        ];
    }
}
