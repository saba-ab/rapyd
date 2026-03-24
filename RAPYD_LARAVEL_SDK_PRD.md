# Rapyd Laravel SDK — Product Requirements Document & Implementation Guide

> **Purpose**: This document is the single source of truth for building `saba-ab/rapyd`, a full-featured Laravel package for the Rapyd fintech API. Feed this entire file to Claude Code as context before beginning implementation.

---

## 1. Project Overview

### What We're Building
A Laravel package (`composer require saba-ab/rapyd`) that wraps the entire Rapyd Payments REST API with:
- HMAC-SHA256 request signing (the hard part)
- Typed DTOs for every response object
- Resource classes covering all 6 Rapyd domains
- Webhook signature verification + Laravel event dispatch for every webhook type
- Auto-pagination via `LazyCollection`
- PHP 8.2+ enums for all status fields
- Artisan commands for developer ergonomics
- Full test suite with fakes/mocks

### Target
- PHP 8.2+
- Laravel 10.x / 11.x / 12.x
- PSR-4 autoloading
- Built on `spatie/laravel-package-tools` (handles ServiceProvider, config, routes, commands boilerplate)
- Scaffolded from `spatie/package-skeleton-laravel` (provides CI, code style, static analysis out of the box)
- Tested with `orchestra/testbench` (boots a real Laravel app for package tests)
- Code quality: `laravel/pint` for code style, `phpstan/phpstan` + `larastan/larastan` for static analysis

### Package Namespace
`Sabaab\Rapyd`

### Scaffolding — How to Start
1. Go to `github.com/spatie/package-skeleton-laravel` → click "Use this template"
2. Clone your new repo, run `php ./configure.php` (sets vendor name, namespace, package name)
3. The skeleton gives you: ServiceProvider, Facade, composer.json, phpunit.xml, GitHub Actions CI, pint.json, phpstan.neon
4. Overlay the structure from this PRD on top of the skeleton output

---

## 2. Rapyd API Fundamentals

### 2.1 Base URLs

| Environment | Base URL |
|---|---|
| Sandbox | `https://sandboxapi.rapyd.net` |
| Production | `https://api.rapyd.net` |

All paths start with `/v1/`.

### 2.2 Authentication — Request Signing

Every request requires these headers:

| Header | Value |
|---|---|
| `Content-Type` | `application/json` |
| `access_key` | API access key from Rapyd Client Portal |
| `salt` | Random string, 8-16 chars, unique per request |
| `timestamp` | Unix time in seconds (must be within 60s of actual time) |
| `signature` | Calculated HMAC-SHA256 signature |
| `idempotency` | Optional. Unique string to prevent duplicate operations |

#### Signature Formula

```
signature = BASE64( HEX( HMAC-SHA256( http_method + url_path + salt + timestamp + access_key + secret_key + body_string ) ) )
```

**CRITICAL implementation detail**: The signing pipeline is HMAC-SHA256 digest → convert to **hex string** → Base64 encode that hex string. You CANNOT Base64 encode the raw binary digest directly — Rapyd will reject it.

#### Signing Rules
- `http_method` must be **lowercase**: `get`, `post`, `put`, `delete`
- `url_path` is everything after the base URL, starting with `/v1/`. Include query params with `?` if present
- `body_string` is JSON with **no whitespace** except inside string values. For GET or empty bodies, use empty string `""` — NOT `"{}"`
- `secret_key` is used as the HMAC key AND also concatenated into the signing string
- The HMAC key for `hash_hmac()` is the `secret_key`

#### PHP Signature Implementation (reference)

```php
$toSign = $httpMethod . $urlPath . $salt . $timestamp . $accessKey . $secretKey . $bodyString;
$hmac = hash_hmac('sha256', $toSign, $secretKey); // Returns hex string
$signature = base64_encode($hmac);
```

This is simple in PHP because `hash_hmac()` returns hex by default. The pitfall in other languages is manually converting binary→hex→base64.

### 2.3 Response Envelope

Every Rapyd response follows this structure:

```json
{
  "status": {
    "error_code": "",
    "status": "SUCCESS",
    "message": "",
    "response_code": "",
    "operation_id": "uuid-here"
  },
  "data": { ... }
}
```

On error: `status.status` = `"ERROR"`, `error_code` and `message` populated, `data` may be null/absent.

For list endpoints, `data` is an array. Some list endpoints return pagination info in the top-level response (not inside `data`).

### 2.4 Webhook Signature Verification

Webhook signature uses a **different formula** than request signatures:

```
signature = BASE64( HMAC-SHA256( url_path + salt + timestamp + access_key + secret_key + body_string ) )
```

Note: **No `http_method` prefix**. The `url_path` is the full webhook URL configured in the Rapyd Client Portal.

Webhook headers contain: `Content-Type`, `salt`, `timestamp`, `signature`.

### 2.5 Idempotency

Optional `idempotency` header (unique string per request). The SDK should auto-generate one for POST requests (timestamp + salt), but allow override.

---

## 3. Complete Endpoint Map

### 3.1 Collect (Payments)

| Method | Path | Description | Resource Method |
|---|---|---|---|
| POST | `/v1/payments` | Create payment | `payments()->create()` |
| GET | `/v1/payments/{id}` | Retrieve payment | `payments()->get()` |
| PUT | `/v1/payments/{id}` | Update payment | `payments()->update()` |
| DELETE | `/v1/payments/{id}` | Cancel payment | `payments()->cancel()` |
| GET | `/v1/payments` | List payments | `payments()->list()` / `payments()->all()` |
| POST | `/v1/payments/{id}/capture` | Capture payment | `payments()->capture()` |
| POST | `/v1/refunds` | Create refund | `refunds()->create()` |
| GET | `/v1/refunds/{id}` | Retrieve refund | `refunds()->get()` |
| PUT | `/v1/refunds/{id}` | Update refund | `refunds()->update()` |
| GET | `/v1/refunds` | List refunds | `refunds()->list()` |
| GET | `/v1/payments/{id}/refunds` | List refunds by payment | `refunds()->listByPayment()` |
| POST | `/v1/customers` | Create customer | `customers()->create()` |
| GET | `/v1/customers/{id}` | Retrieve customer | `customers()->get()` |
| PUT | `/v1/customers/{id}` | Update customer | `customers()->update()` |
| DELETE | `/v1/customers/{id}` | Delete customer | `customers()->delete()` |
| GET | `/v1/customers` | List customers | `customers()->list()` |
| POST | `/v1/customers/{id}/payment_methods` | Add payment method to customer | `customers()->addPaymentMethod()` |
| GET | `/v1/customers/{id}/payment_methods` | List customer payment methods | `customers()->listPaymentMethods()` |
| DELETE | `/v1/customers/{id}/payment_methods/{pmId}` | Delete customer payment method | `customers()->deletePaymentMethod()` |
| POST | `/v1/checkout` | Create checkout page | `checkout()->create()` |
| GET | `/v1/checkout/{id}` | Retrieve checkout page | `checkout()->get()` |
| POST | `/v1/hosted/collect/payments` | Create payment link | `paymentLinks()->create()` |
| GET | `/v1/hosted/collect/payments/{id}` | Retrieve payment link | `paymentLinks()->get()` |
| GET | `/v1/hosted/collect/payments` | List payment links | `paymentLinks()->list()` |
| GET | `/v1/payment_methods/country?country={cc}` | List payment methods by country | `paymentMethods()->listByCountry()` |
| GET | `/v1/payment_methods/{type}/required_fields` | Get required fields | `paymentMethods()->requiredFields()` |
| POST | `/v1/subscriptions` | Create subscription | `subscriptions()->create()` |
| GET | `/v1/subscriptions/{id}` | Retrieve subscription | `subscriptions()->get()` |
| PUT | `/v1/subscriptions/{id}` | Update subscription | `subscriptions()->update()` |
| DELETE | `/v1/subscriptions/{id}` | Cancel subscription | `subscriptions()->cancel()` |
| GET | `/v1/subscriptions` | List subscriptions | `subscriptions()->list()` |
| POST | `/v1/plans` | Create plan | `plans()->create()` |
| GET | `/v1/plans/{id}` | Retrieve plan | `plans()->get()` |
| PUT | `/v1/plans/{id}` | Update plan | `plans()->update()` |
| DELETE | `/v1/plans/{id}` | Delete plan | `plans()->delete()` |
| GET | `/v1/plans` | List plans | `plans()->list()` |
| POST | `/v1/products` | Create product | `products()->create()` |
| GET | `/v1/products/{id}` | Retrieve product | `products()->get()` |
| PUT | `/v1/products/{id}` | Update product | `products()->update()` |
| DELETE | `/v1/products/{id}` | Delete product | `products()->delete()` |
| GET | `/v1/products` | List products | `products()->list()` |
| POST | `/v1/invoices` | Create invoice | `invoices()->create()` |
| GET | `/v1/invoices/{id}` | Retrieve invoice | `invoices()->get()` |
| PUT | `/v1/invoices/{id}` | Update invoice | `invoices()->update()` |
| DELETE | `/v1/invoices/{id}` | Delete invoice | `invoices()->delete()` |
| GET | `/v1/invoices` | List invoices | `invoices()->list()` |
| POST | `/v1/invoices/{id}/finalize` | Finalize invoice | `invoices()->finalize()` |
| POST | `/v1/invoices/{id}/pay` | Pay invoice | `invoices()->pay()` |
| GET | `/v1/payment_disputes/{id}` | Retrieve dispute | `disputes()->get()` |
| GET | `/v1/payment_disputes` | List disputes | `disputes()->list()` |
| POST | `/v1/escrows/{id}/escrow_releases` | Release escrow | `escrows()->release()` |

