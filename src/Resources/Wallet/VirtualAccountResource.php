<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Wallet;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\VirtualAccount;
use Sabaab\Rapyd\Resources\Concerns\HasCrud;

final class VirtualAccountResource
{
    use HasCrud;

    public function __construct(
        private readonly RapydClient $client,
    ) {}

    protected function basePath(): string
    {
        return '/v1/virtual_accounts';
    }

    protected function dtoClass(): string
    {
        return VirtualAccount::class;
    }

    public function close(string $id): VirtualAccount
    {
        return $this->client->delete("{$this->basePath()}/{$id}")->toDto(VirtualAccount::class);
    }
}
