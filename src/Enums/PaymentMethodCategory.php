<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum PaymentMethodCategory: string
{
    case Card = 'card';
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case BankRedirect = 'bank_redirect';
    case EWallet = 'ewallet';
}
