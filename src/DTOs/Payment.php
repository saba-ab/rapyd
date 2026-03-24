<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\FixedSide;
use Sabaab\Rapyd\Enums\NextAction;
use Sabaab\Rapyd\Enums\PaymentStatus;

final class Payment implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly float $amount,
        public readonly ?float $originalAmount,
        public readonly bool $isPartial,
        public readonly string $currencyCode,
        public readonly ?string $countryCode,
        public readonly ?PaymentStatus $status,
        public readonly ?string $description,
        public readonly ?string $merchantReferenceId,
        public readonly ?string $customerToken,
        public readonly ?string $paymentMethod,
        public readonly ?array $paymentMethodData,
        public readonly ?Carbon $expiration,
        public readonly bool $captured,
        public readonly bool $refunded,
        public readonly float $refundedAmount,
        public readonly ?string $receiptEmail,
        public readonly ?string $redirectUrl,
        public readonly ?string $completePaymentUrl,
        public readonly ?string $errorPaymentUrl,
        public readonly ?string $statementDescriptor,
        public readonly ?string $transactionId,
        public readonly ?Carbon $createdAt,
        public readonly bool $paid,
        public readonly ?Carbon $paidAt,
        public readonly ?string $failureCode,
        public readonly ?string $failureMessage,
        public readonly ?NextAction $nextAction,
        public readonly ?string $flowType,
        public readonly ?array $ewallets,
        public readonly ?array $metadata,
        public readonly ?Address $address,
        public readonly ?array $dispute,
        public readonly ?array $escrow,
        public readonly ?float $fxRate,
        public readonly ?FixedSide $fixedSide,
        public readonly ?string $ewalletId,
        public readonly ?string $merchantRequestedCurrency,
        public readonly ?float $merchantRequestedAmount,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            amount: (float) $data['amount'],
            originalAmount: isset($data['original_amount']) ? (float) $data['original_amount'] : null,
            isPartial: $data['is_partial'] ?? false,
            currencyCode: $data['currency_code'] ?? $data['currency'] ?? '',
            countryCode: $data['country_code'] ?? null,
            status: isset($data['status']) ? PaymentStatus::tryFrom($data['status']) : null,
            description: $data['description'] ?? null,
            merchantReferenceId: $data['merchant_reference_id'] ?? null,
            customerToken: $data['customer_token'] ?? null,
            paymentMethod: $data['payment_method'] ?? null,
            paymentMethodData: $data['payment_method_data'] ?? null,
            expiration: isset($data['expiration']) ? Carbon::createFromTimestamp($data['expiration']) : null,
            captured: $data['captured'] ?? false,
            refunded: $data['refunded'] ?? false,
            refundedAmount: (float) ($data['refunded_amount'] ?? 0),
            receiptEmail: $data['receipt_email'] ?? null,
            redirectUrl: $data['redirect_url'] ?? null,
            completePaymentUrl: $data['complete_payment_url'] ?? null,
            errorPaymentUrl: $data['error_payment_url'] ?? null,
            statementDescriptor: $data['statement_descriptor'] ?? null,
            transactionId: $data['transaction_id'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            paid: $data['paid'] ?? false,
            paidAt: isset($data['paid_at']) ? Carbon::createFromTimestamp($data['paid_at']) : null,
            failureCode: $data['failure_code'] ?? null,
            failureMessage: $data['failure_message'] ?? null,
            nextAction: isset($data['next_action']) ? NextAction::tryFrom($data['next_action']) : null,
            flowType: $data['flow_type'] ?? null,
            ewallets: $data['ewallets'] ?? null,
            metadata: $data['metadata'] ?? null,
            address: isset($data['address']) ? Address::fromArray($data['address']) : null,
            dispute: $data['dispute'] ?? null,
            escrow: $data['escrow'] ?? null,
            fxRate: isset($data['fx_rate']) ? (float) $data['fx_rate'] : null,
            fixedSide: isset($data['fixed_side']) ? FixedSide::tryFrom($data['fixed_side']) : null,
            ewalletId: $data['ewallet_id'] ?? null,
            merchantRequestedCurrency: $data['merchant_requested_currency'] ?? null,
            merchantRequestedAmount: isset($data['merchant_requested_amount']) ? (float) $data['merchant_requested_amount'] : null,
        );
    }
}
