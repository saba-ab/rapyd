<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Sabaab\Rapyd\Webhooks\WebhookController;
use Sabaab\Rapyd\Webhooks\WebhookMiddleware;

Route::post(config('rapyd.webhook.path', '/rapyd/webhook'), WebhookController::class)
    ->middleware(WebhookMiddleware::class)
    ->name('rapyd.webhook');
