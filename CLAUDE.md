# CLAUDE.md — Claude Code Project Instructions

## Project
`saba-ab/rapyd` — A full-featured Laravel package wrapping the Rapyd fintech API.

## Required Reading
Before writing any code, read `RAPYD_LARAVEL_SDK_PRD.md` in the project root. It contains:
- Complete Rapyd API authentication spec (HMAC-SHA256 signing)
- Full endpoint map (100+ endpoints across 6 domains)
- All webhook event types (50+ types)
- All PHP enum definitions (24 enums)
- Complete directory structure
- DTO, Resource, and Pagination specs
- Build order (Phase 0-8)
- Testing strategy with Pest examples

## Tech Stack
- PHP 8.2+ (use constructor promotion, readonly, enums, match, named args)
- Laravel 11/12/13 (use Http facade, not raw Guzzle)
- **`spatie/laravel-package-tools`** — ServiceProvider base class. Handles config, routes, commands registration
- **`orchestra/testbench`** — Boots a real Laravel app for package tests
- **Pest PHP** — Test framework (Spatie skeleton default, Laravel 11+ is Pest-first)
- **Laravel Pint** — Code style fixer (run `composer format`)
- **PHPStan + Larastan** — Static analysis (run `composer analyse`)
- Carbon for date/time handling

## Namespace
`Sabaab\Rapyd`

## Scaffolding
This package is scaffolded from `spatie/package-skeleton-laravel`. The skeleton provides:
- Pre-configured `composer.json`, `phpunit.xml.dist`, `pint.json`, `phpstan.neon.dist`
- GitHub Actions CI: test matrix (PHP 8.2-8.4 × Laravel 10-12), auto Pint, PHPStan, changelog
- Base ServiceProvider extending `Spatie\LaravelPackageTools\PackageServiceProvider`
- Pest configuration in `tests/Pest.php`

**Do not manually create files the skeleton already provides.** Modify what exists.

## Code Style & Conventions

### General
- PSR-12 coding standard (enforced by Pint)
- Strict types: every file starts with `declare(strict_types=1);`
- Use `final` on classes that aren't meant to be extended (DTOs, Enums, Events)
- Use `readonly` on all DTO properties
- Constructor promotion everywhere possible
- Return types on every method
- No `@param`/`@return` docblocks when types are declared (redundant)
- Docblocks only for complex descriptions, `@throws`, or `@method` annotations on Facades
- Run `composer format` (Pint) before every commit
- Run `composer analyse` (PHPStan) before every commit

### ServiceProvider (Spatie PackageServiceProvider)
The ServiceProvider extends `Spatie\LaravelPackageTools\PackageServiceProvider` — NOT `Illuminate\Support\ServiceProvider` directly. Use these methods:

```php
class RapydServiceProvider extends PackageServiceProvider
{
    // Declarative config — Spatie handles boot/register internally
    public function configurePackage(Package $package): void
    {
        $package
            ->name('rapyd')
            ->hasConfigFile()
            ->hasRoute('webhooks')
            ->hasCommands([...])
            ->hasInstallCommand(function (InstallCommand $command) {
                $command->publishConfigFile()
                    ->askToStarRepoOnGitHub('saba-ab/rapyd');
            });
    }

    // Custom bindings — runs during register phase
    public function packageRegistered(): void
    {
        $this->app->singleton(RapydClient::class, fn ($app) => ...);
        $this->app->singleton(Rapyd::class, fn ($app) => ...);
    }

    // Post-boot logic — runs during boot phase
    public function packageBooted(): void { }
}
```

**Key rule**: `configurePackage()` for declarative setup. `packageRegistered()` for bindings. `packageBooted()` for anything that needs the app fully booted.

### DTOs
- All properties `public readonly`
- Factory method: `public static function fromArray(array $data): static`
- Use `EnumType::tryFrom($value)` (NOT `from()`) for resilience to unknown API values
- Nullable types for optional fields: `public readonly ?string $description`
- Cast timestamps: `$data['created_at'] ? Carbon::createFromTimestamp($data['created_at']) : null`
- Cast nested objects: `isset($data['address']) ? Address::fromArray($data['address']) : null`
- Implement `\JsonSerializable` and `\Illuminate\Contracts\Support\Arrayable`

### Enums
- PHP 8.1 backed string enums: `enum PaymentStatus: string { case Active = 'ACT'; }`
- One file per enum in `src/Enums/`
- No methods on enums unless truly needed (keep them pure value types)

### Resources
- One file per API resource in `src/Resources/{Domain}/`
- Use `HasCrud` trait for standard CRUD operations
- Custom methods for non-standard endpoints (capture, confirm, etc.)
- Return DTOs, never raw arrays
- Accept arrays for request bodies (not DTOs — keep the request side flexible)

