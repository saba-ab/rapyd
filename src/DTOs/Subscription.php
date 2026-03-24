<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\SubscriptionStatus;

final class Subscription implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?string $customer,
        public readonly ?string $billing,
        public readonly ?Carbon $billingCycleAnchor,
        public readonly bool $cancelAtPeriodEnd,
        public readonly ?Carbon $canceledAt,
        public readonly ?Carbon $createdAt,
        public readonly ?Carbon $currentPeriodEnd,
        public readonly ?Carbon $currentPeriodStart,
        public readonly ?int $daysUntilDue,
        public readonly ?array $discount,
        public readonly ?Carbon $endedAt,
        public readonly ?array $metadata,
        public readonly ?string $paymentMethod,
        public readonly ?SubscriptionStatus $status,
        public readonly ?array $subscriptionItems,
        public readonly ?float $taxPercent,
        public readonly ?Carbon $trialEnd,
        public readonly ?Carbon $trialStart,
        public readonly ?string $type,
        public readonly ?bool $simultaneousInvoice,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            customer: $data['customer'] ?? null,
            billing: $data['billing'] ?? null,
            billingCycleAnchor: isset($data['billing_cycle_anchor']) ? Carbon::createFromTimestamp($data['billing_cycle_anchor']) : null,
            cancelAtPeriodEnd: $data['cancel_at_period_end'] ?? false,
            canceledAt: isset($data['canceled_at']) ? Carbon::createFromTimestamp($data['canceled_at']) : null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            currentPeriodEnd: isset($data['current_period_end']) ? Carbon::createFromTimestamp($data['current_period_end']) : null,
            currentPeriodStart: isset($data['current_period_start']) ? Carbon::createFromTimestamp($data['current_period_start']) : null,
            daysUntilDue: $data['days_until_due'] ?? null,
            discount: $data['discount'] ?? null,
            endedAt: isset($data['ended_at']) ? Carbon::createFromTimestamp($data['ended_at']) : null,
            metadata: $data['metadata'] ?? null,
            paymentMethod: $data['payment_method'] ?? null,
            status: isset($data['status']) ? SubscriptionStatus::tryFrom($data['status']) : null,
            subscriptionItems: $data['subscription_items'] ?? null,
            taxPercent: isset($data['tax_percent']) ? (float) $data['tax_percent'] : null,
            trialEnd: isset($data['trial_end']) ? Carbon::createFromTimestamp($data['trial_end']) : null,
            trialStart: isset($data['trial_start']) ? Carbon::createFromTimestamp($data['trial_start']) : null,
            type: $data['type'] ?? null,
            simultaneousInvoice: $data['simultaneous_invoice'] ?? null,
        );
    }
}
