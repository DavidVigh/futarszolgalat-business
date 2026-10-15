<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PythonApiClient
{
    public function forward(string $method, string $endpoint, array $query = [], ?array $payload = null): array
    {
        $baseUrl = config('python-api.base_url');

        if (! is_string($baseUrl) || $baseUrl === '' || $endpoint === '') {
            throw new RuntimeException('A Python API nincs beallitva.');
        }

        $request = $this->request();
        $response = match (strtoupper($method)) {
            'GET' => $request->get($endpoint, $query),
            'POST' => $request->post($endpoint, $payload ?? []),
            'PUT' => $request->put($endpoint, $payload ?? []),
            'PATCH' => $request->patch($endpoint, $payload ?? []),
            'DELETE' => $request->delete($endpoint, $query),
            default => throw new RuntimeException('Nem tamogatott HTTP metodus.'),
        };

        if ($response->failed()) {
            throw new RuntimeException('A Python API lekerese sikertelen.', $response->status());
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    public function call(string $operation, array $payload): array
    {
        $baseUrl = config('python-api.base_url');
        $endpoint = config("python-api.endpoints.$operation");

        if (! is_string($baseUrl) || $baseUrl === '' || ! is_string($endpoint) || $endpoint === '') {
            throw new RuntimeException('A Python API nincs beallitva.');
        }

        $response = $this->request()->post($endpoint, $payload);

        if ($response->failed()) {
            throw new RuntimeException('A Python API adatrogzitese sikertelen.', $response->status());
        }

        $json = $response->json();

        return is_array($json) ? ($json['data'] ?? $json) : [];
    }

    public function get(string $operation, array $query = []): array
    {
        $baseUrl = config('python-api.base_url');
        $endpoint = config("python-api.endpoints.$operation");

        if (! is_string($baseUrl) || $baseUrl === '' || ! is_string($endpoint) || $endpoint === '') {
            throw new RuntimeException('A Python API nincs beallitva.');
        }

        foreach ($query as $key => $value) {
            $placeholder = '{'.$key.'}';

            if (str_contains($endpoint, $placeholder)) {
                $endpoint = str_replace($placeholder, rawurlencode((string) $value), $endpoint);
                unset($query[$key]);
            }
        }

        $response = $this->request()->get($endpoint, $query);

        if ($response->failed()) {
            throw new RuntimeException('A Python API lekerese sikertelen.', $response->status());
        }

        $json = $response->json();

        return is_array($json) ? ($json['data'] ?? $json) : [];
    }

    private function request(): PendingRequest
    {
        $request = Http::baseUrl(rtrim((string) config('python-api.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('python-api.timeout', 10));

        $token = config('python-api.token');

        return is_string($token) && $token !== ''
            ? $request->withToken($token)
            : $request;
    }
}
