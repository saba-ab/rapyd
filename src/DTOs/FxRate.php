<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\FixedSide;

final class FxRate implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly ?string $actionType,
        public readonly ?string $buyCurrency,
        public readonly ?string $sellCurrency,
        public readonly ?FixedSide $fixedSide,
        public readonly ?float $buyAmount,
        public readonly ?float $sellAmount,
        public readonly ?float $rate,
        public readonly ?string $date,
        public readonly ?array $metadata,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            actionType: $data['action_type'] ?? null,
            buyCurrency: $data['buy_currency'] ?? null,
            sellCurrency: $data['sell_currency'] ?? null,
            fixedSide: isset($data['fixed_side']) ? FixedSide::tryFrom($data['fixed_side']) : null,
            buyAmount: isset($data['buy_amount']) ? (float) $data['buy_amount'] : null,
            sellAmount: isset($data['sell_amount']) ? (float) $data['sell_amount'] : null,
            rate: isset($data['rate']) ? (float) $data['rate'] : null,
            date: $data['date'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }
}
