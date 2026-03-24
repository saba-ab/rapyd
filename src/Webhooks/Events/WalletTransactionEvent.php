<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Webhooks\Events;

final class WalletTransactionEvent
{
    public function __construct(
        public readonly string $type,
        public readonly array $data,
        public readonly string $webhookId,
        public readonly int $timestamp,
        public readonly string $triggerOperationId,
    ) {}
}
