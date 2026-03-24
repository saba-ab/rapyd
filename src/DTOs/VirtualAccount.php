<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;

final class VirtualAccount implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?string $merchantReferenceId,
        public readonly ?string $ewallet,
        public readonly ?string $countryIso,
        public readonly ?string $currency,
        public readonly ?string $status,
        public readonly ?string $description,
        public readonly ?array $metadata,
        public readonly ?Carbon $createdAt,
        public readonly ?Carbon $updatedAt,
        public readonly ?array $transactions,
        public readonly ?string $requestCurrency,
        public readonly ?array $bankAccount,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            merchantReferenceId: $data['merchant_reference_id'] ?? null,
            ewallet: $data['ewallet'] ?? null,
            countryIso: $data['country_iso'] ?? null,
            currency: $data['currency'] ?? null,
            status: $data['status'] ?? null,
            description: $data['description'] ?? null,
            metadata: $data['metadata'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::createFromTimestamp($data['updated_at']) : null,
            transactions: $data['transactions'] ?? null,
            requestCurrency: $data['request_currency'] ?? null,
            bankAccount: $data['bank_account'] ?? null,
        );
    }
}
