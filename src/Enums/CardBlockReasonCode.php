<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum CardBlockReasonCode: string
{
    case Stolen = 'STO';
    case Lost = 'LOS';
    case Fraud = 'FRD';
    case Canceled = 'CAN';
    case Locked = 'LOC';
}