### 3.2 Disburse (Payouts)

| Method | Path | Description | Resource Method |
|---|---|---|---|
| POST | `/v1/payouts` | Create payout | `payouts()->create()` |
| GET | `/v1/payouts/{id}` | Retrieve payout | `payouts()->get()` |
| PUT | `/v1/payouts/{id}` | Update payout | `payouts()->update()` |
| DELETE | `/v1/payouts/{id}` | Cancel payout | `payouts()->cancel()` |
| GET | `/v1/payouts` | List payouts | `payouts()->list()` |
| POST | `/v1/payouts/confirm/{id}` | Confirm payout | `payouts()->confirm()` |
| POST | `/v1/payouts/complete/{id}/{amount}` | Complete payout (sandbox) | `payouts()->complete()` |
| POST | `/v1/payouts/{id}/beneficiary/response` | Set payout response | `payouts()->setResponse()` |
| GET | `/v1/payouts/{id}/payout_method_types` | List payout method types | `payoutMethods()->listTypes()` |
| GET | `/v1/payout_method_types` | List all payout method types | `payoutMethods()->list()` |
| GET | `/v1/payouts/required_fields/{type}` | Get required fields | `payoutMethods()->requiredFields()` |
| POST | `/v1/payouts/beneficiary` | Create beneficiary | `beneficiaries()->create()` |
| GET | `/v1/payouts/beneficiary/{id}` | Retrieve beneficiary | `beneficiaries()->get()` |
| PUT | `/v1/payouts/beneficiary/{id}` | Update beneficiary | `beneficiaries()->update()` |
| DELETE | `/v1/payouts/beneficiary/{id}` | Delete beneficiary | `beneficiaries()->delete()` |
| GET | `/v1/payouts/beneficiary` | List beneficiaries | `beneficiaries()->list()` |
| POST | `/v1/payouts/sender` | Create sender | `senders()->create()` |
| GET | `/v1/payouts/sender/{id}` | Retrieve sender | `senders()->get()` |
| PUT | `/v1/payouts/sender/{id}` | Update sender | `senders()->update()` |
| DELETE | `/v1/payouts/sender/{id}` | Delete sender | `senders()->delete()` |
| GET | `/v1/payouts/sender` | List senders | `senders()->list()` |

### 3.3 Wallet

| Method | Path | Description | Resource Method |
|---|---|---|---|
| POST | `/v1/user` | Create wallet | `wallets()->create()` |
| GET | `/v1/user/{id}` | Retrieve wallet | `wallets()->get()` |
| PUT | `/v1/user/{id}` | Update wallet | `wallets()->update()` |
| DELETE | `/v1/user/{id}` | Delete wallet | `wallets()->delete()` |
| GET | `/v1/user` | List wallets | `wallets()->list()` |
| POST | `/v1/user/{id}/contacts` | Add contact to wallet | `walletContacts()->create()` |
| GET | `/v1/user/{walletId}/contacts/{contactId}` | Retrieve contact | `walletContacts()->get()` |
| PUT | `/v1/user/{walletId}/contacts/{contactId}` | Update contact | `walletContacts()->update()` |
| DELETE | `/v1/user/{walletId}/contacts/{contactId}` | Delete contact | `walletContacts()->delete()` |
| GET | `/v1/user/{id}/contacts` | List contacts | `walletContacts()->list()` |
| POST | `/v1/account/transfer` | Transfer between wallets | `walletTransfers()->create()` |
| PUT | `/v1/account/transfer/response` | Set transfer response | `walletTransfers()->setResponse()` |
| GET | `/v1/user/{id}/transactions` | List transactions | `walletTransactions()->list()` |
| GET | `/v1/user/{walletId}/transactions/{transactionId}` | Retrieve transaction | `walletTransactions()->get()` |
| POST | `/v1/virtual_accounts` | Issue virtual account | `virtualAccounts()->create()` |
| GET | `/v1/virtual_accounts/{id}` | Retrieve virtual account | `virtualAccounts()->get()` |
| PUT | `/v1/virtual_accounts/{id}` | Update virtual account | `virtualAccounts()->update()` |
| DELETE | `/v1/virtual_accounts/{id}` | Close virtual account | `virtualAccounts()->close()` |
| GET | `/v1/virtual_accounts` | List virtual accounts | `virtualAccounts()->list()` |

### 3.4 Issuing (Cards)

| Method | Path | Description | Resource Method |
|---|---|---|---|
| POST | `/v1/issuing/cards` | Issue card | `cards()->create()` |
| GET | `/v1/issuing/cards/{id}` | Retrieve card | `cards()->get()` |
| PUT | `/v1/issuing/cards/{id}` | Update card | `cards()->update()` |
| POST | `/v1/issuing/cards/status` | Update card status | `cards()->updateStatus()` |
| POST | `/v1/issuing/cards/activate` | Activate card | `cards()->activate()` |
| GET | `/v1/issuing/cards` | List cards | `cards()->list()` |
| GET | `/v1/issuing/cards/{id}/transactions` | List card transactions | `cards()->listTransactions()` |
| POST | `/v1/issuing/cards/pin/set` | Set PIN | `cards()->setPin()` |
| GET | `/v1/issuing/cards/pin/get` | Get PIN | `cards()->getPin()` |
| POST | `/v1/issuing/card_programs` | Create card program | `cardPrograms()->create()` |
| GET | `/v1/issuing/card_programs/{id}` | Retrieve card program | `cardPrograms()->get()` |
| GET | `/v1/issuing/card_programs` | List card programs | `cardPrograms()->list()` |

### 3.5 Verify (KYC/KYB)

| Method | Path | Description | Resource Method |
|---|---|---|---|
| POST | `/v1/identities` | Create identity verification | `identities()->create()` |
| GET | `/v1/identities/{id}` | Retrieve identity | `identities()->get()` |
| GET | `/v1/identities` | List identities | `identities()->list()` |
| POST | `/v1/hosted/idv` | Create verification hosted page | `verification()->createHostedPage()` |
| GET | `/v1/verify/applications/status/{id}` | Get application status | `verification()->getApplicationStatus()` |

### 3.6 Protect (Fraud)

| Method | Path | Description | Resource Method |
|---|---|---|---|
| GET | `/v1/fraud/merchant/settings` | Get fraud settings | `fraud()->getSettings()` |
| PUT | `/v1/fraud/merchant/settings` | Update fraud settings | `fraud()->updateSettings()` |

### 3.7 Data / Utilities

| Method | Path | Description | Resource Method |
|---|---|---|---|
| GET | `/v1/data/countries` | List countries | `data()->countries()` |
| GET | `/v1/data/currencies` | List currencies | `data()->currencies()` |
| GET | `/v1/rates/fxrate` | Get FX rate | `data()->fxRate()` |
| GET | `/v1/rates/daily` | Get daily rate | `data()->dailyRate()` |

