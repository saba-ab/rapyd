<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Client;

use Illuminate\Http\Client\Response;
use Sabaab\Rapyd\Exceptions\ApiException;
use Sabaab\Rapyd\Exceptions\AuthenticationException;
use Sabaab\Rapyd\Exceptions\RapydException;
use Sabaab\Rapyd\Exceptions\ValidationException;

final class RapydResponse
{
    private readonly array $body;

    public function __construct(Response $response)
    {
        $this->body = $response->json() ?? [];
    }

    public function successful(): bool
    {
        return ($this->body['status']['status'] ?? '') === 'SUCCESS';
    }

    public function failed(): bool
    {
        return ! $this->successful();
    }

    public function data(): ?array
    {
        return $this->body['data'] ?? null;
    }

    public function status(): array
    {
        return $this->body['status'] ?? [];
    }

    public function operationId(): string
    {
        return $this->body['status']['operation_id'] ?? '';
    }

    public function errorCode(): string
    {
        return $this->body['status']['error_code'] ?? '';
    }

    public function message(): string
    {
        return $this->body['status']['message'] ?? '';
    }

    public function toArray(): array
    {
        return $this->body;
    }

    public function toDto(string $dtoClass): mixed
    {
        $this->throwIfFailed();

        return $dtoClass::fromArray($this->data());
    }

    /**
     * @throws RapydException
     */
    public function throw(): self
    {
        $this->throwIfFailed();

        return $this;
    }

    private function throwIfFailed(): void
    {
        if ($this->successful()) {
            return;
        }

        $errorCode = $this->errorCode();
        $message = $this->message() ?: 'Unknown Rapyd API error';
        $operationId = $this->operationId();
        $responseCode = $this->body['status']['response_code'] ?? null;

        if (in_array($errorCode, ['UNAUTHENTICATED', 'UNAUTHORIZED'], true)) {
            throw new AuthenticationException($message);
        }

        if (str_starts_with($errorCode, 'INVALID_FIELDS')) {
            throw new ValidationException(
                message: $message,
                fields: $this->data() ?? [],
            );
        }

        throw new ApiException(
            message: $message,
            errorCode: $errorCode,
            operationId: $operationId,
            responseCode: $responseCode,
        );
    }
}
