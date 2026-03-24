<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Disburse;

use Sabaab\Rapyd\Client\RapydClient;

final class PayoutMethodResource
{
    public function __construct(
        private readonly RapydClient $client,
    ) {}

    public function list(array $params = []): array
    {
        $response = $this->client->get('/v1/payout_method_types', $params);
        $response->throw();

        return $response->data() ?? [];
    }

    public function requiredFields(string $type): array
    {
        $response = $this->client->get("/v1/payouts/required_fields/{$type}");
        $response->throw();

        return $response->data() ?? [];
    }
}