---

## 4. Complete Webhook Event Types

The SDK must register a Laravel Event class for every webhook type. Webhook `type` field values:

### 4.1 Payment Webhooks
- `PAYMENT_COMPLETED` — Payment is fully completed (funds captured)
- `PAYMENT_SUCCEEDED` — Payment succeeded (synchronous, same as response)
- `PAYMENT_FAILED` — Payment failed
- `PAYMENT_EXPIRED` — Payment expired before completion
- `PAYMENT_UPDATED` — Payment object was modified
- `PAYMENT_CAPTURED` — Auth-only payment was captured
- `PAYMENT_CANCELED` — Payment was canceled
- `PAYMENT_REFUND_COMPLETED` — Refund on a payment completed
- `PAYMENT_REFUND_FAILED` — Refund on a payment failed
- `PAYMENT_REFUND_REJECTED` — Refund was rejected by processor
- `PAYMENT_DISPUTE_CREATED` — Dispute was created
- `PAYMENT_DISPUTE_UPDATED` — Dispute status changed

### 4.2 Refund Webhooks
- `REFUND_COMPLETED` — Standalone refund completed
- `REFUND_FAILED` — Standalone refund failed
- `REFUND_REJECTED` — Standalone refund rejected

### 4.3 Customer Webhooks
- `CUSTOMER_CREATED` — Customer object created
- `CUSTOMER_UPDATED` — Customer object updated
- `CUSTOMER_DELETED` — Customer object deleted
- `CUSTOMER_PAYMENT_METHOD_CREATED` — Payment method added to customer
- `CUSTOMER_PAYMENT_METHOD_UPDATED` — Customer payment method updated
- `CUSTOMER_PAYMENT_METHOD_DELETED` — Customer payment method deleted
- `CUSTOMER_PAYMENT_METHOD_EXPIRING` — Customer payment method nearing expiry

### 4.4 Subscription Webhooks
- `CUSTOMER_SUBSCRIPTION_CREATED` — Subscription created
- `CUSTOMER_SUBSCRIPTION_UPDATED` — Subscription updated
- `CUSTOMER_SUBSCRIPTION_COMPLETED` — Subscription billing cycle completed
- `CUSTOMER_SUBSCRIPTION_CANCELED` — Subscription canceled
- `CUSTOMER_SUBSCRIPTION_PAST_DUE` — Subscription payment overdue
- `CUSTOMER_SUBSCRIPTION_TRIAL_END` — Subscription trial period ending
- `CUSTOMER_SUBSCRIPTION_RENEWED` — Subscription renewed

### 4.5 Invoice Webhooks
- `INVOICE_CREATED` — Invoice created
- `INVOICE_UPDATED` — Invoice updated
- `INVOICE_PAYMENT_CREATED` — Payment created for invoice
- `INVOICE_PAYMENT_SUCCEEDED` — Invoice payment succeeded
- `INVOICE_PAYMENT_FAILED` — Invoice payment failed

### 4.6 Payout Webhooks
- `PAYOUT_COMPLETED` — Payout fully completed
- `PAYOUT_UPDATED` — Payout object was updated
- `PAYOUT_FAILED` — Payout failed
- `PAYOUT_EXPIRED` — Payout expired
- `PAYOUT_CANCELED` — Payout was canceled
- `PAYOUT_RETURNED` — Payout was returned (beneficiary rejected)

### 4.7 Wallet Webhooks
- `WALLET_TRANSACTION` — Wallet transaction occurred
- `WALLET_FUNDS_ADDED` — Funds added to wallet
- `WALLET_FUNDS_REMOVED` — Funds removed from wallet
- `WALLET_TRANSFER_COMPLETED` — Transfer between wallets completed
- `WALLET_TRANSFER_FAILED` — Transfer between wallets failed
- `WALLET_TRANSFER_RESPONSE_RECEIVED` — Transfer response from beneficiary wallet

### 4.8 Card Issuing Webhooks
- `CARD_ISSUING_AUTHORIZATION_APPROVED` — Card transaction authorization approved
- `CARD_ISSUING_AUTHORIZATION_DECLINED` — Card transaction authorization declined
- `CARD_ISSUING_SALE` — Card sale completed
- `CARD_ISSUING_CREDIT` — Credit issued to card
- `CARD_ISSUING_REVERSAL` — Card transaction reversed
- `CARD_ISSUING_REFUND` — Refund to issued card
- `CARD_ISSUING_CHARGEBACK` — Chargeback on issued card
- `CARD_ISSUING_ADJUSTMENT` — Adjustment to card transaction
- `CARD_ISSUING_ATM_FEE` — ATM fee charged
- `CARD_ISSUING_ATM_WITHDRAWAL` — ATM withdrawal
- `CARD_ADDED_SUCCESSFULLY` — Card added successfully to customer
- `CARD_ISSUING_TRANSACTION_COMPLETED` — Card transaction fully completed

### 4.9 Verify/KYC Webhooks
- `VERIFY_APPLICATION_SUBMITTED` — KYC application submitted
- `VERIFY_APPLICATION_APPROVED` — KYC application approved
- `VERIFY_APPLICATION_REJECTED` — KYC application rejected

### 4.10 Virtual Account Webhooks
- `VIRTUAL_ACCOUNT_CREATED` — Virtual account created
- `VIRTUAL_ACCOUNT_UPDATED` — Virtual account updated
- `VIRTUAL_ACCOUNT_CLOSED` — Virtual account closed
- `VIRTUAL_ACCOUNT_TRANSACTION` — Transaction on virtual account

---

## 5. Enum Definitions

All enums go in `src/Enums/`. Use PHP 8.1+ backed enums with `string` backing type.

### 5.1 PaymentStatus
```php
enum PaymentStatus: string {
    case Active = 'ACT';        // Awaiting completion/3DS/capture
    case Closed = 'CLO';        // Payment completed successfully
    case Canceled = 'CAN';      // Canceled by client or customer bank
    case Error = 'ERR';         // Payment processing error
    case Expired = 'EXP';       // Payment expired
    case Reviewed = 'REV';      // Payment under review
    case New = 'NEW';           // Payment newly created
}
```

### 5.2 PaymentMethodCategory
```php
enum PaymentMethodCategory: string {
    case Card = 'card';
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case BankRedirect = 'bank_redirect';
    case EWallet = 'ewallet';
}
```

### 5.3 PaymentFlowType
```php
enum PaymentFlowType: string {
    case Direct = 'direct';
    case Redirect = 'redirect';
    case EWalletPayer = 'ewallet_payer';
}
```

### 5.4 NextAction
```php
enum NextAction: string {
    case ThreeDSVerification = '3d_verification';
    case PendingConfirmation = 'pending_confirmation';
    case PendingCapture = 'pending_capture';
    case NotApplicable = 'not_applicable';
}
```

### 5.5 RefundStatus
```php
enum RefundStatus: string {
    case Pending = 'Pending';
    case Completed = 'Completed';
    case Canceled = 'Canceled';
    case Error = 'Error';
    case Rejected = 'Rejected';
}
```

### 5.6 DisputeStatus
```php
enum DisputeStatus: string {
    case Active = 'ACT';
    case Review = 'RVW';
    case PreArbitration = 'PRA';
    case Arbitration = 'ARB';
    case Loss = 'LOS';
    case Win = 'WIN';
    case Reverse = 'REV';
}
```

### 5.7 PayoutStatus
```php
enum PayoutStatus: string {
    case Created = 'Created';
    case Confirmation = 'Confirmation';
    case Completed = 'Completed';
    case Canceled = 'Canceled';
    case Error = 'Error';
    case Expired = 'Expired';
    case Returned = 'Returned';
}
```

### 5.8 PayoutMethodCategory
```php
enum PayoutMethodCategory: string {
    case Bank = 'bank';
    case Cash = 'cash';
    case Card = 'card';
    case EWallet = 'ewallet';
    case RapydWallet = 'rapyd_ewallet';
}
```

### 5.9 SubscriptionStatus
```php
enum SubscriptionStatus: string {
    case Active = 'active';
    case Canceled = 'canceled';
    case PastDue = 'past_due';
    case Trialing = 'trialing';
    case Unpaid = 'unpaid';
}
```

