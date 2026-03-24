<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum PaymentFlowType: string
{
    case Direct = 'direct';
    case Redirect = 'redirect';
    case EWalletPayer = 'ewallet_payer';
}
