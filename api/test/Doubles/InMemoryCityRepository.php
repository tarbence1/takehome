<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Doubles;

use ADS\TakeHome\Cities\City;
use ADS\TakeHome\Cities\CityRepositoryInterface;

final readonly class InMemoryCityRepository implements CityRepositoryInterface
{
    /**
     * @param list<City> $cities
     */
    public function __construct(private array $cities = [])
    {
    }

    public function findCity(int $cityId): ?City
    {
        foreach ($this->cities as $city) {
            if ($city->id === $cityId) {
                return $city;
            }
        }
        return null;
    }

    public function findCitiesInRegion(string $region): array
    {
        return $this->sortedByName(array_filter(
            $this->cities,
            static fn (City $city): bool => $city->region === $region,
        ));
    }

    public function findAllCities(): array
    {
        return $this->sortedByName($this->cities);
    }

    /**
     * @param array<City> $cities
     * @return list<City>
     */
    private function sortedByName(array $cities): array
    {
        $sorted = array_values($cities);
        usort($sorted, static fn (City $a, City $b): int => [$a->name, $a->id] <=> [$b->name, $b->id]);
        return $sorted;
    }
}
