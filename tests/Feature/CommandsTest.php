<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('rapyd:test-connection succeeds with valid API response', function () {
    Http::fake([
        '*' => Http::response([
            'status' => ['status' => 'SUCCESS', 'error_code' => ''],
            'data' => [
                ['id' => 1, 'name' => 'US', 'iso_alpha2' => 'US'],
                ['id' => 2, 'name' => 'GB', 'iso_alpha2' => 'GB'],
                ['id' => 3, 'name' => 'DE', 'iso_alpha2' => 'DE'],
            ],
        ]),
    ]);

    $this->artisan('rapyd:test-connection')
        ->assertSuccessful();
});

it('rapyd:test-connection fails with API error', function () {
    Http::fake([
        '*' => Http::response([
            'status' => ['status' => 'ERROR', 'error_code' => 'UNAUTHENTICATED', 'message' => 'Bad credentials'],
        ], 401),
    ]);

    $this->artisan('rapyd:test-connection')
        ->assertFailed();
});

it('rapyd:list-payment-methods shows table for valid country', function () {
    Http::fake([
        '*' => Http::response([
            'status' => ['status' => 'SUCCESS', 'error_code' => ''],
            'data' => [
                ['type' => 'us_visa_card', 'name' => 'Visa', 'category' => 'card', 'currencies' => ['USD'], 'is_refundable' => true],
                ['type' => 'us_mastercard_card', 'name' => 'Mastercard', 'category' => 'card', 'currencies' => ['USD'], 'is_refundable' => true],
            ],
        ]),
    ]);

    $this->artisan('rapyd:list-payment-methods', ['country' => 'US'])
        ->expectsOutputToContain('us_visa_card')
        ->expectsOutputToContain('Found 2 payment methods')
        ->assertSuccessful();
});

it('rapyd:list-payment-methods shows error for invalid country code', function () {
    $this->artisan('rapyd:list-payment-methods', ['country' => 'INVALID'])
        ->expectsOutputToContain('2-letter')
        ->assertFailed();
});

it('rapyd:webhook-info displays configuration', function () {
    $this->artisan('rapyd:webhook-info')
        ->expectsOutputToContain('/rapyd/webhook')
        ->expectsOutputToContain('PAYMENT_COMPLETED')
        ->assertSuccessful();
});
