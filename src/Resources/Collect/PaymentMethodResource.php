<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Collect;

use Sabaab\Rapyd\Client\RapydClient;

final class PaymentMethodResource
{
    public function __construct(
        private readonly RapydClient $client,
    ) {}

    public function listByCountry(string $countryCode, array $params = []): array
    {
        $query = array_merge(['country' => $countryCode], $params);
        $response = $this->client->get('/v1/payment_methods/country', $query);
        $response->throw();

        return $response->data() ?? [];
    }

    public function requiredFields(string $type): array
    {
        $response = $this->client->get("/v1/payment_methods/{$type}/required_fields");
        $response->throw();

        return $response->data() ?? [];
    }
}
