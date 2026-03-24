<?php

namespace Sabaab\Rapyd\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Sabaab\Rapyd\Facades\Rapyd;
use Sabaab\Rapyd\RapydServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            RapydServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Rapyd' => Rapyd::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('rapyd.access_key', 'rak_test_1234567890');
        $app['config']->set('rapyd.secret_key', 'rsk_test_abcdefghij');
        $app['config']->set('rapyd.sandbox', true);
    }
}
