<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Wallet;

use Sabaab\Rapyd\Client\RapydClient;

final class WalletTransferResource
{
    public function __construct(
        private readonly RapydClient $client,
    ) {}

    public function create(array $data): array
    {
        $response = $this->client->post('/v1/account/transfer', $data);
        $response->throw();

        return $response->data() ?? [];
    }

    public function setResponse(array $data): array
    {
        $response = $this->client->put('/v1/account/transfer/response', $data);
        $response->throw();

        return $response->data() ?? [];
    }
}
