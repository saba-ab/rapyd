<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum PlanInterval: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';
}
