<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Commands;

use Illuminate\Console\Command;
use Sabaab\Rapyd\Enums\WebhookEventType;

final class WebhookSecretCommand extends Command
{
    protected $signature = 'rapyd:webhook-info';

    protected $description = 'Display webhook configuration information';

    public function handle(): int
    {
        $this->info('Rapyd Webhook Configuration');
        $this->newLine();

        $this->table(['Setting', 'Value'], [
            ['Webhook Path', config('rapyd.webhook.path', '/rapyd/webhook')],
            ['Route Name', 'rapyd.webhook'],
            ['Signature Tolerance', config('rapyd.webhook.tolerance', 60).' seconds'],
            ['Environment', config('rapyd.sandbox') ? 'Sandbox' : 'Production'],
            ['Access Key', config('rapyd.access_key') ? 'Yes (set)' : 'No (missing!)'],
            ['Secret Key', config('rapyd.secret_key') ? 'Yes (set)' : 'No (missing!)'],
        ]);

        $this->newLine();
        $this->info('Supported Webhook Event Types:');
        $this->newLine();

        $groups = [
            'Payment' => ['PAYMENT_'],
            'Refund' => ['REFUND_'],
            'Customer' => ['CUSTOMER_'],
            'Subscription' => ['CUSTOMER_SUBSCRIPTION_'],
            'Invoice' => ['INVOICE_'],
            'Payout' => ['PAYOUT_'],
            'Wallet' => ['WALLET_'],
            'Card Issuing' => ['CARD_'],
            'Verify' => ['VERIFY_'],
            'Virtual Account' => ['VIRTUAL_ACCOUNT_'],
        ];

        foreach ($groups as $domain => $prefixes) {
            $events = array_filter(
                WebhookEventType::cases(),
                fn (WebhookEventType $case) => collect($prefixes)->contains(
                    fn (string $prefix) => str_starts_with($case->value, $prefix)
                ),
            );

            if ($events !== []) {
                $this->line("  <comment>{$domain}</comment>");
                foreach ($events as $event) {
                    $this->line("    - {$event->value}");
                }
            }
        }

        $this->newLine();
        $this->line('Configure your webhook URL in the Rapyd Client Portal: https://dashboard.rapyd.net');

        return self::SUCCESS;
    }
}
