<?php

declare(strict_types=1);

namespace ADS\TakeHome\Weather;

use ADS\TakeHome\Validation\ValidationException;
use InvalidArgumentException;

/**
 * A kért oldal (1-től számozva); az oldalméret rögzített.
 */
final readonly class Pagination
{
    /** Az egy oldalon visszaadott napok száma. */
    public const PAGE_SIZE = 100;

    public function __construct(public int $page)
    {
        if ($page < 1 || $page > self::maxPage()) {
            throw new InvalidArgumentException(
                'az oldalszám 1 és ' . self::maxPage() . " között lehet, kapott: {$page}",
            );
        }
    }

    /**
     * A kérésből érkező oldalszámból lapozás.
     *
     * @throws ValidationException Ha az oldalszám nem 1 és `maxPage()` közötti.
     */
    public static function fromRequestedPage(int $page): self
    {
        if ($page < 1 || $page > self::maxPage()) {
            throw new ValidationException("'page' query paraméter 1 és " . self::maxPage() . ' közötti egész kell legyen');
        }
        return new self($page);
    }

    /** A lapozás állapota egy $totalItems elemű sorozatnál. */
    public function describe(int $totalItems): PaginationInfo
    {
        return new PaginationInfo($this->page, self::PAGE_SIZE, $totalItems);
    }

    /** A legnagyobb oldalszám, amelynek `offset()`-je még belefér egy egészbe (nem csordul át lebegőpontosra). */
    public static function maxPage(): int
    {
        return intdiv(PHP_INT_MAX, self::PAGE_SIZE) + 1;
    }

    /** Az oldal első sorának helye a teljes sorozatban (0-tól). */
    public function offset(): int
    {
        return ($this->page - 1) * self::PAGE_SIZE;
    }
}
