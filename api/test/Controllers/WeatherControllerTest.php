<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Controllers;

use ADS\TakeHome\Cities\City;
use ADS\TakeHome\Cities\CityRepositoryInterface;
use ADS\TakeHome\Controllers\WeatherController;
use ADS\TakeHome\Framework\HttpRequest;
use ADS\TakeHome\Framework\HttpResponseInterface;
use ADS\TakeHome\Tests\Doubles\InMemoryCityRepository;
use ADS\TakeHome\Tests\Doubles\InMemoryWeatherRepository;
use ADS\TakeHome\Weather\ColumnSummary;
use ADS\TakeHome\Weather\DailyWeather;
use ADS\TakeHome\Weather\Pagination;
use ADS\TakeHome\Weather\WeatherSummary;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WeatherControllerTest extends TestCase
{
    private WeatherController $controller;

    protected function setUp(): void
    {
        $this->controller = new WeatherController(
            new InMemoryCityRepository([
                new City(1, 'Budapest', 'Közép-Magyarország'),
                new City(3, 'Keszthely', 'Dunántúl'),
                new City(2, 'Győr', 'Dunántúl'),
            ]),
            new InMemoryWeatherRepository(
                [1 => [
                    new DailyWeather(new DateTimeImmutable('2023-01-01'), -1.0, 3.5, 1.25, 0.0),
                    new DailyWeather(new DateTimeImmutable('2023-01-02'), null, 4.0, null, 1.2),
                    new DailyWeather(new DateTimeImmutable('2023-01-03'), 0.0, 5.0, 2.5, null),
                ]],
                ['Dunántúl' => [
                    new DailyWeather(new DateTimeImmutable('2023-01-01'), 1.23, 6.5, 3.87, 0.4),
                    new DailyWeather(new DateTimeImmutable('2023-01-02'), null, 7.0, null, null),
                    new DailyWeather(new DateTimeImmutable('2023-02-01'), 0.0, 1.0, 0.5, 2.0),
                ]],
            ),
        );
    }

    #[Test]
    public function returnsCityWeather(): void
    {
        $response = $this->get(['cityId' => '1', 'from' => '2023-01-01', 'to' => '2023-01-02']);

        $body = $this->decode($response);
        $this->assertSame(200, $response->getStatus());
        $this->assertSame(['id' => 1, 'name' => 'Budapest', 'region' => 'Közép-Magyarország'], $body['city']);
        $this->assertSame('2023-01-01', $body['from']);
        $this->assertSame('2023-01-02', $body['to']);
        $this->assertSame(['temperature' => '°C', 'precipitation' => 'mm'], $body['units']);
        $this->assertSame(
            ['page' => 1, 'pageSize' => 100, 'totalItems' => 2, 'totalPages' => 1],
            $body['pagination'],
        );
        $this->assertCount(2, $body['days']);
        $this->assertSame(
            ['date' => '2023-01-01', 'minTemp' => -1, 'maxTemp' => 3.5, 'avgTemp' => 1.25, 'precipitation' => 0],
            $body['days'][0],
        );
        $this->assertSame(
            ['date' => '2023-01-02', 'minTemp' => null, 'maxTemp' => 4, 'avgTemp' => null, 'precipitation' => 1.2],
            $body['days'][1],
        );
    }

    #[Test]
    public function returnsEmptyDaysOutsideData(): void
    {
        $response = $this->get(['cityId' => '1', 'from' => '2030-01-01', 'to' => '2030-01-31']);

        $body = $this->decode($response);
        $this->assertSame(200, $response->getStatus());
        $this->assertSame([], $body['days']);
        $this->assertSame(
            ['page' => 1, 'pageSize' => 100, 'totalItems' => 0, 'totalPages' => 0],
            $body['pagination'],
        );
        $this->assertSame(['median' => null, 'modes' => []], $body['summary']['minTemp']);
    }

    #[Test]
    public function pageSliceAndSummaryComeFromTheWholePeriod(): void
    {
        $controller = $this->controllerWithLongSeries();
        $query = ['cityId' => '1', 'from' => '2023-01-01', 'to' => '2023-12-31'];

        $first = $this->decode($controller->getCityWeather(new HttpRequest($query)));
        $last = $this->decode($controller->getCityWeather(new HttpRequest($query + ['page' => '3'])));

        $this->assertSame(
            ['page' => 1, 'pageSize' => 100, 'totalItems' => 250, 'totalPages' => 3],
            $first['pagination'],
        );
        $this->assertCount(100, $first['days']);
        $this->assertSame('2023-01-01', $first['days'][0]['date']);
        $this->assertSame('2023-04-10', $first['days'][99]['date']);
        $this->assertCount(50, $last['days']);
        $this->assertSame('2023-07-20', $last['days'][0]['date']);
        $this->assertSame($first['summary'], $last['summary']);
    }

    #[Test]
    public function returnsBadRequestForPageBeyondLastPage(): void
    {
        $response = $this->controllerWithLongSeries()->getCityWeather(
            new HttpRequest(['cityId' => '1', 'from' => '2023-01-01', 'to' => '2023-12-31', 'page' => '4']),
        );

        $this->assertSame(400, $response->getStatus());
        $this->assertSame('a(z) 4. oldal nem létezik, összesen 3 oldal van', $this->decode($response)['error']);
    }

    #[Test]
    public function doesNotQueryThePageWhenItIsBeyondTheLastPage(): void
    {
        $weather = new InMemoryWeatherRepository(
            [1 => [new DailyWeather(new DateTimeImmutable('2023-01-01'), 0.0, 1.0, 0.5, null)]],
            ['Dunántúl' => [new DailyWeather(new DateTimeImmutable('2023-01-01'), 0.0, 1.0, 0.5, null)]],
        );
        $controller = new WeatherController(
            new InMemoryCityRepository([new City(1, 'Budapest', 'Dunántúl')]),
            $weather,
        );

        $city = $controller->getCityWeather(new HttpRequest(['cityId' => '1', 'from' => '2023-01-01', 'to' => '2023-01-31', 'page' => '2']));
        $region = $controller->getRegionWeather(new HttpRequest(['region' => 'Dunántúl', 'from' => '2023-01-01', 'to' => '2023-01-31', 'page' => '2']));

        $this->assertSame(400, $city->getStatus());
        $this->assertSame(400, $region->getStatus());
        $this->assertSame(0, $weather->pageQueries);
    }

    #[Test]
    public function hugePageOnEmptyPeriodIsOkWithoutPageQuery(): void
    {
        $weather = new InMemoryWeatherRepository();
        $controller = new WeatherController(
            new InMemoryCityRepository([new City(1, 'Budapest', 'Közép-Magyarország')]),
            $weather,
        );

        $response = $controller->getCityWeather(new HttpRequest(
            ['cityId' => '1', 'from' => '2030-01-01', 'to' => '2030-01-31', 'page' => (string) Pagination::maxPage()],
        ));

        $this->assertSame(200, $response->getStatus());
        $this->assertSame([], $this->decode($response)['days']);
        $this->assertSame(0, $weather->pageQueries);
    }

    #[Test]
    public function hugePageOnNonEmptyPeriodIsBadRequest(): void
    {
        $response = $this->controllerWithLongSeries()->getCityWeather(new HttpRequest(
            ['cityId' => '1', 'from' => '2023-01-01', 'to' => '2023-12-31', 'page' => (string) Pagination::maxPage()],
        ));

        $this->assertSame(400, $response->getStatus());
    }

    #[Test]
    public function pageBeyondEmptyPeriodIsNotAnError(): void
    {
        $response = $this->get(['cityId' => '1', 'from' => '2030-01-01', 'to' => '2030-01-31', 'page' => '2']);

        $this->assertSame(200, $response->getStatus());
        $this->assertSame([], $this->decode($response)['days']);
    }

    #[Test]
    public function returnsBadRequestForRegionPageBeyondLastPage(): void
    {
        $response = $this->getRegion(['region' => 'Dunántúl', 'from' => '2023-01-01', 'to' => '2023-12-31', 'page' => '2']);

        $this->assertSame(400, $response->getStatus());
        $this->assertSame('a(z) 2. oldal nem létezik, összesen 1 oldal van', $this->decode($response)['error']);
    }

    /**
     * @param array<string, string|list<string>> $query
     */
    #[Test]
    #[DataProvider('invalidQueries')]
    public function returnsBadRequestOnInvalidInput(array $query): void
    {
        $response = $this->get($query);

        $this->assertSame(400, $response->getStatus());
        $error = $this->decode($response)['error'] ?? null;
        $this->assertIsString($error);
        $this->assertNotSame('', $error);
    }

    /**
     * @return array<string, array{array<string, string|list<string>>}>
     */
    public static function invalidQueries(): array
    {
        return [
            'missing cityId' => [['from' => '2023-01-01', 'to' => '2023-01-02']],
            'non-numeric cityId' => [['cityId' => 'abc', 'from' => '2023-01-01', 'to' => '2023-01-02']],
            'array cityId' => [['cityId' => ['1'], 'from' => '2023-01-01', 'to' => '2023-01-02']],
            'missing from' => [['cityId' => '1', 'to' => '2023-01-02']],
            'missing to' => [['cityId' => '1', 'from' => '2023-01-01']],
            'impossible date' => [['cityId' => '1', 'from' => '2023-02-30', 'to' => '2023-03-01']],
            'from after to' => [['cityId' => '1', 'from' => '2023-01-02', 'to' => '2023-01-01']],
            'non-numeric page' => [['cityId' => '1', 'from' => '2023-01-01', 'to' => '2023-01-02', 'page' => 'abc']],
            'zero page' => [['cityId' => '1', 'from' => '2023-01-01', 'to' => '2023-01-02', 'page' => '0']],
            'page beyond int range' => [['cityId' => '1', 'from' => '2023-01-01', 'to' => '2023-01-02', 'page' => (string) PHP_INT_MAX]],
        ];
    }

    #[Test]
    public function returnsNotFoundForUnknownCity(): void
    {
        $response = $this->get(['cityId' => '99', 'from' => '2023-01-01', 'to' => '2023-01-02']);

        $this->assertSame(404, $response->getStatus());
        $this->assertSame(
            'a(z) 99 azonosítójú város nem található',
            $this->decode($response)['error'],
        );
    }

    #[Test]
    public function returnsRegionAverages(): void
    {
        $response = $this->getRegion(['region' => 'Dunántúl', 'from' => '2023-01-01', 'to' => '2023-01-31']);

        $body = $this->decode($response);
        $this->assertSame(200, $response->getStatus());
        $this->assertSame('Dunántúl', $body['region']);
        $this->assertSame(
            [
                ['id' => 2, 'name' => 'Győr', 'region' => 'Dunántúl'],
                ['id' => 3, 'name' => 'Keszthely', 'region' => 'Dunántúl'],
            ],
            $body['cities'],
        );
        $this->assertSame('2023-01-01', $body['from']);
        $this->assertSame('2023-01-31', $body['to']);
        $this->assertSame(['temperature' => '°C', 'precipitation' => 'mm'], $body['units']);
        $this->assertSame(
            ['page' => 1, 'pageSize' => 100, 'totalItems' => 2, 'totalPages' => 1],
            $body['pagination'],
        );
        $this->assertCount(2, $body['days']);
        $this->assertSame(
            ['date' => '2023-01-01', 'minTemp' => 1.23, 'maxTemp' => 6.5, 'avgTemp' => 3.87, 'precipitation' => 0.4],
            $body['days'][0],
        );
        $this->assertSame(
            ['date' => '2023-01-02', 'minTemp' => null, 'maxTemp' => 7, 'avgTemp' => null, 'precipitation' => null],
            $body['days'][1],
        );
    }

    #[Test]
    public function returnsStoredRegionNameForDifferentSpelling(): void
    {
        $cities = new class () implements CityRepositoryInterface {
            public function findCity(int $cityId): ?City
            {
                return null;
            }

            public function findCitiesInRegion(string $region): array
            {
                return $region === 'DUNÁNTÚL' ? [new City(3, 'Keszthely', 'Dunántúl')] : [];
            }

            public function findAllCities(): array
            {
                return [];
            }
        };
        $controller = new WeatherController($cities, new InMemoryWeatherRepository([], [
            'Dunántúl' => [new DailyWeather(new DateTimeImmutable('2023-01-01'), 1.0, 2.0, 1.5, 3.0)],
        ]));

        $response = $controller->getRegionWeather(
            new HttpRequest(['region' => 'DUNÁNTÚL', 'from' => '2023-01-01', 'to' => '2023-01-02']),
        );

        $this->assertSame(200, $response->getStatus());
        $body = $this->decode($response);
        $this->assertSame('Dunántúl', $body['region']);
        $this->assertSame(
            [['date' => '2023-01-01', 'minTemp' => 1, 'maxTemp' => 2, 'avgTemp' => 1.5, 'precipitation' => 3]],
            $body['days'],
        );
    }

    /**
     * @param array<string, string|list<string>> $query
     */
    #[Test]
    #[DataProvider('invalidRegionQueries')]
    public function returnsBadRequestOnInvalidRegionInput(array $query): void
    {
        $response = $this->getRegion($query);

        $this->assertSame(400, $response->getStatus());
        $error = $this->decode($response)['error'] ?? null;
        $this->assertIsString($error);
        $this->assertNotSame('', $error);
    }

    /**
     * @return array<string, array{array<string, string|list<string>>}>
     */
    public static function invalidRegionQueries(): array
    {
        return [
            'missing region' => [['from' => '2023-01-01', 'to' => '2023-01-02']],
            'empty region' => [['region' => '', 'from' => '2023-01-01', 'to' => '2023-01-02']],
            'array region' => [['region' => ['Dunántúl'], 'from' => '2023-01-01', 'to' => '2023-01-02']],
            'impossible date' => [['region' => 'Dunántúl', 'from' => '2023-02-30', 'to' => '2023-03-01']],
            'non-numeric page' => [['region' => 'Dunántúl', 'from' => '2023-01-01', 'to' => '2023-01-02', 'page' => 'abc']],
            'zero page' => [['region' => 'Dunántúl', 'from' => '2023-01-01', 'to' => '2023-01-02', 'page' => '0']],
        ];
    }

    #[Test]
    public function returnsNotFoundForUnknownRegion(): void
    {
        $response = $this->getRegion(['region' => 'Atlantisz', 'from' => '2023-01-01', 'to' => '2023-01-02']);

        $this->assertSame(404, $response->getStatus());
        $this->assertSame("a(z) 'Atlantisz' régió nem található", $this->decode($response)['error']);
    }

    /**
     * @param array<string, string|list<string>> $query
     */
    private function getRegion(array $query): HttpResponseInterface
    {
        return $this->controller->getRegionWeather(new HttpRequest($query));
    }

    /**
     * @param array<string, string|list<string>> $query
     */
    private function get(array $query): HttpResponseInterface
    {
        return $this->controller->getCityWeather(new HttpRequest($query));
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(HttpResponseInterface $response): array
    {
        $decoded = json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($decoded);
        return $decoded;
    }

    private function controllerWithLongSeries(): WeatherController
    {
        $start = new DateTimeImmutable('2023-01-01');
        $days = array_map(
            static fn (int $i): DailyWeather => new DailyWeather($start->modify("+{$i} days"), 0.0, (float) $i, (float) $i / 2, null),
            range(0, 249),
        );

        return new WeatherController(
            new InMemoryCityRepository([new City(1, 'Budapest', 'Közép-Magyarország')]),
            new InMemoryWeatherRepository(
                [1 => $days],
                summary: new WeatherSummary(
                    new ColumnSummary(0.0, [0.0]),
                    new ColumnSummary(124.5, []),
                    new ColumnSummary(62.25, []),
                    new ColumnSummary(null, []),
                ),
            ),
        );
    }
}
