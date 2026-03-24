<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum PayoutMethodCategory: string
{
    case Bank = 'bank';
    case Cash = 'cash';
    case Card = 'card';
    case EWallet = 'ewallet';
    case RapydWallet = 'rapyd_ewallet';
}
