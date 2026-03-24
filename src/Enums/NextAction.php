<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum NextAction: string
{
    case ThreeDSVerification = '3d_verification';
    case PendingConfirmation = 'pending_confirmation';
    case PendingCapture = 'pending_capture';
    case NotApplicable = 'not_applicable';
}
