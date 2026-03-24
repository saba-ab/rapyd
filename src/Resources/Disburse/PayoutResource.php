<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Disburse;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Payout;
use Sabaab\Rapyd\Resources\Concerns\HasCrud;

final class PayoutResource
{
    use HasCrud;

    public function __construct(
        private readonly RapydClient $client,
    ) {}

    protected function basePath(): string
    {
        return '/v1/payouts';
    }

    protected function dtoClass(): string
    {
        return Payout::class;
    }

    public function cancel(string $id): Payout
    {
        return $this->client->delete("{$this->basePath()}/{$id}")->toDto(Payout::class);
    }

    public function confirm(string $id): Payout
    {
        return $this->client->post("/v1/payouts/confirm/{$id}")->toDto(Payout::class);
    }

    public function complete(string $id, float $amount): Payout
    {
        return $this->client->post("/v1/payouts/complete/{$id}/{$amount}")->toDto(Payout::class);
    }

    public function setResponse(string $id, array $data): Payout
    {
        return $this->client->post("/v1/payouts/{$id}/beneficiary/response", $data)->toDto(Payout::class);
    }
}
