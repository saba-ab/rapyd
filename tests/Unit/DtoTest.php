<?php

declare(strict_types=1);

use Carbon\Carbon;
use Sabaab\Rapyd\DTOs\Address;
use Sabaab\Rapyd\DTOs\Country;
use Sabaab\Rapyd\DTOs\Customer;
use Sabaab\Rapyd\DTOs\Payment;
use Sabaab\Rapyd\DTOs\Refund;
use Sabaab\Rapyd\DTOs\WalletContact;
use Sabaab\Rapyd\Enums\PaymentStatus;
use Sabaab\Rapyd\Enums\RefundStatus;

it('hydrates Payment from full data', function () {
    $payment = Payment::fromArray([
        'id' => 'payment_abc',
        'amount' => 100.50,
        'original_amount' => 100.50,
        'is_partial' => false,
        'currency_code' => 'USD',
        'country_code' => 'US',
        'status' => 'CLO',
        'description' => 'Test payment',
        'merchant_reference_id' => 'ref_123',
        'captured' => true,
        'refunded' => false,
        'refunded_amount' => 0,
        'paid' => true,
        'paid_at' => 1700000000,
        'created_at' => 1700000000,
    ]);

    expect($payment->id)->toBe('payment_abc');
    expect($payment->amount)->toBe(100.50);
    expect($payment->status)->toBe(PaymentStatus::Closed);
    expect($payment->paid)->toBeTrue();
    expect($payment->createdAt)->toBeInstanceOf(Carbon::class);
    expect($payment->createdAt->getTimestamp())->toBe(1700000000);
    expect($payment->captured)->toBeTrue();
    expect($payment->currencyCode)->toBe('USD');
});

it('hydrates Payment with minimal data', function () {
    $payment = Payment::fromArray([
        'id' => 'payment_min',
        'amount' => 50,
    ]);

    expect($payment->id)->toBe('payment_min');
    expect($payment->amount)->toBe(50.0);
    expect($payment->status)->toBeNull();
    expect($payment->description)->toBeNull();
    expect($payment->createdAt)->toBeNull();
    expect($payment->paidAt)->toBeNull();
    expect($payment->address)->toBeNull();
    expect($payment->metadata)->toBeNull();
    expect($payment->nextAction)->toBeNull();
    expect($payment->fixedSide)->toBeNull();
});

it('returns null for unknown Payment status via tryFrom', function () {
    $payment = Payment::fromArray([
        'id' => 'payment_unknown',
        'amount' => 10,
        'status' => 'UNKNOWN_STATUS',
    ]);

    expect($payment->status)->toBeNull();
});

it('hydrates Customer with nested data', function () {
    $customer = Customer::fromArray([
        'id' => 'cus_123',
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone_number' => '+1234567890',
        'delinquent' => false,
        'created_at' => 1700000000,
        'addresses' => [['id' => 'addr_1', 'city' => 'NYC']],
        'payment_methods' => [['id' => 'pm_1']],
    ]);

    expect($customer->id)->toBe('cus_123');
    expect($customer->name)->toBe('John Doe');
    expect($customer->email)->toBe('john@example.com');
    expect($customer->delinquent)->toBeFalse();
    expect($customer->createdAt)->toBeInstanceOf(Carbon::class);
    expect($customer->addresses)->toBeArray()->toHaveCount(1);
    expect($customer->paymentMethods)->toBeArray()->toHaveCount(1);
});

it('hydrates Refund with enum casting', function () {
    $refund = Refund::fromArray([
        'id' => 'refund_123',
        'amount' => 25.00,
        'currency' => 'EUR',
        'payment' => 'payment_abc',
        'status' => 'Completed',
        'proportional_refund' => true,
        'created_at' => 1700000000,
    ]);

    expect($refund->id)->toBe('refund_123');
    expect($refund->amount)->toBe(25.00);
    expect($refund->status)->toBe(RefundStatus::Completed);
    expect($refund->proportionalRefund)->toBeTrue();
});

it('hydrates Country as a simple DTO', function () {
    $country = Country::fromArray([
        'id' => 1,
        'name' => 'United States',
        'iso_alpha2' => 'US',
        'iso_alpha3' => 'USA',
        'currency_code' => 'USD',
        'currency_name' => 'US Dollar',
        'currency_sign' => '$',
        'phone_code' => '1',
    ]);

    expect($country->name)->toBe('United States');
    expect($country->isoAlpha2)->toBe('US');
    expect($country->isoAlpha3)->toBe('USA');
    expect($country->currencyCode)->toBe('USD');
});

it('roundtrips Payment through toArray', function () {
    $data = [
        'id' => 'payment_rt',
        'amount' => 99.99,
        'status' => 'ACT',
        'currency_code' => 'GBP',
        'paid' => true,
        'captured' => false,
        'refunded' => false,
        'refunded_amount' => 0,
        'is_partial' => false,
        'created_at' => 1700000000,
    ];

    $payment = Payment::fromArray($data);
    $array = $payment->toArray();

    expect($array['id'])->toBe('payment_rt');
    expect($array['amount'])->toBe(99.99);
    expect($array['status'])->toBe('ACT');
    expect($array['currency_code'])->toBe('GBP');
    expect($array['paid'])->toBeTrue();
    expect($array['created_at'])->toBe(1700000000);
});

it('hydrates nested Address in WalletContact', function () {
    $contact = WalletContact::fromArray([
        'id' => 'cont_123',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'contact_type' => 'personal',
        'address' => [
            'id' => 'addr_456',
            'city' => 'London',
            'country' => 'GB',
            'line_1' => '123 Main St',
        ],
    ]);

    expect($contact->id)->toBe('cont_123');
    expect($contact->firstName)->toBe('Jane');
    expect($contact->address)->toBeInstanceOf(Address::class);
    expect($contact->address->city)->toBe('London');
    expect($contact->address->country)->toBe('GB');
    expect($contact->address->line1)->toBe('123 Main St');
});

it('serializes nested Address in WalletContact toArray', function () {
    $contact = WalletContact::fromArray([
        'id' => 'cont_ser',
        'first_name' => 'Test',
        'address' => [
            'id' => 'addr_ser',
            'city' => 'Berlin',
            'country' => 'DE',
        ],
    ]);

    $array = $contact->toArray();

    expect($array['address'])->toBeArray();
    expect($array['address']['city'])->toBe('Berlin');
    expect($array['address']['country'])->toBe('DE');
});

it('hydrates Payment with nested Address', function () {
    $payment = Payment::fromArray([
        'id' => 'payment_addr',
        'amount' => 50,
        'address' => [
            'city' => 'Paris',
            'country' => 'FR',
            'zip' => '75001',
        ],
    ]);

    expect($payment->address)->toBeInstanceOf(Address::class);
    expect($payment->address->city)->toBe('Paris');
    expect($payment->address->zip)->toBe('75001');
});

it('handles currency fallback in Payment', function () {
    $payment = Payment::fromArray([
        'id' => 'payment_cur',
        'amount' => 10,
        'currency' => 'JPY',
    ]);

    expect($payment->currencyCode)->toBe('JPY');
});
