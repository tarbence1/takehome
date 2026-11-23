<?php

declare(strict_types=1);

namespace ADS\TakeHome\Cities;

use JsonSerializable;

final readonly class City implements JsonSerializable
{
    public function __construct(
        public int $id,
        public string $name,
        public string $region,
    ) {
    }

    /** @return array{id: int, name: string, region: string} */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'region' => $this->region,
        ];
    }
}
