<?php

declare(strict_types=1);

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\Facades\Rapyd;
use Sabaab\Rapyd\Rapyd as RapydManager;

it('registers RapydClient as a singleton', function () {
    $instance1 = app(RapydClient::class);
    $instance2 = app(RapydClient::class);

    expect($instance1)->toBeInstanceOf(RapydClient::class);
    expect($instance1)->toBe($instance2);
});

it('registers Rapyd manager as a singleton', function () {
    $instance1 = app(RapydManager::class);
    $instance2 = app(RapydManager::class);

    expect($instance1)->toBeInstanceOf(RapydManager::class);
    expect($instance1)->toBe($instance2);
});

it('resolves the Facade to the Rapyd manager', function () {
    $client = Rapyd::client();

    expect($client)->toBeInstanceOf(RapydClient::class);
});

it('loads the rapyd config with test access key', function () {
    expect(config('rapyd.access_key'))->toBe('rak_test_1234567890');
});

it('loads the rapyd config with sandbox enabled', function () {
    expect(config('rapyd.sandbox'))->toBeTrue();
});

it('loads the rapyd config with sandbox base URL', function () {
    expect(config('rapyd.base_url.sandbox'))->toBe('https://sandboxapi.rapyd.net');
});
