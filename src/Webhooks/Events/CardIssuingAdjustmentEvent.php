<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Webhooks\Events;

use Sabaab\Rapyd\DTOs\CardTransaction;

final class CardIssuingAdjustmentEvent
{
    public readonly CardTransaction $transaction;

    public function __construct(
        public readonly string $type,
        public readonly array $data,
        public readonly string $webhookId,
        public readonly int $timestamp,
        public readonly string $triggerOperationId,
    ) {
        $this->transaction = CardTransaction::fromArray($this->data);
    }
}
