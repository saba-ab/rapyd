<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum CardStatus: string
{
    case Active = 'ACT';
    case Inactive = 'INA';
    case Blocked = 'BLO';
    case Expired = 'EXP';
}
