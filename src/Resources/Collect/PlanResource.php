<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Collect;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Plan;
use Sabaab\Rapyd\Resources\Concerns\HasCrud;

final class PlanResource
{
    use HasCrud;

    public function __construct(
        private readonly RapydClient $client,
    ) {}

    protected function basePath(): string
    {
        return '/v1/plans';
    }

    protected function dtoClass(): string
    {
        return Plan::class;
    }
}
