<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;

final class WalletTransaction implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?float $amount,
        public readonly ?string $currency,
        public readonly ?string $type,
        public readonly ?string $balanceType,
        public readonly ?float $balance,
        public readonly ?string $status,
        public readonly ?string $reason,
        public readonly ?string $ewalletId,
        public readonly ?Carbon $createdAt,
        public readonly ?Carbon $updatedAt,
        public readonly ?array $metadata,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            amount: isset($data['amount']) ? (float) $data['amount'] : null,
            currency: $data['currency'] ?? null,
            type: $data['type'] ?? null,
            balanceType: $data['balance_type'] ?? null,
            balance: isset($data['balance']) ? (float) $data['balance'] : null,
            status: $data['status'] ?? null,
            reason: $data['reason'] ?? null,
            ewalletId: $data['ewallet_id'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::createFromTimestamp($data['updated_at']) : null,
            metadata: $data['metadata'] ?? null,
        );
    }
}
