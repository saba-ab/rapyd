<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\CardBlockReasonCode;
use Sabaab\Rapyd\Enums\CardStatus;

final class Card implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?array $ewalletContact,
        public readonly ?CardStatus $status,
        public readonly ?string $cardId,
        public readonly ?string $cardNumber,
        public readonly ?string $cardProgram,
        public readonly ?string $expirationMonth,
        public readonly ?string $expirationYear,
        public readonly ?string $cvv,
        public readonly ?string $pin,
        public readonly ?array $metadata,
        public readonly ?Carbon $createdAt,
        public readonly ?Carbon $activatedAt,
        public readonly ?CardBlockReasonCode $blockedReason,
        public readonly ?string $countryIso,
        public readonly ?Carbon $assignedAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            ewalletContact: $data['ewallet_contact'] ?? null,
            status: isset($data['status']) ? CardStatus::tryFrom($data['status']) : null,
            cardId: $data['card_id'] ?? null,
            cardNumber: $data['card_number'] ?? null,
            cardProgram: $data['card_program'] ?? null,
            expirationMonth: $data['expiration_month'] ?? null,
            expirationYear: $data['expiration_year'] ?? null,
            cvv: $data['cvv'] ?? null,
            pin: $data['pin'] ?? null,
            metadata: $data['metadata'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            activatedAt: isset($data['activated_at']) ? Carbon::createFromTimestamp($data['activated_at']) : null,
            blockedReason: isset($data['blocked_reason']) ? CardBlockReasonCode::tryFrom($data['blocked_reason']) : null,
            countryIso: $data['country_iso'] ?? null,
            assignedAt: isset($data['assigned_at']) ? Carbon::createFromTimestamp($data['assigned_at']) : null,
        );
    }
}
