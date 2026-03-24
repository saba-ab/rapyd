<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;

final class Product implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?string $name,
        public readonly ?string $type,
        public readonly bool $active,
        public readonly ?string $description,
        public readonly ?array $metadata,
        public readonly ?Carbon $createdAt,
        public readonly ?Carbon $updatedAt,
        public readonly ?string $statementDescriptor,
        public readonly ?string $unitLabel,
        public readonly ?array $images,
        public readonly ?bool $shippable,
        public readonly ?string $url,
        public readonly ?array $packageDimensions,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            name: $data['name'] ?? null,
            type: $data['type'] ?? null,
            active: $data['active'] ?? false,
            description: $data['description'] ?? null,
            metadata: $data['metadata'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::createFromTimestamp($data['updated_at']) : null,
            statementDescriptor: $data['statement_descriptor'] ?? null,
            unitLabel: $data['unit_label'] ?? null,
            images: $data['images'] ?? null,
            shippable: $data['shippable'] ?? null,
            url: $data['url'] ?? null,
            packageDimensions: $data['package_dimensions'] ?? null,
        );
    }
}
