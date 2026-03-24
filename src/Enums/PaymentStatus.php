<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum PaymentStatus: string
{
    case Active = 'ACT';
    case Closed = 'CLO';
    case Canceled = 'CAN';
    case Error = 'ERR';
    case Expired = 'EXP';
    case Reviewed = 'REV';
    case New = 'NEW';
}
