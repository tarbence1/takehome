<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Weather;

use ADS\TakeHome\Weather\WeatherRowMapper;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class WeatherRowMapperTest extends TestCase
{
    #[Test]
    public function mapsRow(): void
    {
        $day = WeatherRowMapper::toDailyWeather([
            'date' => 20230115,
            'minTemp' => '-3.20',
            'maxTemp' => 4.0,
            'avgTemp' => '0.40',
            'precipitation' => '0.00',
        ]);

        $this->assertSame('2023-01-15', $day->date->format('Y-m-d'));
        $this->assertSame(-3.2, $day->minTemp);
        $this->assertSame(4.0, $day->maxTemp);
        $this->assertSame(0.4, $day->avgTemp);
        $this->assertSame(0.0, $day->precipitation);
    }

    #[Test]
    public function acceptsStringDate(): void
    {
        $day = WeatherRowMapper::toDailyWeather([
            'date' => '19010102',
            'minTemp' => 1.0,
            'maxTemp' => 2.0,
            'avgTemp' => 1.5,
            'precipitation' => 3.0,
        ]);

        $this->assertSame('1901-01-02', $day->date->format('Y-m-d'));
    }

    #[Test]
    public function mapsSqlNullToNullPerField(): void
    {
        $day = WeatherRowMapper::toDailyWeather([
            'date' => 20230115,
            'minTemp' => null,
            'maxTemp' => 2.0,
            'avgTemp' => null,
            'precipitation' => null,
        ]);

        $this->assertNull($day->minTemp);
        $this->assertSame(2.0, $day->maxTemp);
        $this->assertNull($day->avgTemp);
        $this->assertNull($day->precipitation);
    }

    #[Test]
    public function toDbDateFormatsAsInteger(): void
    {
        $this->assertSame(20230105, WeatherRowMapper::toDbDate(new DateTimeImmutable('2023-01-05')));
    }

    #[Test]
    public function rejectsNonExistentDate(): void
    {
        $this->expectException(UnexpectedValueException::class);

        WeatherRowMapper::toDailyWeather(['date' => 20230230, 'minTemp' => 1.0, 'maxTemp' => 2.0, 'avgTemp' => 1.5, 'precipitation' => 0.0]);
    }

    #[Test]
    public function rejectsNonNumericMeasurement(): void
    {
        $this->expectException(UnexpectedValueException::class);

        WeatherRowMapper::toDailyWeather(['date' => 20230115, 'minTemp' => 'abc', 'maxTemp' => 2.0, 'avgTemp' => 1.5, 'precipitation' => 0.0]);
    }
}
