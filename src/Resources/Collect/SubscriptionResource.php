<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Collect;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Subscription;
use Sabaab\Rapyd\Resources\Concerns\HasCrud;

final class SubscriptionResource
{
    use HasCrud;

    public function __construct(
        private readonly RapydClient $client,
    ) {}

    protected function basePath(): string
    {
        return '/v1/subscriptions';
    }

    protected function dtoClass(): string
    {
        return Subscription::class;
    }

    public function cancel(string $id, array $data = []): Subscription
    {
        return $this->client->delete("{$this->basePath()}/{$id}", $data)->toDto(Subscription::class);
    }
}
