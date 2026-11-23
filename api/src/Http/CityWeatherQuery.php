<?php

declare(strict_types=1);

namespace ADS\TakeHome\Http;

use ADS\TakeHome\Framework\HttpRequest;
use ADS\TakeHome\Validation\ValidationException;
use ADS\TakeHome\Weather\DateRange;
use ADS\TakeHome\Weather\Pagination;

/**
 * A `GET /weather/city` érvényes, feldolgozott kérése.
 */
final readonly class CityWeatherQuery
{
    private function __construct(
        public int $cityId,
        public DateRange $range,
        public Pagination $pagination,
    ) {
    }

    /**
     * @throws ValidationException Ha bármelyik paraméter hiányzik vagy érvénytelen.
     */
    public static function fromRequest(HttpRequest $request): self
    {
        $parameters = QueryParameters::fromRequest($request);

        return new self(
            $parameters->requirePositiveInt('cityId'),
            DateRange::fromStrings(
                $parameters->requireString('from'),
                $parameters->requireString('to'),
            ),
            Pagination::fromRequestedPage($parameters->optionalPositiveInt('page', 1)),
        );
    }
}
