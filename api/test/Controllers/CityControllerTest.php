<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Controllers;

use ADS\TakeHome\Cities\City;
use ADS\TakeHome\Controllers\CityController;
use ADS\TakeHome\Framework\HttpRequest;
use ADS\TakeHome\Tests\Doubles\InMemoryCityRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CityControllerTest extends TestCase
{
    #[Test]
    public function listsAllCities(): void
    {
        $controller = new CityController(new InMemoryCityRepository([
            new City(2, 'Szeged', 'Dél-Alföld'),
            new City(1, 'Budapest', 'Közép-Magyarország'),
        ]));

        $response = $controller->listCities(new HttpRequest([]));

        $this->assertSame(200, $response->getStatus());
        $this->assertSame(
            [
                ['id' => 1, 'name' => 'Budapest', 'region' => 'Közép-Magyarország'],
                ['id' => 2, 'name' => 'Szeged', 'region' => 'Dél-Alföld'],
            ],
            json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR),
        );
    }

    #[Test]
    public function returnsEmptyListWhenNoCities(): void
    {
        $response = (new CityController(new InMemoryCityRepository()))->listCities(new HttpRequest([]));

        $this->assertSame(200, $response->getStatus());
        $this->assertSame('[]', (string) $response->getBody());
    }
}
