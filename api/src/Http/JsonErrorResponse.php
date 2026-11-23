<?php

declare(strict_types=1);

namespace ADS\TakeHome\Http;

use ADS\TakeHome\Framework\JsonHttpResponse;

/**
 * Hiba válasz `{"error": "..."}` törzzsel, a Router saját hibáival egyező alakban.
 */
final class JsonErrorResponse extends JsonHttpResponse
{
    public function __construct(string $message, int $status)
    {
        parent::__construct(['error' => $message], $status);
    }

    public static function badRequest(string $message): self
    {
        return new self($message, 400);
    }

    public static function notFound(string $message): self
    {
        return new self($message, 404);
    }
}
