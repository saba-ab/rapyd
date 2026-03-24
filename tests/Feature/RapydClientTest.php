<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\Client\RapydResponse;
use Sabaab\Rapyd\Client\SignatureGenerator;

function createClient(array $configOverrides = []): RapydClient
{
    $config = array_merge([
        'access_key' => 'rak_test_123',
        'secret_key' => 'rsk_test_abc',
        'timeout' => 30,
        'retry' => ['times' => 1, 'sleep' => 0],
    ], $configOverrides);

    return new RapydClient(
        new SignatureGenerator($config['access_key'], $config['secret_key']),
        'https://sandboxapi.rapyd.net',
        $config['access_key'],
        $config,
    );
}

function fakeSuccessResponse(array $data = []): array
{
    return [
        'status' => ['status' => 'SUCCESS', 'error_code' => '', 'message' => '', 'operation_id' => 'op_test'],
        'data' => $data,
    ];
}

function fakeSuccess(): void
{
    Http::fake([
        'sandboxapi.rapyd.net/*' => Http::response(fakeSuccessResponse(['id' => 'test_123'])),
    ]);
}

it('sends correct headers on GET request', function () {
    fakeSuccess();
    $client = createClient();
    $response = $client->get('/v1/data/countries');

    expect($response)->toBeInstanceOf(RapydResponse::class);
    expect($response->successful())->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->hasHeader('access_key', 'rak_test_123')
            && $request->hasHeader('salt')
            && $request->hasHeader('timestamp')
            && $request->hasHeader('signature')
            && $request->hasHeader('Content-Type', 'application/json');
    });
});

it('does not send idempotency header on GET request', function () {
    fakeSuccess();
    $client = createClient();
    $client->get('/v1/data/countries');

    Http::assertSent(function ($request) {
        return ! $request->hasHeader('idempotency');
    });
});

it('sends idempotency header on POST request', function () {
    fakeSuccess();
    $client = createClient();
    $client->post('/v1/payments', ['amount' => 100, 'currency' => 'USD']);

    Http::assertSent(function ($request) {
        return $request->hasHeader('idempotency')
            && strlen($request->header('idempotency')[0]) > 0;
    });
});

it('sends idempotency header on PUT request', function () {
    fakeSuccess();
    $client = createClient();
    $client->put('/v1/payments/pay_123', ['description' => 'updated']);

    Http::assertSent(function ($request) {
        return $request->hasHeader('idempotency');
    });
});

it('sends idempotency header on DELETE request', function () {
    fakeSuccess();
    $client = createClient();
    $client->delete('/v1/payments/pay_123');

    Http::assertSent(function ($request) {
        return $request->hasHeader('idempotency');
    });
});

it('includes body in POST request', function () {
    fakeSuccess();
    $client = createClient();
    $client->post('/v1/payments', ['amount' => 100, 'currency' => 'USD']);

    Http::assertSent(function ($request) {
        $body = $request->body();

        return str_contains($body, '"amount":100')
            && str_contains($body, '"currency":"USD"');
    });
});

it('sends empty body for POST with no data', function () {
    fakeSuccess();
    $client = createClient();
    $client->post('/v1/payments');

    Http::assertSent(function ($request) {
        return $request->body() === '';
    });
});

it('appends query params to GET URL', function () {
    fakeSuccess();
    $client = createClient();
    $client->get('/v1/payments', ['limit' => 10, 'page' => 2]);

    Http::assertSent(function ($request) {
        $url = $request->url();

        return str_contains($url, 'limit=10')
            && str_contains($url, 'page=2');
    });
});

it('returns RapydResponse that reports failed on error', function () {
    Http::fake([
        'sandboxapi.rapyd.net/*' => Http::response([
            'status' => ['status' => 'ERROR', 'error_code' => 'SOME_ERROR', 'message' => 'Bad request', 'operation_id' => 'op_err'],
        ], 400),
    ]);

    $client = createClient();
    $response = $client->get('/v1/data/countries');

    expect($response->failed())->toBeTrue();
    expect($response->errorCode())->toBe('SOME_ERROR');
});

it('retries on 500 responses', function () {
    Http::fake([
        'sandboxapi.rapyd.net/*' => Http::sequence()
            ->push(['status' => ['status' => 'ERROR', 'error_code' => 'SERVER_ERROR', 'message' => 'Internal']], 500)
            ->push(fakeSuccessResponse(['id' => 'retry_ok']), 200),
    ]);

    $client = createClient(['retry' => ['times' => 3, 'sleep' => 0]]);
    $response = $client->get('/v1/data/countries');

    expect($response->successful())->toBeTrue();
    expect($response->data()['id'])->toBe('retry_ok');
});

it('generates a valid signature for the request', function () {
    fakeSuccess();
    $client = createClient();
    $client->get('/v1/data/countries');

    Http::assertSent(function ($request) {
        $signature = $request->header('signature')[0];
        $decoded = base64_decode($signature, true);

        return $decoded !== false && preg_match('/^[a-f0-9]{64}$/', $decoded);
    });
});
