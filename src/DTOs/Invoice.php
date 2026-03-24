<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\InvoiceStatus;

final class Invoice implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?string $customer,
        public readonly ?string $subscription,
        public readonly ?InvoiceStatus $status,
        public readonly ?string $currency,
        public readonly ?float $amountDue,
        public readonly ?float $amountPaid,
        public readonly ?float $amountRemaining,
        public readonly ?string $billingReason,
        public readonly ?Carbon $dueDate,
        public readonly ?string $paymentMethod,
        public readonly ?string $payout,
        public readonly ?Carbon $periodStart,
        public readonly ?Carbon $periodEnd,
        public readonly ?array $metadata,
        public readonly ?Carbon $createdAt,
        public readonly ?float $total,
        public readonly ?float $subtotal,
        public readonly ?float $tax,
        public readonly ?array $lines,
        public readonly ?array $discount,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            customer: $data['customer'] ?? null,
            subscription: $data['subscription'] ?? null,
            status: isset($data['status']) ? InvoiceStatus::tryFrom($data['status']) : null,
            currency: $data['currency'] ?? null,
            amountDue: isset($data['amount_due']) ? (float) $data['amount_due'] : null,
            amountPaid: isset($data['amount_paid']) ? (float) $data['amount_paid'] : null,
            amountRemaining: isset($data['amount_remaining']) ? (float) $data['amount_remaining'] : null,
            billingReason: $data['billing_reason'] ?? null,
            dueDate: isset($data['due_date']) ? Carbon::createFromTimestamp($data['due_date']) : null,
            paymentMethod: $data['payment_method'] ?? null,
            payout: $data['payout'] ?? null,
            periodStart: isset($data['period_start']) ? Carbon::createFromTimestamp($data['period_start']) : null,
            periodEnd: isset($data['period_end']) ? Carbon::createFromTimestamp($data['period_end']) : null,
            metadata: $data['metadata'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            total: isset($data['total']) ? (float) $data['total'] : null,
            subtotal: isset($data['subtotal']) ? (float) $data['subtotal'] : null,
            tax: isset($data['tax']) ? (float) $data['tax'] : null,
            lines: $data['lines'] ?? null,
            discount: $data['discount'] ?? null,
        );
    }
}
