<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum EscrowStatus: string
{
    case Pending = 'pending';
    case Released = 'released';
    case PartiallyReleased = 'partially_released';
}
