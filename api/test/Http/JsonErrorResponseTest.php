<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Http;

use ADS\TakeHome\Framework\JsonHttpResponse;
use ADS\TakeHome\Http\JsonErrorResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JsonErrorResponseTest extends TestCase
{
    #[Test]
    public function badRequestHasStatus400AndErrorBody(): void
    {
        $response = JsonErrorResponse::badRequest('hibás kérés');

        $this->assertSame(400, $response->getStatus());
        $this->assertSame('{"error":"hibás kérés"}', $response->getBody());
    }

    #[Test]
    public function notFoundHasStatus404AndErrorBody(): void
    {
        $response = JsonErrorResponse::notFound('nincs ilyen');

        $this->assertSame(404, $response->getStatus());
        $this->assertSame('{"error":"nincs ilyen"}', $response->getBody());
    }

    #[Test]
    public function isAJsonResponseSoTheRouterSendsJsonContentType(): void
    {
        $this->assertInstanceOf(JsonHttpResponse::class, JsonErrorResponse::notFound('x'));
    }
}
