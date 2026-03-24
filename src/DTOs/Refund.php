<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\FixedSide;
use Sabaab\Rapyd\Enums\RefundStatus;

final class Refund implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly float $amount,
        public readonly ?string $currency,
        public readonly ?string $payment,
        public readonly ?string $reason,
        public readonly ?RefundStatus $status,
        public readonly ?array $metadata,
        public readonly ?string $merchantReferenceId,
        public readonly ?int $receiptNumber,
        public readonly ?Carbon $createdAt,
        public readonly ?Carbon $updatedAt,
        public readonly ?string $failureReason,
        public readonly ?array $ewallets,
        public readonly bool $proportionalRefund,
        public readonly ?float $fxRate,
        public readonly ?FixedSide $fixedSide,
        public readonly ?float $merchantDebitedAmount,
        public readonly ?string $merchantDebitedCurrency,
        public readonly ?Carbon $paymentCreatedAt,
        public readonly ?string $paymentMethodType,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            amount: (float) $data['amount'],
            currency: $data['currency'] ?? null,
            payment: $data['payment'] ?? null,
            reason: $data['reason'] ?? null,
            status: isset($data['status']) ? RefundStatus::tryFrom($data['status']) : null,
            metadata: $data['metadata'] ?? null,
            merchantReferenceId: $data['merchant_reference_id'] ?? null,
            receiptNumber: $data['receipt_number'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::createFromTimestamp($data['updated_at']) : null,
            failureReason: $data['failure_reason'] ?? null,
            ewallets: $data['ewallets'] ?? null,
            proportionalRefund: $data['proportional_refund'] ?? false,
            fxRate: isset($data['fx_rate']) ? (float) $data['fx_rate'] : null,
            fixedSide: isset($data['fixed_side']) ? FixedSide::tryFrom($data['fixed_side']) : null,
            merchantDebitedAmount: isset($data['merchant_debited_amount']) ? (float) $data['merchant_debited_amount'] : null,
            merchantDebitedCurrency: $data['merchant_debited_currency'] ?? null,
            paymentCreatedAt: isset($data['payment_created_at']) ? Carbon::createFromTimestamp($data['payment_created_at']) : null,
            paymentMethodType: $data['payment_method_type'] ?? null,
        );
    }
}
