<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum RefundStatus: string
{
    case Pending = 'Pending';
    case Completed = 'Completed';
    case Canceled = 'Canceled';
    case Error = 'Error';
    case Rejected = 'Rejected';
}
