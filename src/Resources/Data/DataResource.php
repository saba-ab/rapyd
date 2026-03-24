<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Data;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Country;
use Sabaab\Rapyd\DTOs\Currency;
use Sabaab\Rapyd\DTOs\FxRate;

final class DataResource
{
    public function __construct(
        private readonly RapydClient $client,
    ) {}

    /**
     * @return Country[]
     */
    public function countries(array $params = []): array
    {
        $response = $this->client->get('/v1/data/countries', $params);
        $response->throw();
        $items = $response->data() ?? [];

        return array_map(fn (array $item) => Country::fromArray($item), $items);
    }

    /**
     * @return Currency[]
     */
    public function currencies(array $params = []): array
    {
        $response = $this->client->get('/v1/data/currencies', $params);
        $response->throw();
        $items = $response->data() ?? [];

        return array_map(fn (array $item) => Currency::fromArray($item), $items);
    }

    public function fxRate(array $params): FxRate
    {
        return $this->client->get('/v1/rates/fxrate', $params)->toDto(FxRate::class);
    }

    public function dailyRate(array $params): array
    {
        $response = $this->client->get('/v1/rates/daily', $params);
        $response->throw();

        return $response->data() ?? [];
    }
}
