<?php

declare(strict_types=1);

namespace ADS\TakeHome\Weather;

use ADS\TakeHome\Validation\ValidationException;
use DateTimeImmutable;

/**
 * Érvényes, zárt naptári dátumtartomány (mindkét határ benne van).
 */
final readonly class DateRange
{
    /** A dátumok szöveges alakja (`YYYY-MM-DD`), a JSON válaszban is. */
    public const FORMAT = 'Y-m-d';

    private function __construct(
        public DateTimeImmutable $from,
        public DateTimeImmutable $to,
    ) {
    }

    /**
     * @param string $from Kezdőnap, `YYYY-MM-DD`.
     * @param string $to Záró nap, `YYYY-MM-DD`.
     *
     * @throws ValidationException Ha valamelyik dátum érvénytelen, vagy a sorrend fordított.
     */
    public static function fromStrings(string $from, string $to): self
    {
        $fromDate = self::parseDate('from', $from);
        $toDate = self::parseDate('to', $to);

        if ($fromDate > $toDate) {
            throw new ValidationException("'from' nem lehet későbbi, mint 'to'");
        }

        return new self($fromDate, $toDate);
    }

    /**
     * Szigorú `YYYY-MM-DD` feldolgozás: a nem létező napokat (pl. 2023-02-30)
     * a createFromFormat csendben átbillenti, ezért vissza kell ellenőrizni.
     */
    private static function parseDate(string $name, string $value): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!' . self::FORMAT, $value);
        if ($date === false || $date->format(self::FORMAT) !== $value) {
            throw new ValidationException("'{$name}' érvényes dátum kell legyen (ÉÉÉÉ-HH-NN)");
        }
        return $date;
    }
}
