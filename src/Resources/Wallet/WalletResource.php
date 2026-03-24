<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Wallet;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Wallet;
use Sabaab\Rapyd\Resources\Concerns\HasCrud;

final class WalletResource
{
    use HasCrud;

    public function __construct(
        private readonly RapydClient $client,
    ) {}

    protected function basePath(): string
    {
        return '/v1/user';
    }

    protected function dtoClass(): string
    {
        return Wallet::class;
    }
}
