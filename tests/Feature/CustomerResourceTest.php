<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Sabaab\Rapyd\DTOs\Customer;
use Sabaab\Rapyd\Facades\Rapyd;

function fakeCustomerResponse(array $overrides = []): array
{
    $data = array_merge([
        'id' => 'cus_test',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'delinquent' => false,
    ], $overrides);

    return [
        'status' => ['status' => 'SUCCESS', 'error_code' => '', 'message' => '', 'operation_id' => 'op_test'],
        'data' => $data,
    ];
}

it('creates a customer and returns Customer DTO', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/customers' => Http::response(fakeCustomerResponse(['id' => 'cus_new'])),
    ]);

    $customer = Rapyd::customers()->create(['name' => 'Test User', 'email' => 'test@example.com']);

    expect($customer)->toBeInstanceOf(Customer::class);
    expect($customer->id)->toBe('cus_new');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/v1/customers')
        && $request->method() === 'POST');
});

it('gets a customer by ID and returns Customer DTO', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/customers/cus_123' => Http::response(fakeCustomerResponse(['id' => 'cus_123'])),
    ]);

    $customer = Rapyd::customers()->get('cus_123');

    expect($customer)->toBeInstanceOf(Customer::class);
    expect($customer->id)->toBe('cus_123');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/v1/customers/cus_123')
        && $request->method() === 'GET');
});

it('updates a customer and returns Customer DTO', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/customers/cus_upd' => Http::response(fakeCustomerResponse([
            'id' => 'cus_upd',
            'name' => 'Updated Name',
        ])),
    ]);

    $customer = Rapyd::customers()->update('cus_upd', ['name' => 'Updated Name']);

    expect($customer)->toBeInstanceOf(Customer::class);
    expect($customer->name)->toBe('Updated Name');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/v1/customers/cus_upd')
        && $request->method() === 'PUT');
});

it('deletes a customer and returns Customer DTO', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/customers/cus_del' => Http::response(fakeCustomerResponse(['id' => 'cus_del'])),
    ]);

    $customer = Rapyd::customers()->delete('cus_del');

    expect($customer)->toBeInstanceOf(Customer::class);
    expect($customer->id)->toBe('cus_del');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/v1/customers/cus_del')
        && $request->method() === 'DELETE');
});
