<?php

declare(strict_types=1);

namespace ADS\TakeHome\Weather;

use DateTimeImmutable;
use UnexpectedValueException;

/**
 * Az adatbázis sorok és a domain objektumok közötti (tiszta) leképezés.
 */
final class WeatherRowMapper
{
    /** A hiányzó mérést jelölő érték az adatbázisban; a lekérdezések `NULL`-ra cserélik. */
    public const MISSING_VALUE = -999.0;

    private const DB_DATE_FORMAT = 'Ymd';

    /**
     * A visszaadott értékek pontossága (tizedesjegyek): a lekérdezések a méréseket
     * `DECIMAL(10, PRECISION)` típusra alakítják, így a float zaj (pl. -0.4000000059604645)
     * sem kerül ki, az átlagok pedig két tizedesre kerekítve jelennek meg.
     */
    public const PRECISION = 2;

    /**
     * @param array<string, scalar|null> $row Kulcsok: date (`YYYYMMDD`), minTemp, maxTemp, avgTemp, precipitation.
     */
    public static function toDailyWeather(array $row): DailyWeather
    {
        $rawDate = (string) $row['date'];
        $date = DateTimeImmutable::createFromFormat('!' . self::DB_DATE_FORMAT, $rawDate);
        if ($date === false || $date->format(self::DB_DATE_FORMAT) !== $rawDate) {
            throw new UnexpectedValueException('Érvénytelen dátum az adatbázis sorban: ' . var_export($row['date'], true));
        }

        return new DailyWeather(
            $date,
            self::toMeasurement($row['minTemp']),
            self::toMeasurement($row['maxTemp']),
            self::toMeasurement($row['avgTemp']),
            self::toMeasurement($row['precipitation']),
        );
    }

    /** A dátum `YYYYMMDD` egész alakja, ahogy az adatbázis tárolja. */
    public static function toDbDate(DateTimeImmutable $date): int
    {
        return (int) $date->format(self::DB_DATE_FORMAT);
    }

    /** SQL `NULL` (hiányzó mérés) `null`, a nem numerikus érték hiba; különben float. */
    private static function toMeasurement(string|int|float|bool|null $value): ?float
    {
        if ($value === null) {
            return null;
        }
        if (!is_numeric($value)) {
            throw new UnexpectedValueException('Nem numerikus mérés az adatbázis sorban: ' . var_export($value, true));
        }
        return (float) $value;
    }
}
