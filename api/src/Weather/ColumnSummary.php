<?php

declare(strict_types=1);

namespace ADS\TakeHome\Weather;

use JsonSerializable;

/**
 * Egy mért oszlop statisztikája a teljes időszakra.
 */
final readonly class ColumnSummary implements JsonSerializable
{
    /**
     * @param list<float> $modes
     */
    public function __construct(
        public ?float $median,
        public array $modes,
    ) {
    }

    /**
     * A medián a sorba rendezett értékek középső eleme, páros elemszámnál a két középső átlaga
     * (`PRECISION` tizedesre kerekítve, hogy a float zaj ne kerüljön ki). A módusz a leggyakoribb
     * érték(ek) növekvő sorrendben, ha legalább kétszer előfordul(nak).
     *
     * @param list<float> $values
     */
    public static function fromValues(array $values): self
    {
        sort($values);
        return new self(self::median($values), self::modes($values));
    }

    /**
     * @param list<float> $sorted
     */
    private static function median(array $sorted): ?float
    {
        $count = count($sorted);
        if ($count === 0) {
            return null;
        }

        $middle = intdiv($count, 2);
        $median = $count % 2 === 1
            ? $sorted[$middle]
            : ($sorted[$middle - 1] + $sorted[$middle]) / 2;

        return round($median, WeatherRowMapper::PRECISION);
    }

    /**
     * Az `array_count_values` csak sztringet és egészet számol, ezért a float értékek szövegként
     * csoportosulnak; a rendezett bemenet miatt a kulcsok sorrendje is növekvő marad.
     *
     * @param list<float> $sorted
     * @return list<float>
     */
    private static function modes(array $sorted): array
    {
        $frequencies = array_count_values(array_map(strval(...), $sorted));
        $maxFrequency = $frequencies === [] ? 0 : max($frequencies);
        if ($maxFrequency < 2) {
            return [];
        }

        return array_map(floatval(...), array_keys($frequencies, $maxFrequency, true));
    }

    /** @return array{median: ?float, modes: list<float>} */
    public function jsonSerialize(): array
    {
        return [
            'median' => $this->median,
            'modes' => $this->modes,
        ];
    }
}