### 5.10 InvoiceStatus
```php
enum InvoiceStatus: string {
    case Draft = 'draft';
    case Open = 'open';
    case Paid = 'paid';
    case Uncollectible = 'uncollectible';
    case Void = 'void';
}
```

### 5.11 WebhookStatus
```php
enum WebhookStatus: string {
    case New = 'NEW';
    case ReSent = 'RET';
    case Closed = 'CLO';
    case Error = 'ERR';
}
```

### 5.12 CardStatus
```php
enum CardStatus: string {
    case Active = 'ACT';
    case Inactive = 'INA';
    case Blocked = 'BLO';
    case Expired = 'EXP';
}
```

### 5.13 CardBlockReasonCode
```php
enum CardBlockReasonCode: string {
    case Stolen = 'STO';
    case Lost = 'LOS';
    case Fraud = 'FRD';
    case Canceled = 'CAN';
    case Locked = 'LOC';  // Incorrect PIN
}
```

### 5.14 EntityType
```php
enum EntityType: string {
    case Individual = 'individual';
    case Company = 'company';
}
```

### 5.15 FeeCalcType
```php
enum FeeCalcType: string {
    case Net = 'net';
    case Gross = 'gross';
}
```

### 5.16 FixedSide
```php
enum FixedSide: string {
    case Buy = 'buy';
    case Sell = 'sell';
}
```

### 5.17 WalletContactType
```php
enum WalletContactType: string {
    case Personal = 'personal';
    case Business = 'business';
}
```

### 5.18 CouponDuration
```php
enum CouponDuration: string {
    case Forever = 'forever';
    case Repeating = 'repeating';
    case Once = 'once';
}
```

### 5.19 PlanInterval
```php
enum PlanInterval: string {
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';
}
```

### 5.20 CheckoutPageStatus
```php
enum CheckoutPageStatus: string {
    case New = 'NEW';
    case Done = 'DON';
    case Expired = 'EXP';
}
```

### 5.21 EscrowStatus
```php
enum EscrowStatus: string {
    case Pending = 'pending';
    case Released = 'released';
    case PartiallyReleased = 'partially_released';
}
```

### 5.22 IssuingTxnType
```php
enum IssuingTxnType: string {
    case Sale = 'SALE';
    case Credit = 'CREDIT';
    case Reversal = 'REVERSAL';
    case Refund = 'REFUND';
    case Chargeback = 'CHARGEBACK';
    case Adjustment = 'ADJUSTMENT';
    case AtmFee = 'ATM_FEE';
    case AtmWithdrawal = 'ATM_WITHDRAWAL';
}
```

### 5.23 Environment
```php
enum Environment: string {
    case Sandbox = 'sandbox';
    case Production = 'production';
}
```

### 5.24 WebhookEventType
```php
// This is the master enum for ALL webhook types.
// Use for the webhook dispatcher switch and event mapping.
enum WebhookEventType: string {
    // Payments
    case PaymentCompleted = 'PAYMENT_COMPLETED';
    case PaymentSucceeded = 'PAYMENT_SUCCEEDED';
    case PaymentFailed = 'PAYMENT_FAILED';
    case PaymentExpired = 'PAYMENT_EXPIRED';
    case PaymentUpdated = 'PAYMENT_UPDATED';
    case PaymentCaptured = 'PAYMENT_CAPTURED';
    case PaymentCanceled = 'PAYMENT_CANCELED';
    case PaymentRefundCompleted = 'PAYMENT_REFUND_COMPLETED';
    case PaymentRefundFailed = 'PAYMENT_REFUND_FAILED';
    case PaymentRefundRejected = 'PAYMENT_REFUND_REJECTED';
    case PaymentDisputeCreated = 'PAYMENT_DISPUTE_CREATED';
    case PaymentDisputeUpdated = 'PAYMENT_DISPUTE_UPDATED';
    // Refunds
    case RefundCompleted = 'REFUND_COMPLETED';
    case RefundFailed = 'REFUND_FAILED';
    case RefundRejected = 'REFUND_REJECTED';
    // Customers
    case CustomerCreated = 'CUSTOMER_CREATED';
    case CustomerUpdated = 'CUSTOMER_UPDATED';
    case CustomerDeleted = 'CUSTOMER_DELETED';
    case CustomerPaymentMethodCreated = 'CUSTOMER_PAYMENT_METHOD_CREATED';
    case CustomerPaymentMethodUpdated = 'CUSTOMER_PAYMENT_METHOD_UPDATED';
    case CustomerPaymentMethodDeleted = 'CUSTOMER_PAYMENT_METHOD_DELETED';
    case CustomerPaymentMethodExpiring = 'CUSTOMER_PAYMENT_METHOD_EXPIRING';
    // Subscriptions
    case SubscriptionCreated = 'CUSTOMER_SUBSCRIPTION_CREATED';
    case SubscriptionUpdated = 'CUSTOMER_SUBSCRIPTION_UPDATED';
    case SubscriptionCompleted = 'CUSTOMER_SUBSCRIPTION_COMPLETED';
    case SubscriptionCanceled = 'CUSTOMER_SUBSCRIPTION_CANCELED';
    case SubscriptionPastDue = 'CUSTOMER_SUBSCRIPTION_PAST_DUE';
    case SubscriptionTrialEnd = 'CUSTOMER_SUBSCRIPTION_TRIAL_END';
    case SubscriptionRenewed = 'CUSTOMER_SUBSCRIPTION_RENEWED';
    // Invoices
    case InvoiceCreated = 'INVOICE_CREATED';
    case InvoiceUpdated = 'INVOICE_UPDATED';
    case InvoicePaymentCreated = 'INVOICE_PAYMENT_CREATED';
    case InvoicePaymentSucceeded = 'INVOICE_PAYMENT_SUCCEEDED';
    case InvoicePaymentFailed = 'INVOICE_PAYMENT_FAILED';
    // Payouts
    case PayoutCompleted = 'PAYOUT_COMPLETED';
    case PayoutUpdated = 'PAYOUT_UPDATED';
    case PayoutFailed = 'PAYOUT_FAILED';
    case PayoutExpired = 'PAYOUT_EXPIRED';
    case PayoutCanceled = 'PAYOUT_CANCELED';
    case PayoutReturned = 'PAYOUT_RETURNED';
    // Wallet
    case WalletTransaction = 'WALLET_TRANSACTION';
    case WalletFundsAdded = 'WALLET_FUNDS_ADDED';
    case WalletFundsRemoved = 'WALLET_FUNDS_REMOVED';
    case WalletTransferCompleted = 'WALLET_TRANSFER_COMPLETED';
    case WalletTransferFailed = 'WALLET_TRANSFER_FAILED';
    case WalletTransferResponseReceived = 'WALLET_TRANSFER_RESPONSE_RECEIVED';
    // Card Issuing
    case CardIssuingAuthApproved = 'CARD_ISSUING_AUTHORIZATION_APPROVED';
    case CardIssuingAuthDeclined = 'CARD_ISSUING_AUTHORIZATION_DECLINED';
    case CardIssuingSale = 'CARD_ISSUING_SALE';
    case CardIssuingCredit = 'CARD_ISSUING_CREDIT';
    case CardIssuingReversal = 'CARD_ISSUING_REVERSAL';
    case CardIssuingRefund = 'CARD_ISSUING_REFUND';
    case CardIssuingChargeback = 'CARD_ISSUING_CHARGEBACK';
    case CardIssuingAdjustment = 'CARD_ISSUING_ADJUSTMENT';
    case CardIssuingAtmFee = 'CARD_ISSUING_ATM_FEE';
    case CardIssuingAtmWithdrawal = 'CARD_ISSUING_ATM_WITHDRAWAL';
    case CardAddedSuccessfully = 'CARD_ADDED_SUCCESSFULLY';
    case CardIssuingTxnCompleted = 'CARD_ISSUING_TRANSACTION_COMPLETED';
    // Verify
    case VerifyApplicationSubmitted = 'VERIFY_APPLICATION_SUBMITTED';
    case VerifyApplicationApproved = 'VERIFY_APPLICATION_APPROVED';
    case VerifyApplicationRejected = 'VERIFY_APPLICATION_REJECTED';
    // Virtual Accounts
    case VirtualAccountCreated = 'VIRTUAL_ACCOUNT_CREATED';
    case VirtualAccountUpdated = 'VIRTUAL_ACCOUNT_UPDATED';
    case VirtualAccountClosed = 'VIRTUAL_ACCOUNT_CLOSED';
    case VirtualAccountTransaction = 'VIRTUAL_ACCOUNT_TRANSACTION';
}
```

