<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Webhooks\Events;

use Sabaab\Rapyd\DTOs\Refund;

final class PaymentRefundRejectedEvent
{
    public readonly Refund $refund;

    public function __construct(
        public readonly string $type,
        public readonly array $data,
        public readonly string $webhookId,
        public readonly int $timestamp,
        public readonly string $triggerOperationId,
    ) {
        $this->refund = Refund::fromArray($this->data);
    }
}
