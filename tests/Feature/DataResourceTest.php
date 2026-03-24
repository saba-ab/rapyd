<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Sabaab\Rapyd\DTOs\Country;
use Sabaab\Rapyd\DTOs\FxRate;
use Sabaab\Rapyd\Facades\Rapyd;

it('returns array of Country DTOs from countries()', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/data/countries*' => Http::response([
            'status' => ['status' => 'SUCCESS', 'error_code' => ''],
            'data' => [
                ['id' => 1, 'name' => 'United States', 'iso_alpha2' => 'US', 'iso_alpha3' => 'USA', 'currency_code' => 'USD'],
                ['id' => 2, 'name' => 'United Kingdom', 'iso_alpha2' => 'GB', 'iso_alpha3' => 'GBR', 'currency_code' => 'GBP'],
            ],
        ]),
    ]);

    $countries = Rapyd::data()->countries();

    expect($countries)->toBeArray()->toHaveCount(2);
    expect($countries[0])->toBeInstanceOf(Country::class);
    expect($countries[0]->isoAlpha2)->toBe('US');
    expect($countries[1]->isoAlpha2)->toBe('GB');
});

it('returns FxRate DTO from fxRate()', function () {
    Http::fake([
        'sandboxapi.rapyd.net/v1/rates/fxrate*' => Http::response([
            'status' => ['status' => 'SUCCESS', 'error_code' => ''],
            'data' => [
                'action_type' => 'payment',
                'buy_currency' => 'EUR',
                'sell_currency' => 'USD',
                'fixed_side' => 'buy',
                'buy_amount' => 100.0,
                'sell_amount' => 110.50,
                'rate' => 1.105,
            ],
        ]),
    ]);

    $rate = Rapyd::data()->fxRate(['buy_currency' => 'EUR', 'sell_currency' => 'USD', 'fixed_side' => 'buy', 'buy_amount' => 100]);

    expect($rate)->toBeInstanceOf(FxRate::class);
    expect($rate->buyCurrency)->toBe('EUR');
    expect($rate->sellCurrency)->toBe('USD');
    expect($rate->rate)->toBe(1.105);
});
