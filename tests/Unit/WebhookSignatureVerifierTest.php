<?php

declare(strict_types=1);

use Sabaab\Rapyd\Webhooks\WebhookSignatureVerifier;

function computeWebhookSignature(string $urlPath, string $salt, string $timestamp, string $accessKey, string $secretKey, string $body): string
{
    $toSign = $urlPath.$salt.$timestamp.$accessKey.$secretKey.$body;

    return base64_encode(hash_hmac('sha256', $toSign, $secretKey));
}

it('passes verification with a valid signature', function () {
    $verifier = new WebhookSignatureVerifier('rak_test', 'rsk_test');
    $salt = 'abcd1234';
    $timestamp = (string) time();
    $body = '{"type":"PAYMENT_COMPLETED","data":{}}';
    $urlPath = '/rapyd/webhook';

    $signature = computeWebhookSignature($urlPath, $salt, $timestamp, 'rak_test', 'rsk_test', $body);

    expect($verifier->verify($urlPath, $salt, $timestamp, $signature, $body))->toBeTrue();
});

it('fails verification with wrong signature', function () {
    $verifier = new WebhookSignatureVerifier('rak_test', 'rsk_test');
    $timestamp = (string) time();

    expect($verifier->verify('/rapyd/webhook', 'salt', $timestamp, 'wrong_signature', '{}'))->toBeFalse();
});

it('fails verification with tampered body', function () {
    $verifier = new WebhookSignatureVerifier('rak_test', 'rsk_test');
    $salt = 'salt1234';
    $timestamp = (string) time();
    $originalBody = '{"type":"PAYMENT_COMPLETED"}';

    $signature = computeWebhookSignature('/rapyd/webhook', $salt, $timestamp, 'rak_test', 'rsk_test', $originalBody);

    $tamperedBody = '{"type":"PAYMENT_FAILED"}';
    expect($verifier->verify('/rapyd/webhook', $salt, $timestamp, $signature, $tamperedBody))->toBeFalse();
});

it('fails verification with expired timestamp', function () {
    $verifier = new WebhookSignatureVerifier('rak_test', 'rsk_test');
    $salt = 'salt1234';
    $timestamp = (string) (time() - 120);
    $body = '{"type":"PAYMENT_COMPLETED"}';

    $signature = computeWebhookSignature('/rapyd/webhook', $salt, $timestamp, 'rak_test', 'rsk_test', $body);

    expect($verifier->verify('/rapyd/webhook', $salt, $timestamp, $signature, $body, 60))->toBeFalse();
});

it('passes verification when timestamp is within tolerance', function () {
    $verifier = new WebhookSignatureVerifier('rak_test', 'rsk_test');
    $salt = 'salt1234';
    $timestamp = (string) (time() - 30);
    $body = '{"type":"PAYMENT_COMPLETED"}';

    $signature = computeWebhookSignature('/rapyd/webhook', $salt, $timestamp, 'rak_test', 'rsk_test', $body);

    expect($verifier->verify('/rapyd/webhook', $salt, $timestamp, $signature, $body, 60))->toBeTrue();
});

it('creates verifier from config array', function () {
    $verifier = WebhookSignatureVerifier::fromConfig([
        'access_key' => 'rak_cfg',
        'secret_key' => 'rsk_cfg',
    ]);

    $salt = 'salt1234';
    $timestamp = (string) time();
    $body = '{}';

    $signature = computeWebhookSignature('/webhook', $salt, $timestamp, 'rak_cfg', 'rsk_cfg', $body);

    expect($verifier->verify('/webhook', $salt, $timestamp, $signature, $body))->toBeTrue();
});
