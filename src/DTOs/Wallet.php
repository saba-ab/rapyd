<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;

final class Wallet implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?string $type,
        public readonly ?string $status,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $email,
        public readonly ?string $phoneNumber,
        public readonly ?string $ewalletReferenceId,
        public readonly ?string $category,
        public readonly ?array $metadata,
        public readonly ?array $accounts,
        public readonly ?array $contacts,
        public readonly ?Carbon $createdAt,
        public readonly ?Carbon $updatedAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            type: $data['type'] ?? null,
            status: $data['status'] ?? null,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            email: $data['email'] ?? null,
            phoneNumber: $data['phone_number'] ?? null,
            ewalletReferenceId: $data['ewallet_reference_id'] ?? null,
            category: $data['category'] ?? null,
            metadata: $data['metadata'] ?? null,
            accounts: $data['accounts'] ?? null,
            contacts: $data['contacts'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::createFromTimestamp($data['updated_at']) : null,
        );
    }
}
