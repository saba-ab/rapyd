<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Canceled = 'canceled';
    case PastDue = 'past_due';
    case Trialing = 'trialing';
    case Unpaid = 'unpaid';
}
