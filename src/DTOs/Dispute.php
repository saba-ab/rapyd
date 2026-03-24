<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\DisputeStatus;

final class Dispute implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?string $token,
        public readonly ?float $amount,
        public readonly ?string $currency,
        public readonly ?DisputeStatus $status,
        public readonly ?string $payment,
        public readonly ?float $originalAmount,
        public readonly ?string $originalCurrency,
        public readonly ?string $reason,
        public readonly ?Carbon $dueDate,
        public readonly ?Carbon $createdAt,
        public readonly ?Carbon $updatedAt,
        public readonly ?array $metadata,
        public readonly ?string $ewalletId,
        public readonly ?string $disputeCategory,
        public readonly ?string $disputeReasonDescription,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            token: $data['token'] ?? null,
            amount: isset($data['amount']) ? (float) $data['amount'] : null,
            currency: $data['currency'] ?? null,
            status: isset($data['status']) ? DisputeStatus::tryFrom($data['status']) : null,
            payment: $data['payment'] ?? null,
            originalAmount: isset($data['original_amount']) ? (float) $data['original_amount'] : null,
            originalCurrency: $data['original_currency'] ?? null,
            reason: $data['reason'] ?? null,
            dueDate: isset($data['due_date']) ? Carbon::createFromTimestamp($data['due_date']) : null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::createFromTimestamp($data['updated_at']) : null,
            metadata: $data['metadata'] ?? null,
            ewalletId: $data['ewallet_id'] ?? null,
            disputeCategory: $data['dispute_category'] ?? null,
            disputeReasonDescription: $data['dispute_reason_description'] ?? null,
        );
    }
}
