<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum EntityType: string
{
    case Individual = 'individual';
    case Company = 'company';
}
