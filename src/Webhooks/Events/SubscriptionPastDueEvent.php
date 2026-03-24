<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Webhooks\Events;

use Sabaab\Rapyd\DTOs\Subscription;

final class SubscriptionPastDueEvent
{
    public readonly Subscription $subscription;

    public function __construct(
        public readonly string $type,
        public readonly array $data,
        public readonly string $webhookId,
        public readonly int $timestamp,
        public readonly string $triggerOperationId,
    ) {
        $this->subscription = Subscription::fromArray($this->data);
    }
}
