<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;
use Sabaab\Rapyd\Enums\CheckoutPageStatus;

final class Checkout implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?string $country,
        public readonly ?string $currency,
        public readonly ?float $amount,
        public readonly ?CheckoutPageStatus $status,
        public readonly ?array $payment,
        public readonly ?string $redirectUrl,
        public readonly ?string $completeCheckoutUrl,
        public readonly ?string $errorCheckoutUrl,
        public readonly ?string $merchantReferenceId,
        public readonly ?string $language,
        public readonly ?Carbon $pageExpiration,
        public readonly ?Carbon $createdAt,
        public readonly ?array $metadata,
        public readonly ?string $customer,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            country: $data['country'] ?? null,
            currency: $data['currency'] ?? null,
            amount: isset($data['amount']) ? (float) $data['amount'] : null,
            status: isset($data['status']) ? CheckoutPageStatus::tryFrom($data['status']) : null,
            payment: $data['payment'] ?? null,
            redirectUrl: $data['redirect_url'] ?? null,
            completeCheckoutUrl: $data['complete_checkout_url'] ?? null,
            errorCheckoutUrl: $data['error_checkout_url'] ?? null,
            merchantReferenceId: $data['merchant_reference_id'] ?? null,
            language: $data['language'] ?? null,
            pageExpiration: isset($data['page_expiration']) ? Carbon::createFromTimestamp($data['page_expiration']) : null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            metadata: $data['metadata'] ?? null,
            customer: $data['customer'] ?? null,
        );
    }
}
