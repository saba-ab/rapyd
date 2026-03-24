<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Sabaab\Rapyd\DTOs\Payment;
use Sabaab\Rapyd\Facades\Rapyd;

function fakeRapydResponse(array $data): array
{
    return [
        'status' => ['status' => 'SUCCESS', 'error_code' => '', 'message' => '', 'operation_id' => 'op_test'],
        'data' => $data,
    ];
}

it('creates a payment and returns Payment DTO', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/payments' => Http::response(fakeRapydResponse([
            'id' => 'payment_abc',
            'amount' => 100,
            'currency_code' => 'USD',
            'status' => 'ACT',
            'paid' => false,
            'captured' => false,
            'refunded' => false,
            'refunded_amount' => 0,
            'is_partial' => false,
        ])),
    ]);

    $payment = Rapyd::payments()->create(['amount' => 100, 'currency' => 'USD']);

    expect($payment)->toBeInstanceOf(Payment::class);
    expect($payment->id)->toBe('payment_abc');
    expect($payment->amount)->toBe(100.0);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/v1/payments')
        && $request->method() === 'POST');
});

it('gets a payment by ID and returns Payment DTO', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/payments/payment_xyz' => Http::response(fakeRapydResponse([
            'id' => 'payment_xyz',
            'amount' => 50,
            'currency_code' => 'EUR',
            'status' => 'CLO',
            'paid' => true,
            'captured' => true,
            'refunded' => false,
            'refunded_amount' => 0,
            'is_partial' => false,
        ])),
    ]);

    $payment = Rapyd::payments()->get('payment_xyz');

    expect($payment)->toBeInstanceOf(Payment::class);
    expect($payment->id)->toBe('payment_xyz');
    expect($payment->paid)->toBeTrue();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/v1/payments/payment_xyz')
        && $request->method() === 'GET');
});

it('captures a payment and returns Payment DTO', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/payments/payment_cap/capture' => Http::response(fakeRapydResponse([
            'id' => 'payment_cap',
            'amount' => 200,
            'currency_code' => 'GBP',
            'status' => 'CLO',
            'paid' => true,
            'captured' => true,
            'refunded' => false,
            'refunded_amount' => 0,
            'is_partial' => false,
        ])),
    ]);

    $payment = Rapyd::payments()->capture('payment_cap', 200.0);

    expect($payment)->toBeInstanceOf(Payment::class);
    expect($payment->id)->toBe('payment_cap');
    expect($payment->captured)->toBeTrue();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/v1/payments/payment_cap/capture')
        && $request->method() === 'POST');
});
