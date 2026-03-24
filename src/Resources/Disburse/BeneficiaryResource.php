<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Disburse;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Beneficiary;
use Sabaab\Rapyd\Resources\Concerns\HasCrud;

final class BeneficiaryResource
{
    use HasCrud;

    public function __construct(
        private readonly RapydClient $client,
    ) {}

    protected function basePath(): string
    {
        return '/v1/payouts/beneficiary';
    }

    protected function dtoClass(): string
    {
        return Beneficiary::class;
    }
}
