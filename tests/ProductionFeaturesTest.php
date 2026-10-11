<?php

use Orhanerday\OpenAi\OpenAi;
use Orhanerday\OpenAi\OpenAiException;
use Orhanerday\OpenAi\Response;

it('parses error payloads into OpenAiException', function () {
    $payload = json_encode([
        'error' => [
            'message' => 'The model `invalid-model` does not exist.',
            'type' => 'invalid_request_error',
            'param' => 'model',
            'code' => 'model_not_found',
        ],
    ]);

    $exception = new OpenAiException(404, $payload);

    expect($exception->getStatusCode())->toBe(404);
    expect($exception->getCode())->toBe(404);
    expect($exception->getMessage())->toBe('HTTP 404: The model `invalid-model` does not exist.');
    expect($exception->getErrorType())->toBe('invalid_request_error');
    expect($exception->getErrorCode())->toBe('model_not_found');
    expect($exception->getResponseBody())->toBe($payload);
    expect($exception->getApiError())->toBeArray();
    expect($exception->getApiError()['param'])->toBe('model');
});

it('handles string error payloads in OpenAiException', function () {
    $payload = json_encode(['error' => 'Rate limit exceeded']);
    $exception = new OpenAiException(429, $payload);

    expect($exception->getStatusCode())->toBe(429);
    expect($exception->getMessage())->toBe('HTTP 429: Rate limit exceeded');
    expect($exception->getErrorType())->toBeNull();
    expect($exception->getErrorCode())->toBeNull();
});

it('handles non-JSON error payloads in OpenAiException', function () {
    $exception = new OpenAiException(502, 'Bad Gateway');

    expect($exception->getStatusCode())->toBe(502);
    expect($exception->getMessage())->toBe('HTTP 502: The API returned an error response.');
    expect($exception->getResponseBody())->toBe('Bad Gateway');
    expect($exception->getApiError())->toBeNull();
});

it('provides helper methods on the Response object', function () {
    $headers = [
        'content-type' => 'application/json',
        'openai-processing-ms' => '120',
        'x-request-id' => 'req_test123',
    ];
    $body = json_encode(['id' => 'chatcmpl-123', 'model' => 'gpt-4o-mini']);

    $response = new Response(200, $headers, $body);

    expect($response->status())->toBe(200);
    expect($response->ok())->toBeTrue();
    expect($response->body())->toBe($body);
    expect((string) $response)->toBe($body);
    expect($response->headers())->toBe($headers);
    expect($response->header('Content-Type'))->toBe('application/json');
    expect($response->header('X-REQUEST-ID'))->toBe('req_test123');
    expect($response->header('missing-header'))->toBeNull();
    expect($response->json())->toBeArray();
    expect($response->json()['id'])->toBe('chatcmpl-123');
    expect($response->json(false)->model)->toBe('gpt-4o-mini');
});

it('identifies non-2xx status as not ok on Response', function () {
    $response = new Response(400, [], '{"error": {}}');

    expect($response->status())->toBe(400);
    expect($response->ok())->toBeFalse();
});

it('rejects negative retry configuration in OpenAi', function () {
    $client = new OpenAi('test-key');

    expect(fn () => $client->setRetries(-1))->toThrow(InvalidArgumentException::class);
    expect(fn () => $client->setRetries(1, -5))->toThrow(InvalidArgumentException::class);
});

it('retries temporary server errors only for idempotent methods', function () {
    $client = new OpenAi('unused-local-key');
    $policy = new ReflectionMethod($client, 'shouldRetry');
    $policy->setAccessible(true);

    foreach ([500, 502, 503, 504] as $status) {
        foreach (['GET', 'PUT', 'DELETE'] as $method) {
            expect($policy->invoke($client, $status, $method))->toBeTrue();
        }
        foreach (['POST', 'PATCH'] as $method) {
            expect($policy->invoke($client, $status, $method))->toBeFalse();
        }
    }
    foreach ([200, 400, 401, 403, 404, 408, 501] as $status) {
        expect($policy->invoke($client, $status, 'GET'))->toBeFalse();
    }
});

it('distinguishes retryable rate limits from quota and billing failures', function () {
    $client = new OpenAi('unused-local-key');
    $policy = new ReflectionMethod($client, 'shouldRetry');
    $policy->setAccessible(true);

    $transient = json_encode(['error' => ['type' => 'rate_limit_error', 'code' => 'rate_limit_exceeded']]);
    expect($policy->invoke($client, 429, 'POST', $transient))->toBeTrue();
    expect($policy->invoke($client, 429, 'GET', 'Too many requests'))->toBeTrue();

    foreach ([
        ['code' => 'insufficient_quota'],
        ['code' => 'billing_hard_limit_reached'],
        ['type' => 'insufficient_quota'],
        ['type' => 'billing_not_active'],
    ] as $error) {
        expect($policy->invoke($client, 429, 'POST', json_encode(['error' => $error])))->toBeFalse();
    }
});

it('bases retry safety on the effective custom HTTP method', function () {
    $client = new OpenAi('unused-local-key');
    $method = new ReflectionMethod($client, 'retryMethod');
    $method->setAccessible(true);
    $policy = new ReflectionMethod($client, 'shouldRetry');
    $policy->setAccessible(true);
    $options = [CURLOPT_RETURNTRANSFER => true];

    expect($method->invoke($client, $options + [CURLOPT_CUSTOMREQUEST => 'GET']))->toBe('GET');
    $actualMethod = $method->invoke($client, $options + [CURLOPT_CUSTOMREQUEST => 'POST']);
    expect($actualMethod)->toBe('POST');
    expect($policy->invoke($client, 500, $actualMethod))->toBeFalse();
    expect($policy->invoke($client, 429, $actualMethod))->toBeTrue();
    expect($method->invoke($client, $options + [CURLOPT_CUSTOMREQUEST => 'pOsT']))->toBeNull();
    expect($method->invoke($client, $options + [CURLOPT_CUSTOMREQUEST => 'TRACE']))->toBeNull();
});

