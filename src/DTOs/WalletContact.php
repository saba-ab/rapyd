<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\WalletContactType;

final class WalletContact implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $email,
        public readonly ?string $phoneNumber,
        public readonly ?WalletContactType $contactType,
        public readonly ?string $dateOfBirth,
        public readonly ?string $country,
        public readonly ?string $identificationType,
        public readonly ?string $identificationNumber,
        public readonly ?array $metadata,
        public readonly ?Carbon $createdAt,
        public readonly ?array $businessDetails,
        public readonly ?Address $address,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            email: $data['email'] ?? null,
            phoneNumber: $data['phone_number'] ?? null,
            contactType: isset($data['contact_type']) ? WalletContactType::tryFrom($data['contact_type']) : null,
            dateOfBirth: $data['date_of_birth'] ?? null,
            country: $data['country'] ?? null,
            identificationType: $data['identification_type'] ?? null,
            identificationNumber: $data['identification_number'] ?? null,
            metadata: $data['metadata'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            businessDetails: $data['business_details'] ?? null,
            address: isset($data['address']) ? Address::fromArray($data['address']) : null,
        );
    }
}
