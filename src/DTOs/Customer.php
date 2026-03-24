<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sabaab\Rapyd\DTOs\Concerns\HasFactory;

final class Customer implements \JsonSerializable, Arrayable
{
    use HasFactory;

    public function __construct(
        public readonly string $id,
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $phoneNumber,
        public readonly ?string $description,
        public readonly ?string $defaultPaymentMethod,
        public readonly ?string $businessVatId,
        public readonly ?string $invoicePrefix,
        public readonly ?string $ewallet,
        public readonly bool $delinquent,
        public readonly ?array $metadata,
        public readonly ?Carbon $createdAt,
        public readonly ?array $addresses,
        public readonly ?array $paymentMethods,
        public readonly ?array $subscriptions,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            phoneNumber: $data['phone_number'] ?? null,
            description: $data['description'] ?? null,
            defaultPaymentMethod: $data['default_payment_method'] ?? null,
            businessVatId: $data['business_vat_id'] ?? null,
            invoicePrefix: $data['invoice_prefix'] ?? null,
            ewallet: $data['ewallet'] ?? null,
            delinquent: $data['delinquent'] ?? false,
            metadata: $data['metadata'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::createFromTimestamp($data['created_at']) : null,
            addresses: $data['addresses'] ?? null,
            paymentMethods: $data['payment_methods'] ?? null,
            subscriptions: $data['subscriptions'] ?? null,
        );
    }
}
