<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Collect;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Product;
use Sabaab\Rapyd\Resources\Concerns\HasCrud;

final class ProductResource
{
    use HasCrud;

    public function __construct(
        private readonly RapydClient $client,
    ) {}

    protected function basePath(): string
    {
        return '/v1/products';
    }

    protected function dtoClass(): string
    {
        return Product::class;
    }
}
