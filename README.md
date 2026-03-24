# Rapyd Laravel SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/saba-ab/rapyd.svg?style=flat-square)](https://packagist.org/packages/saba-ab/rapyd)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/saba-ab/rapyd/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/saba-ab/rapyd/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/saba-ab/rapyd/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/saba-ab/rapyd/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/saba-ab/rapyd.svg?style=flat-square)](https://packagist.org/packages/saba-ab/rapyd)

A full-featured Laravel package for the Rapyd fintech API covering payments, payouts, wallets, card issuing, verification, and fraud protection.

## Features

- HMAC-SHA256 request signing handled automatically
- Typed DTOs for all API response objects with enum status fields
- Resource classes covering all 6 Rapyd domains (100+ endpoints)
- Webhook signature verification with Laravel event dispatch (50+ event types)
- Auto-pagination via LazyCollection for memory-efficient iteration
- 24 PHP 8.2+ backed enums for all status and type fields
- Artisan commands for testing connectivity and exploring payment methods
- Built on spatie/laravel-package-tools for clean Laravel integration

## Requirements

- PHP 8.2+
- Laravel 10.x, 11.x, or 12.x
- Rapyd API keys (get them at [dashboard.rapyd.net](https://dashboard.rapyd.net))

## Installation

Install the package via Composer:

```bash
composer require saba-ab/rapyd
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="rapyd-config"
```

Add your API keys to `.env`:

```env
RAPYD_ACCESS_KEY=rak_your_access_key
RAPYD_SECRET_KEY=rsk_your_secret_key
RAPYD_SANDBOX=true
```

## Quick Start

### Create a payment

```php
use Sabaab\Rapyd\Facades\Rapyd;

$payment = Rapyd::payments()->create([
    'amount' => 100,
    'currency' => 'USD',
    'payment_method' => [
        'type' => 'us_visa_card',
        'fields' => [
            'number' => '4111111111111111',
            'expiration_month' => '12',
            'expiration_year' => '25',
            'cvv' => '123',
            'name' => 'John Doe',
        ],
    ],
]);

echo $payment->id;       // "payment_abc123"
echo $payment->status;   // PaymentStatus::Active
echo $payment->amount;   // 100.0
```

### List customers with auto-pagination

```php
// Lazy iteration - fetches pages on demand, memory-efficient
foreach (Rapyd::customers()->all() as $customer) {
    echo $customer->name . "\n";
}

// Eager collection
$customers = Rapyd::customers()->list(['limit' => 50])->collect();
```

### Retrieve a payment

```php
$payment = Rapyd::payments()->get('payment_abc123');
echo $payment->paid;         // true
echo $payment->currencyCode; // "USD"
```

## Available Resources

### Collect (Payments)

| Accessor | Description |
|---|---|
| `Rapyd::payments()` | Create, retrieve, update, cancel, capture payments |
| `Rapyd::refunds()` | Create, retrieve, update refunds |
| `Rapyd::customers()` | Manage customers and payment methods |
| `Rapyd::checkout()` | Create and retrieve checkout pages |
| `Rapyd::paymentMethods()` | List payment methods by country, get required fields |
| `Rapyd::paymentLinks()` | Create and manage payment links |
| `Rapyd::subscriptions()` | Manage subscriptions |
| `Rapyd::plans()` | Manage subscription plans |
| `Rapyd::products()` | Manage products |
| `Rapyd::invoices()` | Create, manage, finalize, and pay invoices |
| `Rapyd::disputes()` | Retrieve and list disputes |
| `Rapyd::escrows()` | Release escrow funds |

### Disburse (Payouts)

| Accessor | Description |
|---|---|
| `Rapyd::payouts()` | Create, confirm, cancel payouts |
| `Rapyd::payoutMethods()` | List payout method types and required fields |
| `Rapyd::beneficiaries()` | Manage payout beneficiaries |
| `Rapyd::senders()` | Manage payout senders |

### Wallet

| Accessor | Description |
|---|---|
| `Rapyd::wallets()` | Create and manage e-wallets |
| `Rapyd::walletContacts()` | Manage wallet contacts |
| `Rapyd::walletTransfers()` | Transfer between wallets |
| `Rapyd::walletTransactions()` | View wallet transactions |
| `Rapyd::virtualAccounts()` | Issue and manage virtual accounts |

### Issuing (Cards)

| Accessor | Description |
|---|---|
| `Rapyd::cards()` | Issue, activate, manage cards |
| `Rapyd::cardPrograms()` | Manage card programs |

### Verify (KYC/KYB)

| Accessor | Description |
|---|---|
| `Rapyd::identities()` | Create and check identity verifications |
| `Rapyd::verification()` | Create hosted verification pages |

### Protect & Data

| Accessor | Description |
|---|---|
| `Rapyd::fraud()` | Get and update fraud settings |
| `Rapyd::data()` | List countries, currencies, FX rates |

## Webhooks

The package auto-registers a POST route at `/rapyd/webhook` (configurable) with automatic signature verification.

### Configure in Rapyd Dashboard

Set your webhook URL in the [Rapyd Client Portal](https://dashboard.rapyd.net) to:

```
https://your-app.com/rapyd/webhook
```

### Listen for events

```php
use Sabaab\Rapyd\Webhooks\Events\PaymentCompletedEvent;

Event::listen(PaymentCompletedEvent::class, function (PaymentCompletedEvent $event) {
    $payment = $event->payment; // Hydrated Payment DTO

    Order::where('payment_id', $payment->id)->update(['status' => 'paid']);
});
```

### Catch-all listener

```php
use Sabaab\Rapyd\Webhooks\Events\RapydWebhookReceived;

Event::listen(RapydWebhookReceived::class, function (RapydWebhookReceived $event) {
    Log::info("Rapyd webhook: {$event->type}", $event->data);
});
```

### Available event types

Events are dispatched for all 65 Rapyd webhook types, grouped by domain:

- **Payment**: PaymentCompleted, PaymentSucceeded, PaymentFailed, PaymentExpired, PaymentUpdated, PaymentCaptured, PaymentCanceled
- **Refund**: RefundCompleted, RefundFailed, RefundRejected, PaymentRefundCompleted, PaymentRefundFailed, PaymentRefundRejected
- **Customer**: CustomerCreated, CustomerUpdated, CustomerDeleted, CustomerPaymentMethodCreated/Updated/Deleted/Expiring
- **Subscription**: SubscriptionCreated, SubscriptionUpdated, SubscriptionCompleted, SubscriptionCanceled, SubscriptionPastDue, SubscriptionTrialEnd, SubscriptionRenewed
- **Invoice**: InvoiceCreated, InvoiceUpdated, InvoicePaymentCreated/Succeeded/Failed
- **Payout**: PayoutCompleted, PayoutUpdated, PayoutFailed, PayoutExpired, PayoutCanceled, PayoutReturned
- **Wallet**: WalletTransaction, WalletFundsAdded/Removed, WalletTransferCompleted/Failed/ResponseReceived
- **Card Issuing**: CardIssuingAuthApproved/Declined, CardIssuingSale, CardIssuingCredit, and more
- **Verify**: VerifyApplicationSubmitted/Approved/Rejected
- **Virtual Account**: VirtualAccountCreated/Updated/Closed/Transaction

## Artisan Commands

```bash
# Test API connectivity
php artisan rapyd:test-connection

# List payment methods for a country
php artisan rapyd:list-payment-methods US

# View webhook configuration
php artisan rapyd:webhook-info
```

## Configuration

After publishing, the configuration file is at `config/rapyd.php`:

```php
return [
    // API credentials
    'access_key' => env('RAPYD_ACCESS_KEY', ''),
    'secret_key' => env('RAPYD_SECRET_KEY', ''),

    // Set to false for production
    'sandbox' => env('RAPYD_SANDBOX', true),

    // Base URLs (override only if needed)
    'base_url' => [
        'sandbox' => 'https://sandboxapi.rapyd.net',
        'production' => 'https://api.rapyd.net',
    ],

    // Webhook settings
    'webhook' => [
        'path' => env('RAPYD_WEBHOOK_PATH', '/rapyd/webhook'),
        'tolerance' => env('RAPYD_WEBHOOK_TOLERANCE', 60), // seconds
        'middleware' => [],
    ],

    // HTTP timeout in seconds
    'timeout' => env('RAPYD_TIMEOUT', 30),

    // Retry on 5xx errors
    'retry' => [
        'times' => env('RAPYD_RETRY_TIMES', 3),
        'sleep' => env('RAPYD_RETRY_SLEEP', 100), // milliseconds
    ],
];
```

## DTOs and Enums

All API responses are returned as typed DTOs with `readonly` properties:

```php
$payment = Rapyd::payments()->get('payment_abc');

$payment->id;           // string
$payment->amount;       // float
$payment->status;       // ?PaymentStatus enum
$payment->createdAt;    // ?Carbon
$payment->paid;         // bool
$payment->address;      // ?Address (nested DTO)
```

Use enums for type-safe comparisons:

```php
use Sabaab\Rapyd\Enums\PaymentStatus;

if ($payment->status === PaymentStatus::Closed) {
    // Payment completed successfully
}
```

Available enums: `PaymentStatus`, `PaymentMethodCategory`, `PaymentFlowType`, `NextAction`, `RefundStatus`, `DisputeStatus`, `PayoutStatus`, `PayoutMethodCategory`, `SubscriptionStatus`, `InvoiceStatus`, `WebhookStatus`, `CardStatus`, `CardBlockReasonCode`, `EntityType`, `FeeCalcType`, `FixedSide`, `WalletContactType`, `CouponDuration`, `PlanInterval`, `CheckoutPageStatus`, `EscrowStatus`, `IssuingTxnType`, `Environment`, `WebhookEventType`.

## Pagination

```php
// Lazy iteration - pages fetched on demand
foreach (Rapyd::customers()->all() as $customer) {
    echo $customer->name;
}

// Eager collection
$all = Rapyd::payments()->list(['limit' => 50])->collect();

// First item only (fetches just 1 item)
$first = Rapyd::payments()->list()->first();

// Convert to array
$array = Rapyd::customers()->list()->toArray();
```

## Error Handling

```php
use Sabaab\Rapyd\Exceptions\ApiException;
use Sabaab\Rapyd\Exceptions\AuthenticationException;
use Sabaab\Rapyd\Exceptions\ValidationException;
use Sabaab\Rapyd\Exceptions\RapydException;

try {
    $payment = Rapyd::payments()->create([...]);
} catch (AuthenticationException $e) {
    // Invalid API keys
} catch (ValidationException $e) {
    // Invalid fields - $e->fields contains details
} catch (ApiException $e) {
    // API error - $e->errorCode, $e->operationId
} catch (RapydException $e) {
    // Base exception for all Rapyd errors
}
```

## Testing

Use `Http::fake()` to mock Rapyd API responses in your tests:

```php
use Illuminate\Support\Facades\Http;
use Sabaab\Rapyd\Facades\Rapyd;

Http::fake([
    'sandboxapi.rapyd.net/v1/payments' => Http::response([
        'status' => ['status' => 'SUCCESS', 'error_code' => ''],
        'data' => ['id' => 'payment_test', 'amount' => 100, 'paid' => true],
    ]),
]);

$payment = Rapyd::payments()->create(['amount' => 100, 'currency' => 'USD']);
```

Run the package test suite:

```bash
composer test          # Run Pest tests
composer test-coverage # Run tests with coverage
composer format        # Fix code style with Laravel Pint
composer analyse       # Run PHPStan + Larastan
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/saba-ab/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [saba-ab](https://github.com/saba-ab)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
