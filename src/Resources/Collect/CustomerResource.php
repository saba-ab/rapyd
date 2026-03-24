<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Collect;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Customer;
use Sabaab\Rapyd\Resources\Concerns\HasCrud;

final class CustomerResource
{
    use HasCrud;

    public function __construct(
        private readonly RapydClient $client,
    ) {}

    protected function basePath(): string
    {
        return '/v1/customers';
    }

    protected function dtoClass(): string
    {
        return Customer::class;
    }

    public function addPaymentMethod(string $customerId, array $data): array
    {
        $response = $this->client->post("/v1/customers/{$customerId}/payment_methods", $data);
        $response->throw();

        return $response->data() ?? [];
    }

    public function listPaymentMethods(string $customerId, array $params = []): array
    {
        $response = $this->client->get("/v1/customers/{$customerId}/payment_methods", $params);
        $response->throw();

        return $response->data() ?? [];
    }

    public function deletePaymentMethod(string $customerId, string $paymentMethodId): array
    {
        $response = $this->client->delete("/v1/customers/{$customerId}/payment_methods/{$paymentMethodId}");
        $response->throw();

        return $response->data() ?? [];
    }
}
