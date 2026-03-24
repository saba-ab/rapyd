<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Collect;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Invoice;
use Sabaab\Rapyd\Resources\Concerns\HasCrud;

final class InvoiceResource
{
    use HasCrud;

    public function __construct(
        private readonly RapydClient $client,
    ) {}

    protected function basePath(): string
    {
        return '/v1/invoices';
    }

    protected function dtoClass(): string
    {
        return Invoice::class;
    }

    public function finalize(string $id): Invoice
    {
        return $this->client->post("{$this->basePath()}/{$id}/finalize")->toDto(Invoice::class);
    }

    public function pay(string $id, array $data = []): Invoice
    {
        return $this->client->post("{$this->basePath()}/{$id}/pay", $data)->toDto(Invoice::class);
    }
}
