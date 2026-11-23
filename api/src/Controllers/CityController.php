<?php

declare(strict_types=1);

namespace ADS\TakeHome\Controllers;

use ADS\TakeHome\Cities\CityRepositoryInterface;
use ADS\TakeHome\Framework\HttpRequest;
use ADS\TakeHome\Framework\HttpResponseInterface;
use ADS\TakeHome\Framework\JsonHttpResponse;

/**
 * A `GET /cities` végpont: a városok listája a kliens választóihoz.
 */
final readonly class CityController
{
    public function __construct(private CityRepositoryInterface $cities)
    {
    }

    public function listCities(HttpRequest $request): HttpResponseInterface
    {
        return new JsonHttpResponse($this->cities->findAllCities());
    }
}
