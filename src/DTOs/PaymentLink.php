<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\CheckoutPageStatus;

final class PaymentLink implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?float $amount,
        public readonly ?string $currency,
        public readonly ?string $country,
        public readonly ?bool $amountIsEditable,
        public readonly ?string $merchantReferenceId,
        public readonly ?CheckoutPageStatus $status,
        public readonly ?string $redirectUrl,
        public readonly ?string $language,
        public readonly ?int $maxPayments,
        public readonly ?array $template,
        public readonly ?array $metadata,
        public readonly ?Carbon $createdAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            amount: isset($data['amount']) ? (float) $data['amount'] : null,
            currency: $data['currency'] ?? null,
            country: $data['country'] ?? null,
            amountIsEditable: $data['amount_is_editable'] ?? null,
            merchantReferenceId: $data['merchant_reference_id'] ?? null,
            status: isset($data['status']) ? CheckoutPageStatus::tryFrom($data['status']) : null,
            redirectUrl: $data['redirect_url'] ?? null,
            language: $data['language'] ?? null,
            maxPayments: $data['max_payments'] ?? null,
            template: $data['template'] ?? null,
            metadata: $data['metadata'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
        );
    }
}