### Exceptions
- `RapydException` is the base (extends `\RuntimeException`)
- `ApiException` wraps API error responses — carries `errorCode`, `message`, `operationId`
- `AuthenticationException` for 401-type errors
- `ValidationException` for invalid field errors (INVALID_FIELDS)
- `WebhookSignatureException` for failed webhook verification

### Events
- Each webhook type gets its own event class in `src/Webhooks/Events/`
- All events extend or implement a common interface
- Events carry: `type` (string), `data` (array), `webhookId` (string), `timestamp` (int)
- Payment/Payout/Refund events additionally carry a hydrated DTO

### Testing (Pest)
- **Unit tests**: no Laravel app needed. Test pure functions (signing, response parsing, enum casting)
- **Feature tests**: use `Http::fake()`. Never hit real APIs. Laravel app booted via TestCase
- `tests/Pest.php` binds `TestCase` for Feature tests: `uses(TestCase::class)->in('Feature');`
- Use Pest's expressive syntax: `it()`, `expect()`, `beforeEach()`
- Descriptive test names: `it('generates a valid signature for a GET request')`

```php
// Unit test example
it('generates a valid signature', function () {
    $generator = new SignatureGenerator('rak_test', 'rsk_test');
    $sig = $generator->generate('get', '/v1/data/countries', 'salt', '1234567890', '');
    expect($sig)->toBeString()->not->toBeEmpty();
});

// Feature test example
it('creates a payment', function () {
    Http::fake(['sandboxapi.rapyd.net/*' => Http::response([
        'status' => ['status' => 'SUCCESS'],
        'data' => ['id' => 'payment_abc', 'amount' => 100, 'status' => 'CLO', 'paid' => true],
    ])]);
    $payment = Rapyd::payments()->create(['amount' => 100, 'currency' => 'EUR']);
    expect($payment)->id->toBe('payment_abc');
});
```

## Signing — The Critical Implementation Detail

This is the most important part and the most common source of bugs:

```php
// CORRECT implementation
$toSign = $httpMethod . $urlPath . $salt . $timestamp . $accessKey . $secretKey . $bodyString;
$hmac = hash_hmac('sha256', $toSign, $secretKey); // returns hex by default in PHP
$signature = base64_encode($hmac);
```

Rules:
- `$httpMethod` MUST be lowercase: `get`, `post`, `put`, `delete`
- `$urlPath` starts with `/v1/`, includes query string with `?` if present
- `$bodyString` is compact JSON (no whitespace). Empty string for GET, not `{}`
- Use `json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)` for body serialization
- Salt: use `bin2hex(random_bytes(8))` for 16-char hex string

## Webhook Signing — Different Formula

```php
// NOTE: No httpMethod in webhook signature!
$toSign = $urlPath . $salt . $timestamp . $accessKey . $secretKey . $bodyString;
```

## Build Order

**Phase 0**: Scaffold from `spatie/package-skeleton-laravel` → run `configure.php` → `composer install` → verify skeleton tests pass

Then implement in this order, each phase committed separately:

1. **Foundation**: config/rapyd.php, ALL 24 enums, ALL exceptions, SignatureGenerator + Pest test
2. **HTTP Client**: RapydResponse, RapydClient, PendingRequest + Pest test
3. **Laravel Integration**: Rapyd manager class, ServiceProvider (extends PackageServiceProvider), Facade
4. **DTOs**: HasFactory trait, then ALL DTOs
5. **Resources**: HasCrud trait, DataResource first, then all domain resources, wire into Rapyd manager
6. **Pagination**: RapydPaginator, wire into HasCrud
7. **Webhooks**: Verifier, ALL events, middleware, controller, routes/webhooks.php
8. **Commands & Polish**: Artisan commands, README, `composer format`, `composer analyse`, `composer test`

## File Templates

### ServiceProvider (Spatie)
```php
<?php

declare(strict_types=1);

namespace Sabaab\Rapyd;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\Client\SignatureGenerator;
use Sabaab\Rapyd\Commands\TestConnectionCommand;
use Sabaab\Rapyd\Commands\ListPaymentMethodsCommand;
use Sabaab\Rapyd\Commands\WebhookSecretCommand;

class RapydServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('rapyd')
            ->hasConfigFile()
            ->hasRoute('webhooks')
            ->hasCommands([
                TestConnectionCommand::class,
                ListPaymentMethodsCommand::class,
                WebhookSecretCommand::class,
            ])
            ->hasInstallCommand(function (InstallCommand $command) {
                $command->publishConfigFile()
                    ->askToStarRepoOnGitHub('saba-ab/rapyd');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(RapydClient::class, function ($app) {
            $config = $app['config']['rapyd'];
            return new RapydClient(
                new SignatureGenerator($config['access_key'], $config['secret_key']),
                $config['sandbox'] ? $config['base_url']['sandbox'] : $config['base_url']['production'],
                $config['access_key'],
                $config,
            );
        });

        $this->app->singleton(Rapyd::class, function ($app) {
            return new Rapyd($app->make(RapydClient::class));
        });
    }
}
```

