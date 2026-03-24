<?php

namespace Sabaab\Rapyd;

use Sabaab\Rapyd\Commands\RapydCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class RapydServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('rapyd')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_rapyd_table')
            ->hasCommand(RapydCommand::class);
    }
}
