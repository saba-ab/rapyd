<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\EntityType;

final class Sender implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?EntityType $entityType,
        public readonly ?string $country,
        public readonly ?string $currency,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $companyName,
        public readonly ?array $metadata,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            entityType: isset($data['entity_type']) ? EntityType::tryFrom($data['entity_type']) : null,
            country: $data['country'] ?? null,
            currency: $data['currency'] ?? null,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            companyName: $data['company_name'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }
}
