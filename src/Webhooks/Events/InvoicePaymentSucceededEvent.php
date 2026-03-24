<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Webhooks\Events;

use Sabaab\Rapyd\DTOs\Invoice;

final class InvoicePaymentSucceededEvent
{
    public readonly Invoice $invoice;

    public function __construct(
        public readonly string $type,
        public readonly array $data,
        public readonly string $webhookId,
        public readonly int $timestamp,
        public readonly string $triggerOperationId,
    ) {
        $this->invoice = Invoice::fromArray($this->data);
    }
}
