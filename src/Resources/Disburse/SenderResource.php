<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Disburse;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Sender;
use Sabaab\Rapyd\Resources\Concerns\HasCrud;

final class SenderResource
{
    use HasCrud;

    public function __construct(
        private readonly RapydClient $client,
    ) {}

    protected function basePath(): string
    {
        return '/v1/payouts/sender';
    }

    protected function dtoClass(): string
    {
        return Sender::class;
    }
}
