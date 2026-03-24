<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Wallet;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\WalletTransaction;

final class WalletTransactionResource
{
    public function __construct(
        private readonly RapydClient $client,
    ) {}

    public function list(string $walletId, array $params = []): array
    {
        $response = $this->client->get("/v1/user/{$walletId}/transactions", $params);
        $response->throw();

        return $response->data() ?? [];
    }

    public function get(string $walletId, string $transactionId): WalletTransaction
    {
        return $this->client->get("/v1/user/{$walletId}/transactions/{$transactionId}")->toDto(WalletTransaction::class);
    }
}
