<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum WebhookStatus: string
{
    case New = 'NEW';
    case ReSent = 'RET';
    case Closed = 'CLO';
    case Error = 'ERR';
}
