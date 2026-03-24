<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;

final class Currency implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $code,
        public readonly ?string $name,
        public readonly ?string $symbol,
        public readonly ?string $numericCode,
        public readonly ?bool $digitalCurrency,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            code: $data['code'],
            name: $data['name'] ?? null,
            symbol: $data['symbol'] ?? null,
            numericCode: $data['numeric_code'] ?? null,
            digitalCurrency: $data['digital_currency'] ?? null,
        );
    }
}
