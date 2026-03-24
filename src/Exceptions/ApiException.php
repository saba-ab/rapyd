<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Exceptions;

final class ApiException extends RapydException
{
    public function __construct(
        string $message,
        public readonly string $errorCode,
        public readonly string $operationId = '',
        public readonly ?string $responseCode = null,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
