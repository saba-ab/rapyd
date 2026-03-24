<?php

declare(strict_types=1);

use Sabaab\Rapyd\Enums\CardBlockReasonCode;
use Sabaab\Rapyd\Enums\CardStatus;
use Sabaab\Rapyd\Enums\CheckoutPageStatus;
use Sabaab\Rapyd\Enums\CouponDuration;
use Sabaab\Rapyd\Enums\DisputeStatus;
use Sabaab\Rapyd\Enums\EntityType;
use Sabaab\Rapyd\Enums\Environment;
use Sabaab\Rapyd\Enums\EscrowStatus;
use Sabaab\Rapyd\Enums\FeeCalcType;
use Sabaab\Rapyd\Enums\FixedSide;
use Sabaab\Rapyd\Enums\InvoiceStatus;
use Sabaab\Rapyd\Enums\IssuingTxnType;
use Sabaab\Rapyd\Enums\NextAction;
use Sabaab\Rapyd\Enums\PaymentFlowType;
use Sabaab\Rapyd\Enums\PaymentMethodCategory;
use Sabaab\Rapyd\Enums\PaymentStatus;
use Sabaab\Rapyd\Enums\PayoutMethodCategory;
use Sabaab\Rapyd\Enums\PayoutStatus;
use Sabaab\Rapyd\Enums\PlanInterval;
use Sabaab\Rapyd\Enums\RefundStatus;
use Sabaab\Rapyd\Enums\SubscriptionStatus;
use Sabaab\Rapyd\Enums\WalletContactType;
use Sabaab\Rapyd\Enums\WebhookEventType;
use Sabaab\Rapyd\Enums\WebhookStatus;

it('constructs PaymentStatus from string values', function (string $value) {
    expect(PaymentStatus::from($value))->toBeInstanceOf(PaymentStatus::class);
})->with(['ACT', 'CLO', 'CAN', 'ERR', 'EXP', 'REV', 'NEW']);

it('constructs PaymentMethodCategory from string values', function (string $value) {
    expect(PaymentMethodCategory::from($value))->toBeInstanceOf(PaymentMethodCategory::class);
})->with(['card', 'cash', 'bank_transfer', 'bank_redirect', 'ewallet']);

it('constructs PaymentFlowType from string values', function (string $value) {
    expect(PaymentFlowType::from($value))->toBeInstanceOf(PaymentFlowType::class);
})->with(['direct', 'redirect', 'ewallet_payer']);

it('constructs NextAction from string values', function (string $value) {
    expect(NextAction::from($value))->toBeInstanceOf(NextAction::class);
})->with(['3d_verification', 'pending_confirmation', 'pending_capture', 'not_applicable']);

it('constructs RefundStatus from string values', function (string $value) {
    expect(RefundStatus::from($value))->toBeInstanceOf(RefundStatus::class);
})->with(['Pending', 'Completed', 'Canceled', 'Error', 'Rejected']);

it('constructs DisputeStatus from string values', function (string $value) {
    expect(DisputeStatus::from($value))->toBeInstanceOf(DisputeStatus::class);
})->with(['ACT', 'RVW', 'PRA', 'ARB', 'LOS', 'WIN', 'REV']);

it('constructs PayoutStatus from string values', function (string $value) {
    expect(PayoutStatus::from($value))->toBeInstanceOf(PayoutStatus::class);
})->with(['Created', 'Confirmation', 'Completed', 'Canceled', 'Error', 'Expired', 'Returned']);

it('constructs PayoutMethodCategory from string values', function (string $value) {
    expect(PayoutMethodCategory::from($value))->toBeInstanceOf(PayoutMethodCategory::class);
})->with(['bank', 'cash', 'card', 'ewallet', 'rapyd_ewallet']);

it('constructs SubscriptionStatus from string values', function (string $value) {
    expect(SubscriptionStatus::from($value))->toBeInstanceOf(SubscriptionStatus::class);
})->with(['active', 'canceled', 'past_due', 'trialing', 'unpaid']);

it('constructs InvoiceStatus from string values', function (string $value) {
    expect(InvoiceStatus::from($value))->toBeInstanceOf(InvoiceStatus::class);
})->with(['draft', 'open', 'paid', 'uncollectible', 'void']);

it('constructs WebhookStatus from string values', function (string $value) {
    expect(WebhookStatus::from($value))->toBeInstanceOf(WebhookStatus::class);
})->with(['NEW', 'RET', 'CLO', 'ERR']);

it('constructs CardStatus from string values', function (string $value) {
    expect(CardStatus::from($value))->toBeInstanceOf(CardStatus::class);
})->with(['ACT', 'INA', 'BLO', 'EXP']);

it('constructs CardBlockReasonCode from string values', function (string $value) {
    expect(CardBlockReasonCode::from($value))->toBeInstanceOf(CardBlockReasonCode::class);
})->with(['STO', 'LOS', 'FRD', 'CAN', 'LOC']);

it('constructs EntityType from string values', function (string $value) {
    expect(EntityType::from($value))->toBeInstanceOf(EntityType::class);
})->with(['individual', 'company']);

it('constructs FeeCalcType from string values', function (string $value) {
    expect(FeeCalcType::from($value))->toBeInstanceOf(FeeCalcType::class);
})->with(['net', 'gross']);

it('constructs FixedSide from string values', function (string $value) {
    expect(FixedSide::from($value))->toBeInstanceOf(FixedSide::class);
})->with(['buy', 'sell']);

