<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum PayoutStatus: string
{
    case Created = 'Created';
    case Confirmation = 'Confirmation';
    case Completed = 'Completed';
    case Canceled = 'Canceled';
    case Error = 'Error';
    case Expired = 'Expired';
    case Returned = 'Returned';
}