---

## 6. Directory Structure

> **Note**: This structure is an overlay on top of `spatie/package-skeleton-laravel`. The skeleton provides: composer.json, phpunit.xml, .editorconfig, pint.json, phpstan.neon, GitHub Actions workflows, LICENSE, CHANGELOG.md, and the base ServiceProvider/Facade. You add your package-specific files on top.

```
rapyd/
├── composer.json                          ← from skeleton, customized
├── LICENSE
├── README.md
├── CHANGELOG.md
├── phpunit.xml.dist                       ← from skeleton
├── pint.json                              ← from skeleton (Laravel Pint code style)
├── phpstan.neon.dist                      ← from skeleton (PHPStan + Larastan)
├── phpstan-baseline.neon                  ← from skeleton
├── config/
│   └── rapyd.php
├── routes/
│   └── webhooks.php
├── src/
│   ├── RapydServiceProvider.php           ← extends Spatie PackageServiceProvider
│   ├── Facades/
│   │   └── Rapyd.php
│   ├── Rapyd.php                          ← main manager class (Facade resolves to this)
│   ├── Client/
│   │   ├── RapydClient.php
│   │   ├── SignatureGenerator.php
│   │   ├── PendingRequest.php
│   │   └── RapydResponse.php
│   ├── Enums/
│   │   ├── PaymentStatus.php
│   │   ├── PaymentMethodCategory.php
│   │   ├── PaymentFlowType.php
│   │   ├── NextAction.php
│   │   ├── RefundStatus.php
│   │   ├── DisputeStatus.php
│   │   ├── PayoutStatus.php
│   │   ├── PayoutMethodCategory.php
│   │   ├── SubscriptionStatus.php
│   │   ├── InvoiceStatus.php
│   │   ├── WebhookStatus.php
│   │   ├── CardStatus.php
│   │   ├── CardBlockReasonCode.php
│   │   ├── EntityType.php
│   │   ├── FeeCalcType.php
│   │   ├── FixedSide.php
│   │   ├── WalletContactType.php
│   │   ├── CouponDuration.php
│   │   ├── PlanInterval.php
│   │   ├── CheckoutPageStatus.php
│   │   ├── EscrowStatus.php
│   │   ├── IssuingTxnType.php
│   │   ├── Environment.php
│   │   └── WebhookEventType.php
│   ├── Resources/
│   │   ├── Concerns/
│   │   │   └── HasCrud.php
│   │   ├── Collect/
│   │   │   ├── PaymentResource.php
│   │   │   ├── RefundResource.php
│   │   │   ├── CustomerResource.php
│   │   │   ├── CheckoutResource.php
│   │   │   ├── PaymentMethodResource.php
│   │   │   ├── PaymentLinkResource.php
│   │   │   ├── SubscriptionResource.php
│   │   │   ├── PlanResource.php
│   │   │   ├── ProductResource.php
│   │   │   ├── InvoiceResource.php
│   │   │   ├── DisputeResource.php
│   │   │   └── EscrowResource.php
│   │   ├── Disburse/
│   │   │   ├── PayoutResource.php
│   │   │   ├── PayoutMethodResource.php
│   │   │   ├── BeneficiaryResource.php
│   │   │   └── SenderResource.php
│   │   ├── Wallet/
│   │   │   ├── WalletResource.php
│   │   │   ├── WalletContactResource.php
│   │   │   ├── WalletTransferResource.php
│   │   │   ├── WalletTransactionResource.php
│   │   │   └── VirtualAccountResource.php
│   │   ├── Issuing/
│   │   │   ├── CardResource.php
│   │   │   └── CardProgramResource.php
│   │   ├── Verify/
│   │   │   ├── IdentityResource.php
│   │   │   └── VerificationResource.php
│   │   ├── Protect/
│   │   │   └── FraudResource.php
│   │   └── Data/
│   │       └── DataResource.php
│   ├── DTOs/
│   │   ├── Concerns/
│   │   │   └── HasFactory.php
│   │   ├── Payment.php
│   │   ├── Refund.php
│   │   ├── Customer.php
│   │   ├── Checkout.php
│   │   ├── PaymentMethod.php
│   │   ├── PaymentLink.php
│   │   ├── Subscription.php
│   │   ├── Plan.php
│   │   ├── Product.php
│   │   ├── Invoice.php
│   │   ├── Dispute.php
│   │   ├── Payout.php
│   │   ├── Beneficiary.php
│   │   ├── Sender.php
│   │   ├── Wallet.php
│   │   ├── WalletContact.php
│   │   ├── WalletTransaction.php
│   │   ├── VirtualAccount.php
│   │   ├── Card.php
│   │   ├── CardTransaction.php
│   │   ├── CardProgram.php
│   │   ├── Country.php
│   │   ├── Currency.php
│   │   ├── Address.php
│   │   └── FxRate.php
│   ├── Pagination/
│   │   └── RapydPaginator.php
│   ├── Webhooks/
│   │   ├── WebhookSignatureVerifier.php
│   │   ├── WebhookController.php
│   │   ├── WebhookMiddleware.php
│   │   └── Events/
│   │       ├── RapydWebhookReceived.php          ← base/catch-all event
│   │       ├── PaymentCompletedEvent.php
│   │       ├── PaymentSucceededEvent.php
│   │       ├── PaymentFailedEvent.php
│   │       ├── PaymentExpiredEvent.php
│   │       ├── PaymentUpdatedEvent.php
│   │       ├── PaymentCapturedEvent.php
│   │       ├── PaymentCanceledEvent.php
│   │       ├── PaymentRefundCompletedEvent.php
│   │       ├── PaymentRefundFailedEvent.php
│   │       ├── PaymentDisputeCreatedEvent.php
│   │       ├── PaymentDisputeUpdatedEvent.php
│   │       ├── RefundCompletedEvent.php
│   │       ├── RefundFailedEvent.php
│   │       ├── CustomerCreatedEvent.php
│   │       ├── CustomerUpdatedEvent.php
│   │       ├── CustomerPaymentMethodCreatedEvent.php
│   │       ├── SubscriptionCreatedEvent.php
│   │       ├── SubscriptionUpdatedEvent.php
│   │       ├── SubscriptionCanceledEvent.php
│   │       ├── InvoicePaymentSucceededEvent.php
│   │       ├── PayoutCompletedEvent.php
│   │       ├── PayoutFailedEvent.php
│   │       ├── PayoutUpdatedEvent.php
│   │       ├── WalletTransactionEvent.php
│   │       ├── WalletTransferCompletedEvent.php
│   │       ├── CardIssuingAuthApprovedEvent.php
│   │       ├── CardIssuingAuthDeclinedEvent.php
│   │       ├── CardIssuingSaleEvent.php
│   │       ├── CardIssuingCreditEvent.php
│   │       └── ... (one per webhook type)
│   ├── Exceptions/
│   │   ├── RapydException.php
│   │   ├── AuthenticationException.php
│   │   ├── ValidationException.php
│   │   ├── ApiException.php
│   │   └── WebhookSignatureException.php
│   └── Commands/
│       ├── TestConnectionCommand.php
│       ├── ListPaymentMethodsCommand.php
│       └── WebhookSecretCommand.php
├── tests/
│   ├── TestCase.php                       ← extends Orchestra\Testbench\TestCase
│   ├── Unit/
│   │   ├── SignatureGeneratorTest.php
│   │   ├── RapydResponseTest.php
│   │   ├── WebhookSignatureVerifierTest.php
│   │   └── EnumTest.php
│   └── Feature/
│       ├── PaymentResourceTest.php
│       ├── CustomerResourceTest.php
│       ├── WebhookControllerTest.php
│       └── PaginatorTest.php
└── .github/
    └── workflows/
        ├── run-tests.yml                  ← from skeleton (multi-PHP, multi-Laravel matrix)
        ├── fix-php-code-style-issues.yml  ← from skeleton (auto Pint)
        ├── phpstan.yml                    ← from skeleton
        └── update-changelog.yml           ← from skeleton
```

