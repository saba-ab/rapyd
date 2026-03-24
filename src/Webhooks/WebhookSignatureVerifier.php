<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Webhooks;

final class WebhookSignatureVerifier
{
    public function __construct(
        private readonly string $accessKey,
        private readonly string $secretKey,
    ) {}

    public static function fromConfig(array $config): static
    {
        return new self($config['access_key'], $config['secret_key']);
    }

    public function verify(
        string $urlPath,
        string $salt,
        string $timestamp,
        string $signature,
        string $body,
        int $tolerance = 60,
    ): bool {
        if (abs(time() - (int) $timestamp) > $tolerance) {
            return false;
        }

        $toSign = $urlPath.$salt.$timestamp.$this->accessKey.$this->secretKey.$body;
        $expectedSignature = base64_encode(hash_hmac('sha256', $toSign, $this->secretKey));

        return hash_equals($expectedSignature, $signature);
    }
}
