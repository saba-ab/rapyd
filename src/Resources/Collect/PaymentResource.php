<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Collect;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Payment;
use Sabaab\Rapyd\Resources\Concerns\HasCrud;

final class PaymentResource
{
    use HasCrud;

    public function __construct(
        private readonly RapydClient $client,
    ) {}

    protected function basePath(): string
    {
        return '/v1/payments';
    }

    protected function dtoClass(): string
    {
        return Payment::class;
    }

    public function cancel(string $id): Payment
    {
        return $this->client->delete("{$this->basePath()}/{$id}")->toDto(Payment::class);
    }

    public function capture(string $id, ?float $amount = null): Payment
    {
        $body = $amount !== null ? ['amount' => $amount] : [];

        return $this->client->post("{$this->basePath()}/{$id}/capture", $body)->toDto(Payment::class);
    }
}