### Enum File
```php
<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum PaymentStatus: string
{
    case Active = 'ACT';
    case Closed = 'CLO';
    // ...
}
```

### DTO File
```php
<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\PaymentStatus;

final class Payment implements \JsonSerializable, \Illuminate\Contracts\Support\Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly float $amount,
        public readonly string $currencyCode,
        public readonly ?PaymentStatus $status,
        public readonly bool $paid,
        public readonly ?Carbon $createdAt,
        // ...many more fields
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            amount: (float) $data['amount'],
            currencyCode: $data['currency_code'] ?? $data['currency'] ?? '',
            status: isset($data['status']) ? PaymentStatus::tryFrom($data['status']) : null,
            paid: $data['paid'] ?? false,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
        );
    }
}
```

### Resource File
```php
<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Collect;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\DTOs\Payment;
use Sabaab\Rapyd\Resources\Concerns\HasCrud;

final class PaymentResource
{
    use HasCrud;

    public function __construct(
        private readonly RapydClient $client,
    ) {}

    protected function basePath(): string
    {
        return '/v1/payments';
    }

    protected function dtoClass(): string
    {
        return Payment::class;
    }

    public function capture(string $id, ?float $amount = null): Payment
    {
        $body = $amount !== null ? ['amount' => $amount] : [];
        return $this->client->post("{$this->basePath()}/{$id}/capture", $body)
            ->toDto(Payment::class);
    }
}
```

### Event File
```php
<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Webhooks\Events;

use Sabaab\Rapyd\DTOs\Payment;

final class PaymentCompletedEvent
{
    public readonly Payment $payment;

    public function __construct(
        public readonly string $type,
        public readonly array $data,
        public readonly string $webhookId,
        public readonly int $timestamp,
    ) {
        $this->payment = Payment::fromArray($this->data);
    }
}
```

### Pest Test File
```php
<?php

// tests/Unit/SignatureGeneratorTest.php
use Sabaab\Rapyd\Client\SignatureGenerator;

it('generates a valid HMAC-SHA256 signature', function () {
    $generator = new SignatureGenerator('rak_test_123', 'rsk_test_abc');
    $signature = $generator->generate('get', '/v1/data/countries', 'abcd1234', '1700000000', '');

    expect($signature)->toBeString()->not->toBeEmpty();
});

it('uses empty string for GET body, not curly braces', function () {
    $generator = new SignatureGenerator('rak_test', 'rsk_test');
    $sigEmpty = $generator->generate('get', '/v1/data/countries', 'salt', '123', '');
    $sigBraces = $generator->generate('get', '/v1/data/countries', 'salt', '123', '{}');

    expect($sigEmpty)->not->toBe($sigBraces);
});
```

## Reference Packages
Study these for architectural patterns:
- **`laravel/cashier-stripe`** — Gold standard Laravel API SDK. Client, resources, webhooks, events
- **`spatie/laravel-stripe-webhooks`** — Webhook verify → dispatch pattern (exactly our pattern)
- **`saloonphp/saloon`** — PHP API integration framework. Connectors, DTOs, pagination

## Common Pitfalls to Avoid
1. Do NOT base64-encode the raw HMAC binary output. Encode the hex string.
2. Do NOT send `{}` as body for GET requests. Send empty string.
3. Do NOT use `json_encode` with `JSON_PRETTY_PRINT` for signing.
4. Do NOT use `Enum::from()` for API values — use `tryFrom()` since the API may return values not in our enum.
5. Do NOT hardcode sandbox URL. Always read from config.
6. Do NOT forget the `idempotency` header on POST requests.
7. Do NOT assume `data` key exists in error responses — it may be absent.
8. Webhook signature formula is DIFFERENT from request signature (no http_method).
9. Do NOT extend `Illuminate\Support\ServiceProvider` directly — extend `Spatie\LaravelPackageTools\PackageServiceProvider`.
10. Do NOT manually register config/routes/commands in boot() — use `$package->hasConfigFile()`, `->hasRoute()`, `->hasCommands()`.
11. Do NOT write PHPUnit-style tests — use Pest's `it()` / `expect()` syntax.
12. Do NOT skip `composer format` and `composer analyse` before commits.

## Composer Scripts
```bash
composer test          # Run Pest tests
composer test-coverage # Run tests with coverage
composer format        # Fix code style with Pint
composer analyse       # Run PHPStan + Larastan
```
