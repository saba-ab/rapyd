<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum CheckoutPageStatus: string
{
    case New = 'NEW';
    case Done = 'DON';
    case Expired = 'EXP';
}
