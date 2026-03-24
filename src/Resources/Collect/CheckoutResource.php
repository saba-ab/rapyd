<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Collect;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Checkout;

final class CheckoutResource
{
    public function __construct(
        private readonly RapydClient $client,
    ) {}

    public function create(array $data): Checkout
    {
        return $this->client->post('/v1/checkout', $data)->toDto(Checkout::class);
    }

    public function get(string $id): Checkout
    {
        return $this->client->get("/v1/checkout/{$id}")->toDto(Checkout::class);
    }
}
