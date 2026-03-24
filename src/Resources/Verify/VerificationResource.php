<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Verify;

use Sabaab\Rapyd\Client\RapydClient;

final class VerificationResource
{
    public function __construct(
        private readonly RapydClient $client,
    ) {}

    public function createHostedPage(array $data): array
    {
        $response = $this->client->post('/v1/hosted/idv', $data);
        $response->throw();

        return $response->data() ?? [];
    }

    public function getApplicationStatus(string $applicationId): array
    {
        $response = $this->client->get("/v1/verify/applications/status/{$applicationId}");
        $response->throw();

        return $response->data() ?? [];
    }
}