---

## 7. Implementation Specifications

### 7.1 `config/rapyd.php`

```php
return [
    'access_key' => env('RAPYD_ACCESS_KEY', ''),
    'secret_key' => env('RAPYD_SECRET_KEY', ''),
    'sandbox' => env('RAPYD_SANDBOX', true),
    'base_url' => [
        'sandbox' => 'https://sandboxapi.rapyd.net',
        'production' => 'https://api.rapyd.net',
    ],
    'webhook' => [
        'path' => env('RAPYD_WEBHOOK_PATH', '/rapyd/webhook'),
        'tolerance' => env('RAPYD_WEBHOOK_TOLERANCE', 60), // seconds
        'middleware' => [], // additional middleware for webhook route
    ],
    'timeout' => env('RAPYD_TIMEOUT', 30),
    'retry' => [
        'times' => env('RAPYD_RETRY_TIMES', 3),
        'sleep' => env('RAPYD_RETRY_SLEEP', 100), // milliseconds
    ],
];
```

### 7.2 `RapydServiceProvider` Spec (Spatie PackageServiceProvider)

```php
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;

class RapydServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('rapyd')
            ->hasConfigFile()             // auto-publishes config/rapyd.php
            ->hasRoute('webhooks')         // auto-loads routes/webhooks.php
            ->hasCommands([
                TestConnectionCommand::class,
                ListPaymentMethodsCommand::class,
                WebhookSecretCommand::class,
            ])
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('saba-ab/rapyd');
            });
    }

    // Register bindings (runs during register phase)
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

    // Register webhook route with dynamic prefix from config (runs during boot phase)
    public function packageBooted(): void
    {
        // The webhook route needs the config-driven path prefix
        // Spatie's hasRoute() loads the file, but we register the middleware here
    }
}
```

### 7.3 `Rapyd` Manager Class Spec

The Facade resolves to this class. It's the main entry point that provides access to all resource classes:

```php
final class Rapyd
{
    public function __construct(private readonly RapydClient $client) {}

    public function payments(): PaymentResource { return new PaymentResource($this->client); }
    public function refunds(): RefundResource { return new RefundResource($this->client); }
    public function customers(): CustomerResource { return new CustomerResource($this->client); }
    public function checkout(): CheckoutResource { return new CheckoutResource($this->client); }
    public function paymentLinks(): PaymentLinkResource { return new PaymentLinkResource($this->client); }
    public function paymentMethods(): PaymentMethodResource { return new PaymentMethodResource($this->client); }
    public function subscriptions(): SubscriptionResource { return new SubscriptionResource($this->client); }
    public function plans(): PlanResource { return new PlanResource($this->client); }
    public function products(): ProductResource { return new ProductResource($this->client); }
    public function invoices(): InvoiceResource { return new InvoiceResource($this->client); }
    public function disputes(): DisputeResource { return new DisputeResource($this->client); }
    public function escrows(): EscrowResource { return new EscrowResource($this->client); }
    public function payouts(): PayoutResource { return new PayoutResource($this->client); }
    public function payoutMethods(): PayoutMethodResource { return new PayoutMethodResource($this->client); }
    public function beneficiaries(): BeneficiaryResource { return new BeneficiaryResource($this->client); }
    public function senders(): SenderResource { return new SenderResource($this->client); }
    public function wallets(): WalletResource { return new WalletResource($this->client); }
    public function walletContacts(): WalletContactResource { return new WalletContactResource($this->client); }
    public function walletTransfers(): WalletTransferResource { return new WalletTransferResource($this->client); }
    public function walletTransactions(): WalletTransactionResource { return new WalletTransactionResource($this->client); }
    public function virtualAccounts(): VirtualAccountResource { return new VirtualAccountResource($this->client); }
    public function cards(): CardResource { return new CardResource($this->client); }
    public function cardPrograms(): CardProgramResource { return new CardProgramResource($this->client); }
    public function identities(): IdentityResource { return new IdentityResource($this->client); }
    public function verification(): VerificationResource { return new VerificationResource($this->client); }
    public function fraud(): FraudResource { return new FraudResource($this->client); }
    public function data(): DataResource { return new DataResource($this->client); }

    // Access underlying client for raw requests
    public function client(): RapydClient { return $this->client; }
}
```

### 7.4 `Facades\Rapyd` Spec

```php
use Illuminate\Support\Facades\Facade;

/**
 * @see \Sabaab\Rapyd\Rapyd
 *
 * @method static PaymentResource payments()
 * @method static CustomerResource customers()
 * ... (add @method for IDE autocomplete)
 */
class Rapyd extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Sabaab\Rapyd\Rapyd::class;
    }
}
```

### 7.5 `SignatureGenerator` Spec

```
Input: httpMethod, urlPath, salt, timestamp, accessKey, secretKey, bodyString
Output: base64-encoded HMAC-SHA256 hex signature

Steps:
1. Concatenate: httpMethod + urlPath + salt + timestamp + accessKey + secretKey + bodyString
2. HMAC-SHA256 with secretKey as key → hex string (PHP hash_hmac returns hex by default)
3. Base64 encode the hex string
4. Return result
```

For webhook verification: same but without httpMethod prefix.

### 7.6 `RapydClient` Spec

- Uses Laravel's `Http` facade (Illuminate\Support\Facades\Http)
- Every request auto-attaches: `access_key`, `salt`, `timestamp`, `signature`, `Content-Type`
- POST requests auto-generate `idempotency` header (overridable)
- Retries on 5xx errors (configurable)
- On non-200 responses, throws typed exceptions based on `status.error_code`
- Body serialization: `json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)` — no pretty print, no extra whitespace

### 7.7 `RapydResponse` Spec

- Unwraps the `status`/`data` envelope
- Exposes: `->successful()`, `->data()`, `->status()`, `->operationId()`, `->toDto(DtoClass)`
- On error responses, `->throw()` converts to the appropriate exception

### 7.8 `HasCrud` Trait Spec

Provides shared CRUD logic for resource classes:

```php
trait HasCrud {
    abstract protected function basePath(): string;
    abstract protected function dtoClass(): string;

    public function create(array $data): mixed {
        return $this->client->post($this->basePath(), $data)->toDto($this->dtoClass());
    }

    public function get(string $id): mixed {
        return $this->client->get("{$this->basePath()}/{$id}")->toDto($this->dtoClass());
    }

    public function update(string $id, array $data): mixed {
        return $this->client->put("{$this->basePath()}/{$id}", $data)->toDto($this->dtoClass());
    }

    public function delete(string $id): mixed {
        return $this->client->delete("{$this->basePath()}/{$id}")->toDto($this->dtoClass());
    }

    public function list(array $params = []): RapydPaginator {
        return new RapydPaginator($this->client, $this->basePath(), $params, $this->dtoClass());
    }

    // Alias: returns LazyCollection from paginator
    public function all(array $params = []): LazyCollection {
        return $this->list($params)->lazy();
    }
}
```

### 7.9 DTO Spec

- Readonly public properties
- `fromArray(array $data): static` factory method
- Cast enum fields to their PHP enum types
- Cast timestamps to `Carbon` instances where appropriate
- Nested DTOs (e.g., `Payment` has `Address`, `PaymentMethod` nested objects)
- Implement `JsonSerializable` and `Arrayable`

### 7.10 `RapydPaginator` Spec

- Wraps list endpoints
- Implements `IteratorAggregate`
- `->lazy(): LazyCollection` for memory-efficient iteration
- Auto-fetches next pages by tracking page number
- Respects API pagination params: `page`, `limit` (default 10, max 100)

### 7.11 Webhook System Spec

- `WebhookMiddleware` verifies signature on incoming requests, throws `WebhookSignatureException` on mismatch
- `WebhookController` parses the `type` field, dispatches both:
  1. A **specific** event (e.g., `PaymentCompletedEvent`) — listeners can type-hint the exact event
  2. A **generic** `RapydWebhookReceived` event — catch-all for logging/debugging
- Each event class holds: `public readonly string $type`, `public readonly array $data`, `public readonly string $webhookId`, `public readonly int $timestamp`
- Payment/Payout/Refund events also hydrate the relevant DTO from `$data` for convenience
- Webhook route registered conditionally (can be disabled via config)

