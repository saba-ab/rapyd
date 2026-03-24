<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Paid = 'paid';
    case Uncollectible = 'uncollectible';
    case Void = 'void';
}
