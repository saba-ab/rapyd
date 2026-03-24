<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Verify;

use Sabaab\Rapyd\Client\RapydClient;

final class IdentityResource
{
    public function __construct(
        private readonly RapydClient $client,
    ) {}

    public function create(array $data): array
    {
        $response = $this->client->post('/v1/identities', $data);
        $response->throw();

        return $response->data() ?? [];
    }

    public function get(string $id): array
    {
        $response = $this->client->get("/v1/identities/{$id}");
        $response->throw();

        return $response->data() ?? [];
    }

    public function list(array $params = []): array
    {
        $response = $this->client->get('/v1/identities', $params);
        $response->throw();

        return $response->data() ?? [];
    }
}
