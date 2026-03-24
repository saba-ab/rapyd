<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Exceptions;

final class ValidationException extends RapydException
{
    public function __construct(
        string $message,
        public readonly array $fields = [],
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
