<?php

declare(strict_types=1);

namespace ADS\TakeHome\Cities;

interface CityRepositoryInterface
{
    /**
     * @return City|null `null`, ha nincs ilyen azonosítójú város.
     */
    public function findCity(int $cityId): ?City;

    /**
     * A régió városai név, majd azonosító szerint rendezve. Ismeretlen régióra üres.
     *
     * @return list<City>
     */
    public function findCitiesInRegion(string $region): array;

    /**
     * Az összes város név, majd azonosító szerint rendezve.
     *
     * @return list<City>
     */
    public function findAllCities(): array;
}