it('constructs WalletContactType from string values', function (string $value) {
    expect(WalletContactType::from($value))->toBeInstanceOf(WalletContactType::class);
})->with(['personal', 'business']);

it('constructs CouponDuration from string values', function (string $value) {
    expect(CouponDuration::from($value))->toBeInstanceOf(CouponDuration::class);
})->with(['forever', 'repeating', 'once']);

it('constructs PlanInterval from string values', function (string $value) {
    expect(PlanInterval::from($value))->toBeInstanceOf(PlanInterval::class);
})->with(['day', 'week', 'month', 'year']);

it('constructs CheckoutPageStatus from string values', function (string $value) {
    expect(CheckoutPageStatus::from($value))->toBeInstanceOf(CheckoutPageStatus::class);
})->with(['NEW', 'DON', 'EXP']);

it('constructs EscrowStatus from string values', function (string $value) {
    expect(EscrowStatus::from($value))->toBeInstanceOf(EscrowStatus::class);
})->with(['pending', 'released', 'partially_released']);

it('constructs IssuingTxnType from string values', function (string $value) {
    expect(IssuingTxnType::from($value))->toBeInstanceOf(IssuingTxnType::class);
})->with(['SALE', 'CREDIT', 'REVERSAL', 'REFUND', 'CHARGEBACK', 'ADJUSTMENT', 'ATM_FEE', 'ATM_WITHDRAWAL']);

it('constructs Environment from string values', function (string $value) {
    expect(Environment::from($value))->toBeInstanceOf(Environment::class);
})->with(['sandbox', 'production']);

it('constructs WebhookEventType from string values', function (string $value) {
    expect(WebhookEventType::from($value))->toBeInstanceOf(WebhookEventType::class);
})->with([
    'PAYMENT_COMPLETED', 'PAYMENT_SUCCEEDED', 'PAYMENT_FAILED', 'PAYMENT_EXPIRED',
    'PAYMENT_UPDATED', 'PAYMENT_CAPTURED', 'PAYMENT_CANCELED',
    'PAYMENT_REFUND_COMPLETED', 'PAYMENT_REFUND_FAILED', 'PAYMENT_REFUND_REJECTED',
    'PAYMENT_DISPUTE_CREATED', 'PAYMENT_DISPUTE_UPDATED',
    'REFUND_COMPLETED', 'REFUND_FAILED', 'REFUND_REJECTED',
    'CUSTOMER_CREATED', 'CUSTOMER_UPDATED', 'CUSTOMER_DELETED',
    'CUSTOMER_PAYMENT_METHOD_CREATED', 'CUSTOMER_PAYMENT_METHOD_UPDATED',
    'CUSTOMER_PAYMENT_METHOD_DELETED', 'CUSTOMER_PAYMENT_METHOD_EXPIRING',
    'CUSTOMER_SUBSCRIPTION_CREATED', 'CUSTOMER_SUBSCRIPTION_UPDATED',
    'CUSTOMER_SUBSCRIPTION_COMPLETED', 'CUSTOMER_SUBSCRIPTION_CANCELED',
    'CUSTOMER_SUBSCRIPTION_PAST_DUE', 'CUSTOMER_SUBSCRIPTION_TRIAL_END',
    'CUSTOMER_SUBSCRIPTION_RENEWED',
    'INVOICE_CREATED', 'INVOICE_UPDATED', 'INVOICE_PAYMENT_CREATED',
    'INVOICE_PAYMENT_SUCCEEDED', 'INVOICE_PAYMENT_FAILED',
    'PAYOUT_COMPLETED', 'PAYOUT_UPDATED', 'PAYOUT_FAILED',
    'PAYOUT_EXPIRED', 'PAYOUT_CANCELED', 'PAYOUT_RETURNED',
    'WALLET_TRANSACTION', 'WALLET_FUNDS_ADDED', 'WALLET_FUNDS_REMOVED',
    'WALLET_TRANSFER_COMPLETED', 'WALLET_TRANSFER_FAILED', 'WALLET_TRANSFER_RESPONSE_RECEIVED',
    'CARD_ISSUING_AUTHORIZATION_APPROVED', 'CARD_ISSUING_AUTHORIZATION_DECLINED',
    'CARD_ISSUING_SALE', 'CARD_ISSUING_CREDIT', 'CARD_ISSUING_REVERSAL',
    'CARD_ISSUING_REFUND', 'CARD_ISSUING_CHARGEBACK', 'CARD_ISSUING_ADJUSTMENT',
    'CARD_ISSUING_ATM_FEE', 'CARD_ISSUING_ATM_WITHDRAWAL',
    'CARD_ADDED_SUCCESSFULLY', 'CARD_ISSUING_TRANSACTION_COMPLETED',
    'VERIFY_APPLICATION_SUBMITTED', 'VERIFY_APPLICATION_APPROVED', 'VERIFY_APPLICATION_REJECTED',
    'VIRTUAL_ACCOUNT_CREATED', 'VIRTUAL_ACCOUNT_UPDATED',
    'VIRTUAL_ACCOUNT_CLOSED', 'VIRTUAL_ACCOUNT_TRANSACTION',
]);

it('returns null for unknown enum values with tryFrom', function () {
    expect(PaymentStatus::tryFrom('UNKNOWN'))->toBeNull();
    expect(WebhookEventType::tryFrom('NONEXISTENT_EVENT'))->toBeNull();
    expect(PayoutStatus::tryFrom('InvalidStatus'))->toBeNull();
});
