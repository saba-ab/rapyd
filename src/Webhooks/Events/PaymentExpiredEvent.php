<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Webhooks\Events;

use Sabaab\Rapyd\DTOs\Payment;

final class PaymentExpiredEvent
{
    public readonly Payment $payment;

    public function __construct(
        public readonly string $type,
        public readonly array $data,
        public readonly string $webhookId,
        public readonly int $timestamp,
        public readonly string $triggerOperationId,
    ) {
        $this->payment = Payment::fromArray($this->data);
    }
}
