<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\PayoutStatus;

final class Payout implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly float $amount,
        public readonly ?string $currency,
        public readonly ?string $payoutCurrency,
        public readonly ?float $senderAmount,
        public readonly ?string $senderCurrency,
        public readonly ?PayoutStatus $status,
        public readonly ?array $beneficiary,
        public readonly ?array $sender,
        public readonly ?string $payoutType,
        public readonly ?string $payoutMethodType,
        public readonly ?string $ewallet,
        public readonly ?string $merchantReferenceId,
        public readonly ?float $fxRate,
        public readonly ?Carbon $expiration,
        public readonly ?array $metadata,
        public readonly ?Carbon $createdAt,
        public readonly ?string $description,
        public readonly ?array $payoutFees,
        public readonly ?array $instructions,
        public readonly ?string $error,
        public readonly ?Carbon $paidAt,
        public readonly ?string $identifierType,
        public readonly ?string $identifierValue,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            amount: (float) $data['amount'],
            currency: $data['currency'] ?? null,
            payoutCurrency: $data['payout_currency'] ?? null,
            senderAmount: isset($data['sender_amount']) ? (float) $data['sender_amount'] : null,
            senderCurrency: $data['sender_currency'] ?? null,
            status: isset($data['status']) ? PayoutStatus::tryFrom($data['status']) : null,
            beneficiary: $data['beneficiary'] ?? null,
            sender: $data['sender'] ?? null,
            payoutType: $data['payout_type'] ?? null,
            payoutMethodType: $data['payout_method_type'] ?? null,
            ewallet: $data['ewallet'] ?? null,
            merchantReferenceId: $data['merchant_reference_id'] ?? null,
            fxRate: isset($data['fx_rate']) ? (float) $data['fx_rate'] : null,
            expiration: isset($data['expiration']) ? Carbon::createFromTimestamp($data['expiration']) : null,
            metadata: $data['metadata'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            description: $data['description'] ?? null,
            payoutFees: $data['payout_fees'] ?? null,
            instructions: $data['instructions'] ?? null,
            error: $data['error'] ?? null,
            paidAt: isset($data['paid_at']) ? Carbon::createFromTimestamp($data['paid_at']) : null,
            identifierType: $data['identifier_type'] ?? null,
            identifierValue: $data['identifier_value'] ?? null,
        );
    }
}