it('does not replay cURL transfers with callbacks or altered body handling', function () {
    $client = new OpenAi('unused-local-key');
    $method = new ReflectionMethod($client, 'retryMethod');
    $method->setAccessible(true);
    $output = fopen('php://temp', 'w+');

    try {
        $unsafeOptions = [
            [CURLOPT_READFUNCTION => static function () {
                return '';
            }],
            [CURLOPT_WRITEFUNCTION => static function ($handle, $chunk) {
                return strlen($chunk);
            }],
            [CURLOPT_FILE => $output],
            [CURLOPT_INFILE => $output],
            [CURLOPT_UPLOAD => true],
            [CURLOPT_RETURNTRANSFER => false],
            [CURLOPT_FAILONERROR => true],
            [CURLOPT_HEADER => true],
        ];
        foreach ($unsafeOptions as $options) {
            $options += [CURLOPT_CUSTOMREQUEST => 'GET', CURLOPT_RETURNTRANSFER => true];
            expect($method->invoke($client, $options))->toBeNull();
        }
        expect($method->invoke($client, [CURLOPT_CUSTOMREQUEST => 'GET']))->toBeNull();
        expect($method->invoke($client, [
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => false,
            CURLOPT_HEADER => false,
        ]))->toBe('GET');
    } finally {
        fclose($output);
    }
});

it('honors numeric Retry-After hints without retrying before longer deadlines', function () {
    $client = new OpenAi('unused-local-key');
    $client->setRetries(3, 500);
    $delay = new ReflectionMethod($client, 'retryDelayMicroseconds');
    $delay->setAccessible(true);

    $seconds = $delay->invoke($client, 0, ['retry-after' => '2']);
    expect($seconds)->toBeGreaterThanOrEqual(2000000)->toBeLessThanOrEqual(2200000);
    expect($delay->invoke($client, 0, ['retry-after' => '0']))->toBe(0);
    expect($delay->invoke($client, 0, ['retry-after' => '60']))->toBe(60000000);
    expect($delay->invoke($client, 0, ['retry-after' => '61']))->toBeNull();
    expect($delay->invoke($client, 0, ['retry-after' => '120']))->toBeNull();
});

it('honors HTTP-date Retry-After hints and treats elapsed dates as immediately retryable', function () {
    $client = new OpenAi('unused-local-key');
    $delay = new ReflectionMethod($client, 'retryDelayMicroseconds');
    $delay->setAccessible(true);
    $before = time();
    $deadline = $before + 30;
    $wait = $delay->invoke($client, 0, ['retry-after' => gmdate('D, d M Y H:i:s', $deadline) . ' GMT']);

    expect($wait)->toBeGreaterThanOrEqual(max(0, $deadline - time()) * 1000000);
    expect($wait)->toBeLessThanOrEqual(($deadline - $before) * 1000000 + 1000000);
    expect($delay->invoke($client, 0, [
        'retry-after' => gmdate('D, d M Y H:i:s', time() - 60) . ' GMT',
    ]))->toBe(0);
    expect($delay->invoke($client, 0, [
        'retry-after' => gmdate('D, d M Y H:i:s', time() + 90) . ' GMT',
    ]))->toBeNull();
});

it('bounds exponential retry delays and their jitter', function () {
    $client = new OpenAi('unused-local-key');
    $client->setRetries(4, 500);
    $delay = new ReflectionMethod($client, 'retryDelayMicroseconds');
    $delay->setAccessible(true);

    foreach ([0 => [500000, 550000], 1 => [1000000, 1100000], 2 => [2000000, 2200000]] as $attempt => $bounds) {
        $wait = $delay->invoke($client, $attempt, []);
        expect($wait)->toBeGreaterThanOrEqual($bounds[0])->toBeLessThanOrEqual($bounds[1]);
    }
    expect($delay->invoke($client, 20, []))->toBe(60000000);
    $invalidHint = $delay->invoke($client, 0, ['retry-after' => 'not a date']);
    expect($invalidHint)->toBeGreaterThanOrEqual(500000)->toBeLessThanOrEqual(550000);

    $client->setRetries(1, 0);
    expect($delay->invoke($client, 0, []))->toBe(0);
});

it('keeps only the final response header block after redirects and interim responses', function () {
    $client = new OpenAi('unused-local-key');
    $collector = new ReflectionMethod($client, 'captureResponseHeader');
    $collector->setAccessible(true);
    $headers = [];
    $status = 0;

    foreach ([
        "HTTP/1.1 100 Continue\r\n",
        "X-Interim: ignored\r\n",
        "\r\n",
        "HTTP/1.1 302 Found\r\n",
        "Location: /models\r\n",
        "Retry-After: 120\r\n",
        "\r\n",
        "HTTP/2 200\r\n",
        "Content-Type: application/json\r\n",
        "X-Request-Id: req_final\r\n",
        "\r\n",
    ] as $line) {
        $collector->invokeArgs($client, [&$headers, &$status, $line]);
    }

    expect($status)->toBe(200);
    expect($headers)->toBe([
        'content-type' => 'application/json',
        'x-request-id' => 'req_final',
    ]);
});