### 7.12 Artisan Commands

- `rapyd:test-connection` — Calls `GET /v1/data/countries`, confirms connectivity, shows environment (sandbox/prod)
- `rapyd:list-payment-methods {country}` — Lists available payment methods for a country code, formatted as table
- `rapyd:webhook-info` — Shows configured webhook URL path and lists all registered event types

---

## 8. Build Order (for Claude Code)

Execute in this order. Each phase should be committed separately and must pass tests before proceeding.

### Phase 0: Scaffold from Spatie Skeleton
1. Go to `github.com/spatie/package-skeleton-laravel` → "Use this template" → create repo
2. Clone, run `php ./configure.php` with: vendor=saba-ab, package=rapyd, namespace=Sabaab\Rapyd, class=Rapyd
3. Run `composer install` to verify skeleton works out of the box
4. Run `composer test` to verify the skeleton's placeholder test passes
5. Delete placeholder files: `src/Rapyd.php` (we'll replace), `src/RapydServiceProvider.php` (we'll replace), `src/Facades/Rapyd.php` (we'll replace), `src/Commands/RapydCommand.php` (placeholder)
6. Update `composer.json` to add our specific dependencies: `illuminate/http`, `nesbot/carbon`, `pestphp/pest-plugin-laravel`
7. Run `composer update`
8. Commit: "chore: scaffold from spatie/package-skeleton-laravel"

### Phase 1: Foundation
1. `config/rapyd.php` — replace skeleton placeholder with our config
2. `src/Enums/` — ALL 24 enum files (they have no dependencies)
3. `src/Exceptions/` — ALL 5 exception classes
4. `src/Client/SignatureGenerator.php`
5. `tests/Unit/SignatureGeneratorTest.php` — test with known inputs/outputs from Rapyd docs
6. `tests/Unit/EnumTest.php` — verify all enums construct from their string values
7. Run `composer test` — all tests green
8. Run `composer analyse` — no PHPStan errors
9. Commit: "feat: foundation — enums, exceptions, signature generator"

### Phase 2: HTTP Client
1. `src/Client/RapydResponse.php` — envelope unwrapper
2. `src/Client/RapydClient.php` — signs requests, attaches headers, uses Http facade
3. `src/Client/PendingRequest.php` — fluent builder (optional, can be part of RapydClient)
4. `tests/Unit/RapydResponseTest.php`
5. Run `composer test && composer analyse`
6. Commit: "feat: HTTP client with HMAC signing and response parsing"

### Phase 3: Laravel Integration
1. `src/Rapyd.php` — the manager class (Facade resolves to this)
2. `src/RapydServiceProvider.php` — extends `Spatie\LaravelPackageTools\PackageServiceProvider`
3. `src/Facades/Rapyd.php` — with `@method` annotations for IDE support
4. Update `tests/TestCase.php` to load our ServiceProvider and set test config
5. Verify: `php artisan vendor:publish --tag=rapyd-config` works in testbench
6. Run `composer test && composer analyse`
7. Commit: "feat: Laravel integration — ServiceProvider, Facade, config"

### Phase 4: DTOs
1. `src/DTOs/Concerns/HasFactory.php` trait
2. ALL DTO classes in `src/DTOs/` (Payment, Customer, Refund, etc.)
3. Use enum types for status fields, `?Carbon` for timestamps, `?float` for amounts
4. Quick unit test for a couple of DTOs to verify hydration
5. Run `composer test && composer analyse`
6. Commit: "feat: typed DTOs for all API response objects"

### Phase 5: Resource Layer
1. `src/Resources/Concerns/HasCrud.php` trait
2. `src/Resources/Data/DataResource.php` (simplest, good to test first)
3. ALL Collect resources (Payment, Refund, Customer, Checkout, etc.)
4. ALL Disburse resources (Payout, Beneficiary, Sender)
5. ALL Wallet resources (Wallet, Contact, Transfer, Transaction, VirtualAccount)
6. ALL Issuing resources (Card, CardProgram)
7. ALL Verify resources (Identity, Verification)
8. ALL Protect resources (Fraud)
9. Wire all resources into `src/Rapyd.php` manager (accessor methods)
10. `tests/Feature/PaymentResourceTest.php` using `Http::fake()`
11. `tests/Feature/CustomerResourceTest.php` using `Http::fake()`
12. Run `composer test && composer analyse`
13. Commit: "feat: resource classes for all 6 API domains"

### Phase 6: Pagination
1. `src/Pagination/RapydPaginator.php`
2. Update `HasCrud::list()` and `HasCrud::all()` to use paginator
3. `tests/Feature/PaginatorTest.php` — fake multi-page responses
4. Run `composer test && composer analyse`
5. Commit: "feat: auto-pagination with LazyCollection"

### Phase 7: Webhooks
1. `src/Webhooks/WebhookSignatureVerifier.php`
2. `tests/Unit/WebhookSignatureVerifierTest.php`
3. `src/Webhooks/Events/RapydWebhookReceived.php` (base/catch-all event)
4. ALL specific event classes in `src/Webhooks/Events/`
5. `src/Webhooks/WebhookMiddleware.php`
6. `src/Webhooks/WebhookController.php`
7. `routes/webhooks.php`
8. Wire into ServiceProvider (already handled by `$package->hasRoute('webhooks')`)
9. `tests/Feature/WebhookControllerTest.php`
10. Run `composer test && composer analyse`
11. Commit: "feat: webhook signature verification and event dispatch"

### Phase 8: Commands & Polish
1. `src/Commands/TestConnectionCommand.php`
2. `src/Commands/ListPaymentMethodsCommand.php`
3. `src/Commands/WebhookSecretCommand.php`
4. Commands already registered in ServiceProvider via `$package->hasCommands([])`
5. Update `README.md` with full usage docs, badges, installation instructions
6. Run `composer format` (Pint), `composer analyse`, `composer test`
7. Commit: "feat: artisan commands and documentation"

---

## 9. Testing Strategy

### Test Framework
- **Pest PHP** (the Spatie skeleton includes Pest by default, Laravel 11+ is Pest-first)
- **Orchestra Testbench** for booting a real Laravel app in package tests

### Base TestCase

```php
// tests/TestCase.php
namespace Sabaab\Rapyd\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Sabaab\Rapyd\RapydServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [RapydServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['Rapyd' => \Sabaab\Rapyd\Facades\Rapyd::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('rapyd.access_key', 'rak_test_1234567890');
        $app['config']->set('rapyd.secret_key', 'rsk_test_abcdefghij');
        $app['config']->set('rapyd.sandbox', true);
    }
}
```

### Pest Configuration

```php
// tests/Pest.php
uses(Sabaab\Rapyd\Tests\TestCase::class)->in('Feature');
// Unit tests don't need the Laravel app — use default Pest base
```

### Unit Tests (Pest style, no Laravel app needed)
- `SignatureGeneratorTest` — Known input/output pairs. Verify empty body, body with special chars, query params in URL
- `RapydResponseTest` — Test envelope unwrapping, error detection, DTO hydration
- `WebhookSignatureVerifierTest` — Known webhook payloads, valid/invalid signatures, timestamp tolerance
- `EnumTest` — Verify all enums construct from their string values using `tryFrom()`

```php
// Example: tests/Unit/SignatureGeneratorTest.php
it('generates a valid signature for a GET request', function () {
    $generator = new SignatureGenerator('rak_test', 'rsk_test');
    $signature = $generator->generate('get', '/v1/data/countries', 'salt123', '1234567890', '');
    expect($signature)->toBeString()->not->toBeEmpty();
});

it('generates different signatures for different bodies', function () {
    $generator = new SignatureGenerator('rak_test', 'rsk_test');
    $sig1 = $generator->generate('post', '/v1/payments', 'salt1', '1234567890', '{"amount":100}');
    $sig2 = $generator->generate('post', '/v1/payments', 'salt1', '1234567890', '{"amount":200}');
    expect($sig1)->not->toBe($sig2);
});
```

### Feature Tests (Pest style, uses Http::fake(), Laravel app booted via TestCase)
- `PaymentResourceTest` — Create, get, list, capture payment with faked HTTP responses
- `CustomerResourceTest` — CRUD operations
- `WebhookControllerTest` — POST valid webhook → events dispatched. POST invalid signature → 403. POST unknown type → generic event only
- `PaginatorTest` — Fake multi-page responses, verify lazy collection yields all items

```php
// Example: tests/Feature/PaymentResourceTest.php
use Illuminate\Support\Facades\Http;
use Sabaab\Rapyd\Facades\Rapyd;

it('creates a payment', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/payments' => Http::response([
            'status' => ['status' => 'SUCCESS', 'error_code' => '', 'message' => ''],
            'data' => [
                'id' => 'payment_abc123',
                'amount' => 100.00,
                'currency_code' => 'EUR',
                'status' => 'CLO',
                'paid' => true,
                'created_at' => 1700000000,
            ],
        ]),
    ]);

    $payment = Rapyd::payments()->create(['amount' => 100, 'currency' => 'EUR']);

    expect($payment)
        ->id->toBe('payment_abc123')
        ->amount->toBe(100.00)
        ->status->toBe(\Sabaab\Rapyd\Enums\PaymentStatus::Closed);
});
```

### Test Helpers
Provide a `Rapyd::fake()` method (like `Http::fake()`) that pre-configures faked responses:
```php
Rapyd::fake([
    'payments/create' => Rapyd::fakePayment(['id' => 'payment_123', 'status' => 'CLO']),
]);
```

### CI Pipeline (from skeleton)
The Spatie skeleton includes GitHub Actions workflows that run automatically:
- `run-tests.yml` — Matrix of PHP 8.2/8.3/8.4 × Laravel 10/11/12
- `fix-php-code-style-issues.yml` — Auto-runs Pint and commits fixes
- `phpstan.yml` — Static analysis with Larastan
- `update-changelog.yml` — Auto-generates CHANGELOG from PR titles

---

## 10. Usage Examples (for README.md)

### Basic Payment
```php
use Sabaab\Rapyd\Facades\Rapyd;

$payment = Rapyd::payments()->create([
    'amount' => 100.00,
    'currency' => 'EUR',
    'payment_method' => ['type' => 'de_visa_card'],
    'customer' => 'cus_xxxx',
]);

$payment->id;     // "payment_abc123..."
$payment->status; // PaymentStatus::Closed
$payment->paid;   // true
```

### Auto-Paginated Listing
```php
foreach (Rapyd::customers()->all() as $customer) {
    echo $customer->name;
    // Automatically fetches next page when current page is exhausted
}
```

### Webhook Listening
```php
// In EventServiceProvider or via Event::listen()
use Sabaab\Rapyd\Webhooks\Events\PaymentCompletedEvent;

Event::listen(PaymentCompletedEvent::class, function ($event) {
    $event->payment->id;     // Typed Payment DTO
    $event->payment->amount; // float
    $event->webhookId;       // "wh_xxx"
});

// Catch-all for logging
use Sabaab\Rapyd\Webhooks\Events\RapydWebhookReceived;
Event::listen(RapydWebhookReceived::class, function ($event) {
    Log::info("Rapyd webhook: {$event->type}", $event->data);
});
```

### Artisan
```bash
php artisan rapyd:test-connection
# ✅ Connected to Rapyd Sandbox — 147 countries loaded

php artisan rapyd:list-payment-methods US
# +--------------------+---------------+-------------+
# | Type               | Category      | Refundable  |
# +--------------------+---------------+-------------+
# | us_visa_card       | card          | Yes         |
# | us_mastercard_card | card          | Yes         |
# | us_ach_bank        | bank_transfer | Yes         |
# +--------------------+---------------+-------------+
```

---

## 11. Claude Code Setup Instructions

### Prerequisites
```bash
# In your VS Code terminal, create the package directory
mkdir rapyd && cd rapyd
git init
```

### How to Use This Document

1. Open VS Code with Claude Code extension
2. Open this PRD file as context
3. Tell Claude Code: "Read the PRD at RAPYD_LARAVEL_SDK_PRD.md and implement Phase 1"
4. After each phase, review the code and run tests before proceeding
5. Proceed phase by phase: "Now implement Phase 2", etc.

### Important Notes for Claude Code

- **PHP 8.2+ features**: Use constructor promotion, readonly properties, match expressions, named arguments, fiber/enum features
- **No external HTTP client**: Use Laravel's built-in `Illuminate\Support\Facades\Http` (wraps Guzzle internally)
- **JSON serialization for signing**: Always use `json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)` with no pretty-print flags
- **Enum usage in DTOs**: Use PHP backed enums with `tryFrom()` (not `from()`) for resilience against unknown API values
- **Null safety**: The API returns many nullable fields. Use nullable types extensively in DTOs
- **Carbon for timestamps**: Convert Unix timestamps to Carbon instances in DTOs: `Carbon::createFromTimestamp($value)` when value is not null
- **LazyCollection**: Use `LazyCollection::make(function() { ... yield ... })` for the paginator to be memory-efficient
- **Test with `Http::fake()`**: All feature tests should fake HTTP responses, never hit real APIs
- **ServiceProvider**: Extend `Spatie\LaravelPackageTools\PackageServiceProvider`. Use `configurePackage()` to register config, routes, and commands. For custom bindings (RapydClient singleton), override `packageRegistered()`. For webhook route registration with dynamic config, override `packageBooted()`
- **Webhook route**: Use `$package->hasRoute('webhooks')` in configurePackage OR register manually in `packageBooted()` for dynamic prefix from config
- **Testing**: Extend `Orchestra\Testbench\TestCase`. Load ServiceProvider via `getPackageProviders()`. Set config in `defineEnvironment()`
- **Code style**: Run `./vendor/bin/pint` before committing. The skeleton includes Pint config
- **Static analysis**: Run `./vendor/bin/phpstan analyse`. The skeleton includes PHPStan + Larastan config

### Reference Packages to Study
These packages solve similar problems and demonstrate best patterns:
- `laravel/cashier-stripe` — Gold standard Laravel API SDK. Study: client structure, webhook controller, event dispatch
- `spatie/laravel-stripe-webhooks` — Webhook handling pattern: verify signature → dispatch events. Almost identical to what we need
- `saloonphp/saloon` — PHP API integration framework. Great patterns for connectors, request/response DTOs, pagination

---

## 12. Composer.json Specification

```json
{
    "name": "saba-ab/rapyd",
    "description": "Full-featured Laravel SDK for the Rapyd fintech API",
    "type": "library",
    "license": "MIT",
    "require": {
        "php": "^8.2",
        "spatie/laravel-package-tools": "^1.16",
        "illuminate/contracts": "^10.0|^11.0|^12.0",
        "illuminate/http": "^10.0|^11.0|^12.0",
        "illuminate/support": "^10.0|^11.0|^12.0",
        "nesbot/carbon": "^2.0|^3.0"
    },
    "require-dev": {
        "laravel/pint": "^1.14",
        "larastan/larastan": "^2.9|^3.0",
        "orchestra/testbench": "^8.0|^9.0|^10.0",
        "pestphp/pest": "^2.0|^3.0",
        "pestphp/pest-plugin-laravel": "^2.0|^3.0",
        "phpstan/extension-installer": "^1.3",
        "phpstan/phpstan-deprecation-rules": "^1.1|^2.0",
        "phpstan/phpstan-phpunit": "^1.3|^2.0"
    },
    "autoload": {
        "psr-4": {
            "Rapyd\\Laravel\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Rapyd\\Laravel\\Tests\\": "tests/"
        }
    },
    "scripts": {
        "post-autoload-dump": "@php ./vendor/bin/testbench package:discover --ansi",
        "analyse": "vendor/bin/phpstan analyse",
        "test": "vendor/bin/pest",
        "test-coverage": "vendor/bin/pest --coverage",
        "format": "vendor/bin/pint"
    },
    "config": {
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true,
            "phpstan/extension-installer": true
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Rapyd\\Laravel\\RapydServiceProvider"
            ],
            "aliases": {
                "Rapyd": "Rapyd\\Laravel\\Facades\\Rapyd"
            }
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

> **Note**: The skeleton's `configure.php` will generate most of this. You'll need to manually add the `illuminate/http` and `nesbot/carbon` require entries and the `pestphp/pest-plugin-laravel` dev dependency on top of what the skeleton provides.
