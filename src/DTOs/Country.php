<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;

final class Country implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $isoAlpha2,
        public readonly ?string $isoAlpha3,
        public readonly ?string $currencyCode,
        public readonly ?string $currencyName,
        public readonly ?string $currencySign,
        public readonly ?string $phoneCode,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            isoAlpha2: $data['iso_alpha2'],
            isoAlpha3: $data['iso_alpha3'] ?? null,
            currencyCode: $data['currency_code'] ?? null,
            currencyName: $data['currency_name'] ?? null,
            currencySign: $data['currency_sign'] ?? null,
            phoneCode: $data['phone_code'] ?? null,
        );
    }
}
