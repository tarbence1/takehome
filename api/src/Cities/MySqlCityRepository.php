<?php

declare(strict_types=1);

namespace ADS\TakeHome\Cities;

use ADS\TakeHome\Framework\MySQL;

final readonly class MySqlCityRepository implements CityRepositoryInterface
{
    public function __construct(private MySQL $db)
    {
    }

    public function findCity(int $cityId): ?City
    {
        $rows = $this->db->queryAssoc(
            "SELECT
                `id`,
                `name`,
                `region`
            FROM
                weather.cities
            WHERE
                `id` = :cityId
            ",
            [":cityId" => $cityId],
        );

        return $rows === [] ? null : $this->toCity($rows[0]);
    }

    public function findCitiesInRegion(string $region): array
    {
        $rows = $this->db->queryAssoc(
            "SELECT
                `id`,
                `name`,
                `region`
            FROM
                weather.cities
            WHERE
                `region` = :region
            ORDER BY
                `name`, `id`
            ",
            [":region" => $region],
        );

        return array_map($this->toCity(...), $rows);
    }

    public function findAllCities(): array
    {
        $rows = $this->db->queryAssoc(
            "SELECT
                `id`,
                `name`,
                `region`
            FROM
                weather.cities
            ORDER BY
                `name`, `id`
            ",
        );

        return array_map($this->toCity(...), $rows);
    }

    /**
     * @param array<string, scalar|null> $row
     */
    private function toCity(array $row): City
    {
        return new City((int) $row['id'], (string) $row['name'], (string) $row['region']);
    }
}
