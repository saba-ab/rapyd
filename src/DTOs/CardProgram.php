<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;

final class CardProgram implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?string $name,
        public readonly ?string $status,
        public readonly ?string $country,
        public readonly ?string $currency,
        public readonly ?array $metadata,
        public readonly ?Carbon $createdAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            name: $data['name'] ?? null,
            status: $data['status'] ?? null,
            country: $data['country'] ?? null,
            currency: $data['currency'] ?? null,
            metadata: $data['metadata'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
        );
    }
}
