<?php

declare(strict_types=1);

namespace ADS\TakeHome\Http;

use ADS\TakeHome\Framework\HttpRequest;
use ADS\TakeHome\Validation\ValidationException;

/**
 * Típusos, szigorú hozzáférés a kérés query paramétereihez.
 */
final readonly class QueryParameters
{
    /**
     * @param array<string, string|list<string>> $query
     */
    private function __construct(private array $query)
    {
    }

    public static function fromRequest(HttpRequest $request): self
    {
        return new self($request->query);
    }

    /**
     * Kötelező, nem üres szöveges paraméter.
     *
     * @throws ValidationException Ha hiányzik, üres vagy tömb.
     */
    public function requireString(string $name): string
    {
        $value = $this->query[$name] ?? null;
        if (is_array($value)) {
            throw new ValidationException("'{$name}' query paraméter egyetlen szöveges érték kell legyen");
        }
        if (!is_string($value) || $value === '') {
            throw new ValidationException("'{$name}' query paraméter kötelező");
        }
        return $value;
    }

    /**
     * Kötelező, szigorúan csak számjegyekből álló, pozitív egész paraméter.
     *
     * @throws ValidationException Ha hiányzik, nem tiszta egész, vagy nem pozitív.
     */
    public function requirePositiveInt(string $name): int
    {
        return $this->parsePositiveInt($name, $this->query[$name] ?? null);
    }

    /**
     * Opcionális pozitív egész paraméter: hiányzó érték esetén az alapértelmezés,
     * jelen lévő értékre ugyanazok a szabályok, mint a `requirePositiveInt`-nél.
     *
     * @throws ValidationException Ha jelen van, de nem tiszta egész, vagy nem pozitív.
     */
    public function optionalPositiveInt(string $name, int $default): int
    {
        if (!array_key_exists($name, $this->query)) {
            return $default;
        }
        return $this->parsePositiveInt($name, $this->query[$name]);
    }

    /**
     * @param string|list<string>|null $value
     */
    private function parsePositiveInt(string $name, string|array|null $value): int
    {
        if (!is_string($value) || !ctype_digit($value)) {
            throw $this->notPositiveInt($name);
        }

        $int = (int) $value;
        if ($int < 1 || (string) $int !== ltrim($value, '0')) {
            throw $this->notPositiveInt($name);
        }
        return $int;
    }

    private function notPositiveInt(string $name): ValidationException
    {
        return new ValidationException("'{$name}' query paraméter pozitív egész kell legyen");
    }
}
