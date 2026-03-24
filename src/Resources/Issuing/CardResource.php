<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Issuing;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Card;
use Sabaab\Rapyd\Resources\Concerns\HasCrud;

final class CardResource
{
    use HasCrud;

    public function __construct(
        private readonly RapydClient $client,
    ) {}

    protected function basePath(): string
    {
        return '/v1/issuing/cards';
    }

    protected function dtoClass(): string
    {
        return Card::class;
    }

    public function updateStatus(array $data): Card
    {
        return $this->client->post('/v1/issuing/cards/status', $data)->toDto(Card::class);
    }

    public function activate(array $data): Card
    {
        return $this->client->post('/v1/issuing/cards/activate', $data)->toDto(Card::class);
    }

    public function listTransactions(string $cardId, array $params = []): array
    {
        $response = $this->client->get("/v1/issuing/cards/{$cardId}/transactions", $params);
        $response->throw();

        return $response->data() ?? [];
    }

    public function setPin(array $data): array
    {
        $response = $this->client->post('/v1/issuing/cards/pin/set', $data);
        $response->throw();

        return $response->data() ?? [];
    }

    public function getPin(array $params): array
    {
        $response = $this->client->get('/v1/issuing/cards/pin/get', $params);
        $response->throw();

        return $response->data() ?? [];
    }
}
