<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\PaymentMethodCategory;

final class PaymentMethod implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly ?string $id,
        public readonly string $type,
        public readonly ?string $name,
        public readonly ?PaymentMethodCategory $category,
        public readonly ?string $image,
        public readonly ?string $country,
        public readonly ?array $currencies,
        public readonly ?bool $isRefundable,
        public readonly ?bool $isTokenizable,
        public readonly ?bool $isExpirable,
        public readonly ?int $minimumExpirationSeconds,
        public readonly ?int $maximumExpirationSeconds,
        public readonly ?array $metadata,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'] ?? null,
            type: $data['type'],
            name: $data['name'] ?? null,
            category: isset($data['category']) ? PaymentMethodCategory::tryFrom($data['category']) : null,
            image: $data['image'] ?? null,
            country: $data['country'] ?? null,
            currencies: $data['currencies'] ?? null,
            isRefundable: $data['is_refundable'] ?? null,
            isTokenizable: $data['is_tokenizable'] ?? null,
            isExpirable: $data['is_expirable'] ?? null,
            minimumExpirationSeconds: $data['minimum_expiration_seconds'] ?? null,
            maximumExpirationSeconds: $data['maximum_expiration_seconds'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }
}
