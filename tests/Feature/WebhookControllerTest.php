<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use Sabaab\Rapyd\Webhooks\Events\PaymentCompletedEvent;
use Sabaab\Rapyd\Webhooks\Events\RapydWebhookReceived;
use Sabaab\Rapyd\Webhooks\Events\RefundCompletedEvent;

function webhookSignature(string $urlPath, string $salt, string $timestamp, string $body): string
{
    $accessKey = 'rak_test_1234567890';
    $secretKey = 'rsk_test_abcdefghij';
    $toSign = $urlPath.$salt.$timestamp.$accessKey.$secretKey.$body;

    return base64_encode(hash_hmac('sha256', $toSign, $secretKey));
}

function postWebhook(array $payload, ?array $headerOverrides = null): TestResponse
{
    $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $salt = 'test_salt_123456';
    $timestamp = (string) time();
    $urlPath = '/rapyd/webhook';
    $signature = webhookSignature($urlPath, $salt, $timestamp, $body);

    $headers = $headerOverrides ?? [
        'salt' => $salt,
        'timestamp' => $timestamp,
        'signature' => $signature,
    ];

    return test()->postJson($urlPath, $payload, array_merge(['Content-Type' => 'application/json'], $headers));
}

it('dispatches specific event and catch-all for valid PAYMENT_COMPLETED webhook', function () {
    Event::fake();

    $response = postWebhook([
        'id' => 'wh_123',
        'type' => 'PAYMENT_COMPLETED',
        'data' => ['id' => 'payment_abc', 'amount' => 100, 'currency_code' => 'USD', 'paid' => true, 'captured' => true, 'refunded' => false, 'refunded_amount' => 0, 'is_partial' => false],
        'trigger_operation_id' => 'op_123',
        'created_at' => 1700000000,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'ok']);

    Event::assertDispatched(RapydWebhookReceived::class);
    Event::assertDispatched(PaymentCompletedEvent::class);
});

it('dispatches only catch-all for unknown webhook type', function () {
    Event::fake();

    $response = postWebhook([
        'id' => 'wh_unknown',
        'type' => 'SOME_FUTURE_EVENT_TYPE',
        'data' => ['foo' => 'bar'],
        'trigger_operation_id' => 'op_456',
        'created_at' => 1700000000,
    ]);

    $response->assertOk();

    Event::assertDispatched(RapydWebhookReceived::class);
    Event::assertNotDispatched(PaymentCompletedEvent::class);
});

it('returns 403 for invalid signature', function () {
    Event::fake();

    $response = test()->postJson('/rapyd/webhook', [
        'id' => 'wh_bad',
        'type' => 'PAYMENT_COMPLETED',
        'data' => [],
    ], [
        'Content-Type' => 'application/json',
        'salt' => 'bad_salt',
        'timestamp' => (string) time(),
        'signature' => 'invalid_signature_value',
    ]);

    $response->assertStatus(403);
    Event::assertNotDispatched(RapydWebhookReceived::class);
    Event::assertNotDispatched(PaymentCompletedEvent::class);
});

it('returns 403 for missing signature headers', function () {
    Event::fake();

    $response = test()->postJson('/rapyd/webhook', [
        'id' => 'wh_missing',
        'type' => 'PAYMENT_COMPLETED',
        'data' => [],
    ]);

    $response->assertStatus(403);
    Event::assertNotDispatched(RapydWebhookReceived::class);
});

it('returns 403 for expired timestamp', function () {
    Event::fake();

    $body = json_encode([
        'id' => 'wh_expired',
        'type' => 'PAYMENT_COMPLETED',
        'data' => [],
        'trigger_operation_id' => 'op_exp',
        'created_at' => 1700000000,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $salt = 'expired_salt_1234';
    $timestamp = (string) (time() - 300);
    $signature = webhookSignature('/rapyd/webhook', $salt, $timestamp, $body);

    $response = test()->postJson('/rapyd/webhook', json_decode($body, true), [
        'Content-Type' => 'application/json',
        'salt' => $salt,
        'timestamp' => $timestamp,
        'signature' => $signature,
    ]);

    $response->assertStatus(403);
    Event::assertNotDispatched(RapydWebhookReceived::class);
});

it('dispatched event contains correct data', function () {
    Event::fake();

    postWebhook([
        'id' => 'wh_refund',
        'type' => 'REFUND_COMPLETED',
        'data' => ['id' => 'refund_xxx', 'amount' => 25, 'currency' => 'USD', 'status' => 'Completed', 'proportional_refund' => false],
        'trigger_operation_id' => 'op_ref',
        'created_at' => 1700000000,
    ]);

    Event::assertDispatched(RefundCompletedEvent::class, function ($event) {
        return $event->refund->id === 'refund_xxx'
            && $event->webhookId === 'wh_refund'
            && $event->type === 'REFUND_COMPLETED'
            && $event->triggerOperationId === 'op_ref';
    });
});
