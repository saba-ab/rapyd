<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Wallet;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\WalletContact;

final class WalletContactResource
{
    public function __construct(
        private readonly RapydClient $client,
    ) {}

    public function create(string $walletId, array $data): WalletContact
    {
        return $this->client->post("/v1/user/{$walletId}/contacts", $data)->toDto(WalletContact::class);
    }

    public function get(string $walletId, string $contactId): WalletContact
    {
        return $this->client->get("/v1/user/{$walletId}/contacts/{$contactId}")->toDto(WalletContact::class);
    }

    public function update(string $walletId, string $contactId, array $data): WalletContact
    {
        return $this->client->put("/v1/user/{$walletId}/contacts/{$contactId}", $data)->toDto(WalletContact::class);
    }

    public function delete(string $walletId, string $contactId): WalletContact
    {
        return $this->client->delete("/v1/user/{$walletId}/contacts/{$contactId}")->toDto(WalletContact::class);
    }

    public function list(string $walletId, array $params = []): array
    {
        $response = $this->client->get("/v1/user/{$walletId}/contacts", $params);
        $response->throw();

        return $response->data() ?? [];
    }
}
