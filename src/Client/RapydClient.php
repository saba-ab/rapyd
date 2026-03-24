<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Client;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

final class RapydClient
{
    public function __construct(
        private readonly SignatureGenerator $signatureGenerator,
        private readonly string $baseUrl,
        private readonly string $accessKey,
        private readonly array $config,
    ) {}

    public function get(string $path, array $query = []): RapydResponse
    {
        $urlPath = $path;
        if ($query !== []) {
            $urlPath .= '?'.http_build_query($query);
        }

        return $this->request('get', $urlPath, []);
    }

    public function post(string $path, array $body = []): RapydResponse
    {
        return $this->request('post', $path, $body);
    }

    public function put(string $path, array $body = []): RapydResponse
    {
        return $this->request('put', $path, $body);
    }

    public function delete(string $path, array $body = []): RapydResponse
    {
        return $this->request('delete', $path, $body);
    }

    private function request(string $method, string $urlPath, array $body): RapydResponse
    {
        $salt = $this->signatureGenerator->generateSalt();
        $timestamp = (string) time();
        $bodyString = $body !== [] ? json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';

        $signature = $this->signatureGenerator->generate(
            $method,
            $urlPath,
            $salt,
            $timestamp,
            $bodyString,
        );

        $headers = [
            'Content-Type' => 'application/json',
            'access_key' => $this->accessKey,
            'salt' => $salt,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ];

        if ($method !== 'get') {
            $headers['idempotency'] = $timestamp.$salt;
        }

        $timeout = $this->config['timeout'] ?? 30;
        $retryTimes = $this->config['retry']['times'] ?? 3;
        $retrySleep = $this->config['retry']['sleep'] ?? 100;

        $pending = Http::withHeaders($headers)
            ->baseUrl($this->baseUrl)
            ->timeout($timeout)
            ->retry(
                $retryTimes,
                $retrySleep,
                fn ($exception) => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && $exception->response->status() >= 500),
                throw: false,
            );

        $response = match ($method) {
            'get' => $pending->get($urlPath),
            'post' => $pending->withBody($bodyString, 'application/json')->post($urlPath),
            'put' => $pending->withBody($bodyString, 'application/json')->put($urlPath),
            'delete' => $pending->withBody($bodyString, 'application/json')->delete($urlPath),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };

        return new RapydResponse($response);
    }
}
