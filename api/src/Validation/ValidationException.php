<?php

declare(strict_types=1);

namespace ADS\TakeHome\Validation;

use RuntimeException;

/**
 * Hibás kérés bemenet. Az üzenet a kliensnek szánt, magyar nyelvű hibaszöveg.
 */
final class ValidationException extends RuntimeException
{
}
