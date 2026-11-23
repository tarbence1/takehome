<?php

declare(strict_types=1);

namespace ADS\TakeHome\Tests\Http;

use ADS\TakeHome\Framework\HttpRequest;
use ADS\TakeHome\Http\QueryParameters;
use ADS\TakeHome\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class QueryParametersTest extends TestCase
{
    /**
     * @param array<string, string|list<string>> $query
     */
    private function parameters(array $query): QueryParameters
    {
        return QueryParameters::fromRequest(new HttpRequest($query));
    }

    #[Test]
    public function requireStringReturnsValue(): void
    {
        $this->assertSame('2023-01-01', $this->parameters(['from' => '2023-01-01'])->requireString('from'));
    }

    /**
     * @return array<string, array{array<string, string|list<string>>}>
     */
    public static function invalidStrings(): array
    {
        return [
            'missing' => [[]],
            'empty' => [['from' => '']],
            'array' => [['from' => ['a']]],
        ];
    }

    /**
     * @param array<string, string|list<string>> $query
     */
    #[Test]
    #[DataProvider('invalidStrings')]
    public function requireStringRejectsMissingEmptyOrArray(array $query): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("'from'");
        $this->parameters($query)->requireString('from');
    }

    #[Test]
    public function requireStringReportsArrayValueDistinctly(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("'from' query paraméter egyetlen szöveges érték kell legyen");
        $this->parameters(['from' => ['a']])->requireString('from');
    }

    #[Test]
    public function requirePositiveIntParsesDigits(): void
    {
        $this->assertSame(42, $this->parameters(['cityId' => '42'])->requirePositiveInt('cityId'));
    }

    #[Test]
    public function optionalPositiveIntReturnsDefaultWhenMissing(): void
    {
        $this->assertSame(1, $this->parameters([])->optionalPositiveInt('page', 1));
    }

    #[Test]
    public function optionalPositiveIntParsesValue(): void
    {
        $this->assertSame(3, $this->parameters(['page' => '3'])->optionalPositiveInt('page', 1));
    }

    /**
     * @return array<string, array{array<string, string|list<string>>}>
     */
    public static function invalidOptionalPositiveInts(): array
    {
        return [
            'zero' => [['page' => '0']],
            'negative' => [['page' => '-1']],
            'not a number' => [['page' => 'abc']],
            'empty' => [['page' => '']],
            'array' => [['page' => ['1']]],
        ];
    }

    /**
     * @param array<string, string|list<string>> $query
     */
    #[Test]
    #[DataProvider('invalidOptionalPositiveInts')]
    public function optionalPositiveIntRejectsInvalidPresentValue(array $query): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("'page'");
        $this->parameters($query)->optionalPositiveInt('page', 1);
    }

    /**
     * @return array<string, array{array<string, string|list<string>>}>
     */
    public static function invalidPositiveInts(): array
    {
        return [
            'missing' => [[]],
            'zero' => [['cityId' => '0']],
            'negative' => [['cityId' => '-1']],
            'decimal' => [['cityId' => '1.5']],
            'trailing garbage' => [['cityId' => '1abc']],
            'leading space' => [['cityId' => ' 1']],
            'empty' => [['cityId' => '']],
            'array' => [['cityId' => ['1']]],
            'overflow' => [['cityId' => '99999999999999999999']],
        ];
    }

    /**
     * @param array<string, string|list<string>> $query
     */
    #[Test]
    #[DataProvider('invalidPositiveInts')]
    public function requirePositiveIntRejectsInvalid(array $query): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("'cityId'");
        $this->parameters($query)->requirePositiveInt('cityId');
    }
}
