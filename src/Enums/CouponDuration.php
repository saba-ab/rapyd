<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum CouponDuration: string
{
    case Forever = 'forever';
    case Repeating = 'repeating';
    case Once = 'once';
}
