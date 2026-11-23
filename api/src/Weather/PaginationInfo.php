<?php

declare(strict_types=1);

namespace ADS\TakeHome\Weather;

use JsonSerializable;

/**
 * A lapozás állapota a válaszban: melyik oldal, mekkora oldalméret, hány elem és oldal van összesen.
 */
final readonly class PaginationInfo implements JsonSerializable
{
    public function __construct(
        public int $page,
        public int $pageSize,
        public int $totalItems,
    ) {
    }

    /** Az oldalak száma; üres sorozatnál 0. */
    public function totalPages(): int
    {
        return intdiv($this->totalItems + $this->pageSize - 1, $this->pageSize);
    }

    /** Igaz, ha a sorozat üres: ilyenkor nincs mit lekérdezni, egyik oldal sem létezik. */
    public function isEmpty(): bool
    {
        return $this->totalItems === 0;
    }

    /** Igaz, ha van adat, de a kért oldal az utolsó oldalon túl van (üres sorozatnál nincs "túl nagy" oldal). */
    public function isPageBeyondEnd(): bool
    {
        return $this->totalItems > 0 && $this->page > $this->totalPages();
    }

    /** @return array{page: int, pageSize: int, totalItems: int, totalPages: int} */
    public function jsonSerialize(): array
    {
        return [
            'page' => $this->page,
            'pageSize' => $this->pageSize,
            'totalItems' => $this->totalItems,
            'totalPages' => $this->totalPages(),
        ];
    }
}
