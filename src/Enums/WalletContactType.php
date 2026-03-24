<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum WalletContactType: string
{
    case Personal = 'personal';
    case Business = 'business';
}
