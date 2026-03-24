<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Collect;

use Sabaab\Rapyd\Client\RapydClient;

final class EscrowResource
{
    public function __construct(
        private readonly RapydClient $client,
    ) {}

    public function release(string $escrowId, array $data = []): array
    {
        $response = $this->client->post("/v1/escrows/{$escrowId}/escrow_releases", $data);
        $response->throw();

        return $response->data() ?? [];
    }
}
