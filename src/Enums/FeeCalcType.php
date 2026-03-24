<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum FeeCalcType: string
{
    case Net = 'net';
    case Gross = 'gross';
}
