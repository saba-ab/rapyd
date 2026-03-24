<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Protect;

use Sabaab\Rapyd\Client\RapydClient;

final class FraudResource
{
    public function __construct(
        private readonly RapydClient $client,
    ) {}

    public function getSettings(): array
    {
        $response = $this->client->get('/v1/fraud/merchant/settings');
        $response->throw();

        return $response->data() ?? [];
    }

    public function updateSettings(array $data): array
    {
        $response = $this->client->put('/v1/fraud/merchant/settings', $data);
        $response->throw();

        return $response->data() ?? [];
    }
}
