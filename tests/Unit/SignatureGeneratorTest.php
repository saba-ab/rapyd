<?php

declare(strict_types=1);

use Sabaab\Rapyd\Client\SignatureGenerator;

it('generates a valid HMAC-SHA256 signature', function () {
    $generator = new SignatureGenerator('rak_test_123', 'rsk_test_abc');
    $signature = $generator->generate('get', '/v1/data/countries', 'abcd1234', '1700000000', '');

    expect($signature)->toBeString()->not->toBeEmpty();
});

it('produces deterministic output for the same inputs', function () {
    $generator = new SignatureGenerator('rak_test', 'rsk_test');
    $sig1 = $generator->generate('get', '/v1/data/countries', 'salt1234', '1700000000', '');
    $sig2 = $generator->generate('get', '/v1/data/countries', 'salt1234', '1700000000', '');

    expect($sig1)->toBe($sig2);
});

it('uses empty string for GET body, not curly braces', function () {
    $generator = new SignatureGenerator('rak_test', 'rsk_test');
    $sigEmpty = $generator->generate('get', '/v1/data/countries', 'salt', '123', '');
    $sigBraces = $generator->generate('get', '/v1/data/countries', 'salt', '123', '{}');

    expect($sigEmpty)->not->toBe($sigBraces);
});

it('produces different signatures for different HTTP methods', function () {
    $generator = new SignatureGenerator('rak_test', 'rsk_test');
    $sigGet = $generator->generate('get', '/v1/payments', 'salt', '123', '');
    $sigPost = $generator->generate('post', '/v1/payments', 'salt', '123', '');

    expect($sigGet)->not->toBe($sigPost);
});

it('produces different signatures for different URL paths', function () {
    $generator = new SignatureGenerator('rak_test', 'rsk_test');
    $sig1 = $generator->generate('get', '/v1/payments', 'salt', '123', '');
    $sig2 = $generator->generate('get', '/v1/customers', 'salt', '123', '');

    expect($sig1)->not->toBe($sig2);
});

it('produces a valid base64-encoded hex string', function () {
    $generator = new SignatureGenerator('rak_test', 'rsk_test');
    $signature = $generator->generate('post', '/v1/payments', 'salt1234', '1700000000', '{"amount":100}');

    $decoded = base64_decode($signature, true);
    expect($decoded)->not->toBeFalse();
    expect($decoded)->toMatch('/^[a-f0-9]{64}$/');
});

it('verifies the exact signature computation', function () {
    $generator = new SignatureGenerator('rak_test_key', 'rsk_test_secret');

    $httpMethod = 'get';
    $urlPath = '/v1/data/countries';
    $salt = 'abcdef1234567890';
    $timestamp = '1700000000';
    $bodyString = '';

    $toSign = $httpMethod.$urlPath.$salt.$timestamp.'rak_test_key'.'rsk_test_secret'.$bodyString;
    $expectedHmac = hash_hmac('sha256', $toSign, 'rsk_test_secret');
    $expectedSignature = base64_encode($expectedHmac);

    $actual = $generator->generate($httpMethod, $urlPath, $salt, $timestamp, $bodyString);

    expect($actual)->toBe($expectedSignature);
});

it('generates a webhook signature without HTTP method', function () {
    $generator = new SignatureGenerator('rak_test', 'rsk_test');

    $requestSig = $generator->generate('post', '/webhook', 'salt', '123', '{"type":"PAYMENT_COMPLETED"}');
    $webhookSig = $generator->generateForWebhook('/webhook', 'salt', '123', '{"type":"PAYMENT_COMPLETED"}');

    expect($requestSig)->not->toBe($webhookSig);
});

it('generates a 16-character hex salt', function () {
    $generator = new SignatureGenerator('rak_test', 'rsk_test');
    $salt = $generator->generateSalt();

    expect($salt)->toHaveLength(16)->toMatch('/^[a-f0-9]{16}$/');
});
