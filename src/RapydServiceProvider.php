<?php

declare(strict_types=1);

namespace Sabaab\Rapyd;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\Client\SignatureGenerator;
use Sabaab\Rapyd\Commands\ListPaymentMethodsCommand;
use Sabaab\Rapyd\Commands\TestConnectionCommand;
use Sabaab\Rapyd\Commands\WebhookSecretCommand;
use Sabaab\Rapyd\Webhooks\WebhookSignatureVerifier;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class RapydServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('rapyd')
            ->hasConfigFile()
            ->hasRoute('webhooks')
            ->hasCommands([
                TestConnectionCommand::class,
                ListPaymentMethodsCommand::class,
                WebhookSecretCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(RapydClient::class, function ($app) {
            $config = $app['config']['rapyd'];

            return new RapydClient(
                new SignatureGenerator($config['access_key'], $config['secret_key']),
                $config['sandbox'] ? $config['base_url']['sandbox'] : $config['base_url']['production'],
                $config['access_key'],
                $config,
            );
        });

        $this->app->singleton(Rapyd::class, function ($app) {
            return new Rapyd($app->make(RapydClient::class));
        });

        $this->app->singleton(WebhookSignatureVerifier::class, function ($app) {
            $config = $app['config']['rapyd'];

            return new WebhookSignatureVerifier($config['access_key'], $config['secret_key']);
        });
    }
}
