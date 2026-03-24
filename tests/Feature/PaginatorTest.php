<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Sabaab\Rapyd\DTOs\Customer;
use Sabaab\Rapyd\DTOs\Payment;
use Sabaab\Rapyd\Facades\Rapyd;
use Sabaab\Rapyd\Pagination\RapydPaginator;

function fakePaginatedCustomers(int $count): array
{
    $items = [];
    for ($i = 0; $i < $count; $i++) {
        $items[] = ['id' => "cus_{$i}", 'name' => "Customer {$i}", 'delinquent' => false];
    }

    return [
        'status' => ['status' => 'SUCCESS', 'error_code' => ''],
        'data' => $items,
    ];
}

it('fetches a single page of results', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/customers*' => Http::response(fakePaginatedCustomers(3)),
    ]);

    $customers = Rapyd::customers()->all()->collect();

    expect($customers)->toHaveCount(3);
    expect($customers->first())->toBeInstanceOf(Customer::class);
    Http::assertSentCount(1);
});

it('exhausts multiple pages automatically', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/customers*' => Http::sequence()
            ->push(fakePaginatedCustomers(10))
            ->push(fakePaginatedCustomers(5)),
    ]);

    $customers = Rapyd::customers()->all(['limit' => 10])->collect();

    expect($customers)->toHaveCount(15);
    expect($customers->every(fn ($c) => $c instanceof Customer))->toBeTrue();
    Http::assertSentCount(2);
});

it('returns empty collection for empty first page', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/customers*' => Http::response([
            'status' => ['status' => 'SUCCESS', 'error_code' => ''],
            'data' => [],
        ]),
    ]);

    $customers = Rapyd::customers()->all()->collect();

    expect($customers)->toHaveCount(0);
    Http::assertSentCount(1);
});

it('does not fetch until iterated (lazy)', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/customers*' => Http::response(fakePaginatedCustomers(3)),
    ]);

    $paginator = Rapyd::customers()->list();

    expect($paginator)->toBeInstanceOf(RapydPaginator::class);
    Http::assertSentCount(0);

    $first = $paginator->lazy()->first();

    expect($first)->toBeInstanceOf(Customer::class);
    Http::assertSentCount(1);
});

it('first() only requests one item', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/customers*' => Http::response([
            'status' => ['status' => 'SUCCESS', 'error_code' => ''],
            'data' => [['id' => 'cus_first', 'name' => 'First', 'delinquent' => false]],
        ]),
    ]);

    $customer = Rapyd::customers()->list()->first();

    expect($customer)->toBeInstanceOf(Customer::class);
    expect($customer->id)->toBe('cus_first');

    Http::assertSent(function ($request) {
        $url = $request->url();

        return str_contains($url, 'limit=1') && str_contains($url, 'page=1');
    });
});

it('passes page parameter correctly', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/customers*' => Http::response(fakePaginatedCustomers(3)),
    ]);

    Rapyd::customers()->all(['page' => 3, 'limit' => 5])->collect();

    Http::assertSent(function ($request) {
        $url = $request->url();

        return str_contains($url, 'page=3') && str_contains($url, 'limit=5');
    });
});

it('paginates Payment DTOs correctly', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/payments*' => Http::response([
            'status' => ['status' => 'SUCCESS', 'error_code' => ''],
            'data' => [
                ['id' => 'pay_1', 'amount' => 100, 'currency_code' => 'USD', 'paid' => true, 'captured' => true, 'refunded' => false, 'refunded_amount' => 0, 'is_partial' => false],
                ['id' => 'pay_2', 'amount' => 200, 'currency_code' => 'EUR', 'paid' => false, 'captured' => false, 'refunded' => false, 'refunded_amount' => 0, 'is_partial' => false],
            ],
        ]),
    ]);

    $payments = Rapyd::payments()->all()->collect();

    expect($payments)->toHaveCount(2);
    expect($payments[0])->toBeInstanceOf(Payment::class);
    expect($payments[0]->id)->toBe('pay_1');
    expect($payments[1]->amount)->toBe(200.0);
});
