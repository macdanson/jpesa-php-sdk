<?php

namespace JPesa\SDK;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use JPesa\SDK\Exceptions\JPesaException;
use JPesa\SDK\Exceptions\HttpException;

final class JPesaClient
{
    private HttpClient $http;
    private string $apiKey;

    public function __construct(
        ?string $baseUrl = null,
        ?string $apiKey = null,
        float $timeout = 30.0
    ) {
        $baseUrl ??= 'https://my.jpesa.com/api/';
        $apiKey  ??= getenv('JPESA_API_KEY') ?: null;

        if (!$apiKey) {
            throw new JPesaException("JPesa API key is required. Set it via constructor or JPESA_API_KEY env.");
        }

        $this->http   = new HttpClient([
            'base_uri' => rtrim($baseUrl, '/') . '/',
            'timeout'  => $timeout,
        ]);
        $this->apiKey = $apiKey;
    }

    /** Credit (pull) funds from an MSISDN into your GWallet. */
    public function credit(array $params): array
    {
        $payload = $this->withKey([
            'mobile'   => $this->required($params, 'mobile'),
            'amount'   => $this->required($params, 'amount'),
            'act_to'   => $params['act_to']   ?? null,
            'callback' => $params['callback'] ?? null,
            'tx'       => $params['tx']       ?? null,
        ]);

        return $this->postJson('credit', $payload);
    }

    /** Debit (push) funds from your GWallet to an MSISDN. */
    public function debit(array $params): array
    {
        $payload = $this->withKey([
            'mobile'   => $this->required($params, 'mobile'),
            'amount'   => $this->required($params, 'amount'),
            'act_from' => $params['act_from'] ?? null,
            'callback' => $params['callback'] ?? null,
            'tx'       => $params['tx']       ?? null,
        ]);

        return $this->postJson('debit', $payload);
    }

    /** Transaction information (by tid or pid). If 'cur' is given, returns account/balance. */
    public function transactionInfo(array $params): array
    {
        $payload = $this->withKey([
            'tid' => $params['tid'] ?? null,
            'pid' => $params['pid'] ?? null,
            'cur' => $params['cur'] ?? null,
        ]);

        if (empty($payload['tid']) && empty($payload['pid']) && empty($payload['cur'])) {
            throw new JPesaException("Provide at least one of 'tid', 'pid', or 'cur' for transactionInfo().");
        }

        return $this->postJson('transaction/info', $payload);
    }

    /** KYC by MSISDN. */
    public function kyc(string $mobile): array
    {
        $payload = $this->withKey(['mobile' => $mobile]);
        return $this->postJson('kyc', $payload);
    }

    /** Centralized POST helper with basic error normalization. */
    private function postJson(string $path, array $payload): array
    {
        $payload = array_filter($payload, static fn($v) => $v !== null);

        try {
            $response = $this->http->post($path, [
                'form_params' => $payload,
                'http_errors' => false,
            ]);
        } catch (GuzzleException $e) {
            throw new HttpException("HTTP error calling JPesa: " . $e->getMessage(), 0, $e);
        }

        $status = $response->getStatusCode();
        $body   = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            throw new HttpException("Non-2xx from JPesa ({$status}): {$body}", $status);
        }

        $json = json_decode($body, true);
        if (!is_array($json)) {
            throw new JPesaException("Invalid JSON from JPesa: {$body}");
        }

        if (isset($json['api_status']) && strtolower((string)$json['api_status']) !== 'success') {
            $msg = $json['msg'] ?? $json['message'] ?? 'Unknown JPesa error';
            throw new JPesaException("JPesa API error: {$msg}", payload: $json);
        }

        return $json;
    }

    private function withKey(array $payload): array
    {
        $payload['_key_'] = $this->apiKey;
        return $payload;
    }

    private function required(array $arr, string $key): mixed
    {
        if (!array_key_exists($key, $arr) || $arr[$key] === null || $arr[$key] === '') {
            throw new JPesaException("Missing required parameter: {$key}");
        }
        return $arr[$key];
    }
}
