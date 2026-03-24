<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\IssuingTxnType;

final class CardTransaction implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?float $amount,
        public readonly ?string $currency,
        public readonly ?string $authCode,
        public readonly ?string $bin,
        public readonly ?string $last4,
        public readonly ?string $cardId,
        public readonly ?string $cardProgram,
        public readonly ?string $cardAuthorization,
        public readonly ?IssuingTxnType $issuingTxnType,
        public readonly ?float $originalTxnAmount,
        public readonly ?string $originalTxnCurrency,
        public readonly ?string $merchantCategoryCode,
        public readonly ?string $merchantNameLocation,
        public readonly ?string $walletTransactionId,
        public readonly ?Carbon $createdAt,
        public readonly ?string $posEntryMode,
        public readonly ?string $authorizationApprovedBy,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            amount: isset($data['amount']) ? (float) $data['amount'] : null,
            currency: $data['currency'] ?? null,
            authCode: $data['auth_code'] ?? null,
            bin: $data['bin'] ?? null,
            last4: $data['last4'] ?? null,
            cardId: $data['card_id'] ?? null,
            cardProgram: $data['card_program'] ?? null,
            cardAuthorization: $data['card_authorization'] ?? null,
            issuingTxnType: isset($data['issuing_txn_type']) ? IssuingTxnType::tryFrom($data['issuing_txn_type']) : null,
            originalTxnAmount: isset($data['original_txn_amount']) ? (float) $data['original_txn_amount'] : null,
            originalTxnCurrency: $data['original_txn_currency'] ?? null,
            merchantCategoryCode: $data['merchant_category_code'] ?? null,
            merchantNameLocation: $data['merchant_name_location'] ?? null,
            walletTransactionId: $data['wallet_transaction_id'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            posEntryMode: $data['pos_entry_mode'] ?? null,
            authorizationApprovedBy: $data['authorization_approved_by'] ?? null,
        );
    }
}
