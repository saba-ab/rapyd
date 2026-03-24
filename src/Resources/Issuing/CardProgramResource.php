<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Issuing;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\CardProgram;

final class CardProgramResource
{
    public function __construct(
        private readonly RapydClient $client,
    ) {}

    public function create(array $data): CardProgram
    {
        return $this->client->post('/v1/issuing/card_programs', $data)->toDto(CardProgram::class);
    }

    public function get(string $id): CardProgram
    {
        return $this->client->get("/v1/issuing/card_programs/{$id}")->toDto(CardProgram::class);
    }

    public function list(array $params = []): array
    {
        $response = $this->client->get('/v1/issuing/card_programs', $params);
        $response->throw();
        $items = $response->data() ?? [];

        return array_map(fn (array $item) => CardProgram::fromArray($item), $items);
    }
}
