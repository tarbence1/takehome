<?php

declare(strict_types=1);

namespace ADS\TakeHome\Weather;

/**
 * Egy időszak statisztikája: a napok száma és az oszloponkénti medián/módusz.
 */
final readonly class WeatherStatistics
{
    public function __construct(
        public int $totalDays,
        public WeatherSummary $summary,
    ) {
    }
}
