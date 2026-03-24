<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Client;

final class SignatureGenerator
{
    public function __construct(
        private readonly string $accessKey,
        private readonly string $secretKey,
    ) {}

    public function generate(
        string $httpMethod,
        string $urlPath,
        string $salt,
        string $timestamp,
        string $bodyString,
    ): string {
        $toSign = $httpMethod.$urlPath.$salt.$timestamp.$this->accessKey.$this->secretKey.$bodyString;
        $hmac = hash_hmac('sha256', $toSign, $this->secretKey);

        return base64_encode($hmac);
    }

    public function generateForWebhook(
        string $urlPath,
        string $salt,
        string $timestamp,
        string $bodyString,
    ): string {
        $toSign = $urlPath.$salt.$timestamp.$this->accessKey.$this->secretKey.$bodyString;
        $hmac = hash_hmac('sha256', $toSign, $this->secretKey);

        return base64_encode($hmac);
    }

    public function generateSalt(): string
    {
        return bin2hex(random_bytes(8));
    }
}
