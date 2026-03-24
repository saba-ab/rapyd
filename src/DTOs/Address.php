<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;

final class Address implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly ?string $id,
        public readonly ?string $name,
        public readonly ?string $line1,
        public readonly ?string $line2,
        public readonly ?string $line3,
        public readonly ?string $city,
        public readonly ?string $state,
        public readonly ?string $country,
        public readonly ?string $zip,
        public readonly ?string $phoneNumber,
        public readonly ?array $metadata,
        public readonly ?string $canton,
        public readonly ?string $district,
        public readonly ?Carbon $createdAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            line1: $data['line_1'] ?? null,
            line2: $data['line_2'] ?? null,
            line3: $data['line_3'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            country: $data['country'] ?? null,
            zip: $data['zip'] ?? null,
            phoneNumber: $data['phone_number'] ?? null,
            metadata: $data['metadata'] ?? null,
            canton: $data['canton'] ?? null,
            district: $data['district'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
        );
    }
}
