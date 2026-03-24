<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum DisputeStatus: string
{
    case Active = 'ACT';
    case Review = 'RVW';
    case PreArbitration = 'PRA';
    case Arbitration = 'ARB';
    case Loss = 'LOS';
    case Win = 'WIN';
    case Reverse = 'REV';
}
