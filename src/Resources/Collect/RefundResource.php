<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Collect;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Refund;
use Sabaab\Rapyd\Resources\Concerns\HasCrud;

final class RefundResource
{
    use HasCrud;

    public function __construct(
        private readonly RapydClient $client,
    ) {}

    protected function basePath(): string
    {
        return '/v1/refunds';
    }

    protected function dtoClass(): string
    {
        return Refund::class;
    }

    public function listByPayment(string $paymentId, array $params = []): array
    {
        $response = $this->client->get("/v1/payments/{$paymentId}/refunds", $params);
        $response->throw();

        return $response->data() ?? [];
    }
}
