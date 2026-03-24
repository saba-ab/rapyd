<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\EntityType;

final class Beneficiary implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?string $category,
        public readonly ?EntityType $entityType,
        public readonly ?string $country,
        public readonly ?string $currency,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $companyName,
        public readonly ?string $merchantReferenceId,
        public readonly ?array $metadata,
        public readonly ?string $paymentType,
        public readonly ?string $defaultPayoutMethodType,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            category: $data['category'] ?? null,
            entityType: isset($data['entity_type']) ? EntityType::tryFrom($data['entity_type']) : null,
            country: $data['country'] ?? null,
            currency: $data['currency'] ?? null,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            companyName: $data['company_name'] ?? null,
            merchantReferenceId: $data['merchant_reference_id'] ?? null,
            metadata: $data['metadata'] ?? null,
            paymentType: $data['payment_type'] ?? null,
            defaultPayoutMethodType: $data['default_payout_method_type'] ?? null,
        );
    }
}
