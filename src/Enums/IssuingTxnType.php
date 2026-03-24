<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum IssuingTxnType: string
{
    case Sale = 'SALE';
    case Credit = 'CREDIT';
    case Reversal = 'REVERSAL';
    case Refund = 'REFUND';
    case Chargeback = 'CHARGEBACK';
    case Adjustment = 'ADJUSTMENT';
    case AtmFee = 'ATM_FEE';
    case AtmWithdrawal = 'ATM_WITHDRAWAL';
}
