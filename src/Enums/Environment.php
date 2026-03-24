<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum Environment: string
{
    case Sandbox = 'sandbox';
    case Production = 'production';
}
