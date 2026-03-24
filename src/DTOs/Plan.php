<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\PlanInterval;

final class Plan implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?float $amount,
        public readonly ?string $currency,
        public readonly ?PlanInterval $interval,
        public readonly ?int $intervalCount,
        public readonly ?string $product,
        public readonly ?string $nickname,
        public readonly bool $active,
        public readonly ?array $metadata,
        public readonly ?Carbon $createdAt,
        public readonly ?string $aggregateUsage,
        public readonly ?string $billingScheme,
        public readonly ?int $trialPeriodDays,
        public readonly ?string $usageType,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            amount: isset($data['amount']) ? (float) $data['amount'] : null,
            currency: $data['currency'] ?? null,
            interval: isset($data['interval']) ? PlanInterval::tryFrom($data['interval']) : null,
            intervalCount: $data['interval_count'] ?? null,
            product: $data['product'] ?? null,
            nickname: $data['nickname'] ?? null,
            active: $data['active'] ?? false,
            metadata: $data['metadata'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            aggregateUsage: $data['aggregate_usage'] ?? null,
            billingScheme: $data['billing_scheme'] ?? null,
            trialPeriodDays: $data['trial_period_days'] ?? null,
            usageType: $data['usage_type'] ?? null,
        );
    }
}
