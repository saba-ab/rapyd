<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Collect;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Dispute;

final class DisputeResource
{
    public function __construct(
        private readonly RapydClient $client,
    ) {}

    public function get(string $id): Dispute
    {
        return $this->client->get("/v1/payment_disputes/{$id}")->toDto(Dispute::class);
    }

    public function list(array $params = []): array
    {
        $response = $this->client->get('/v1/payment_disputes', $params);
        $response->throw();
        $items = $response->data() ?? [];

        return array_map(fn (array $item) => Dispute::fromArray($item), $items);
    }
}
