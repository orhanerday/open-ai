<?php

use Orhanerday\OpenAi\OpenAi;

it('requires a callback for streaming requests', function ($method) {
    $client = new OpenAi('unused-local-key');
    expect(fn () => $client->$method(['stream' => true]))
        ->toThrow(Exception::class, 'Please provide a stream function.');
})->with(['chat', 'createResponse', 'image', 'imageEdit', 'transcribe', 'tts'])->group('transport');

it('requires a callback for response retrieval and speech SSE', function () {
    $client = new OpenAi('unused-local-key');
    expect(fn () => $client->retrieveResponse('resp_test', ['stream' => true]))
        ->toThrow(Exception::class, 'Please provide a stream function.');
    expect(fn () => $client->tts(['stream_format' => 'sse']))
        ->toThrow(Exception::class, 'Please provide a stream function.');
})->group('transport');

it('rejects malformed JSON before sending a request', function () {
    $client = new OpenAi('unused-local-key');
    expect(fn () => $client->createResponse(['model' => 'gpt-4o-mini', 'input' => "\xB1"]))
        ->toThrow(JsonException::class);
})->group('transport');

it('captures a real cURL protocol error', function () {
    $client = new OpenAi('unused-local-key');
    $client->setBaseURL('unsupported-protocol://localhost');

    try {
        $client->listModels();
        $this->fail('Expected the native cURL request to fail.');
    } catch (Exception $exception) {
        expect($exception->getCode())->toBe(CURLE_UNSUPPORTED_PROTOCOL);
        expect($exception->getMessage())->not->toBeEmpty();
    }
})->group('transport');

it('applies custom cURL options', function () {
    $client = new OpenAi('unused-local-key');
    // Using a custom curl option to intentionally break the request
    $client->setCURLOptions([
        CURLOPT_URL => 'unsupported-protocol://custom-curl-options-test',
    ]);

    try {
        $client->listModels();
        $this->fail('Expected custom cURL option to override URL and fail.');
    } catch (Exception $exception) {
        expect($exception->getCode())->toBe(CURLE_UNSUPPORTED_PROTOCOL);
    }
})->group('transport');

it('applies custom API versions', function () {
    $root = sys_get_temp_dir() . '/openai-version-' . bin2hex(random_bytes(8));
    $directory = $root . '/api/v3/files/test';
    mkdir($directory, 0700, true);
    file_put_contents($directory . '/content', 'custom version content');

    try {
        $client = new OpenAi('unused-local-key');
        $normalizedPath = str_replace('\\', '/', $root);
        $baseUrl = 'file://' . ($normalizedPath[0] === '/' ? '' : '/') . $normalizedPath;
        $client->setBaseURL($baseUrl);
        $client->setApiVersion('api/v3');

        expect($client->retrieveFileContent('test'))->toBe('custom version content');
        expect($client->getCURLInfo()['url'])->toBe($baseUrl . '/api/v3/files/test/content');
    } finally {
        unlink($directory . '/content');
        rmdir($directory);
        rmdir($root . '/api/v3/files');
        rmdir($root . '/api/v3');
        rmdir($root . '/api');
        rmdir($root);
    }
})->group('transport');

it('returns empty and zero-valued content through native cURL', function ($content) {
    $root = sys_get_temp_dir() . '/openai-curl-' . bin2hex(random_bytes(8));
    $directory = $root . '/v1/files/test';
    mkdir($directory, 0700, true);
    file_put_contents($directory . '/content', $content);

    try {
        $client = new OpenAi('unused-local-key');
        $normalizedPath = str_replace('\\', '/', $root);
        $client->setBaseURL('file://' . ($normalizedPath[0] === '/' ? '' : '/') . $normalizedPath);

        expect($client->retrieveFileContent('test'))->toBe($content);
    } finally {
        unlink($directory . '/content');
        rmdir($directory);
        rmdir($root . '/v1/files');
        rmdir($root . '/v1');
        rmdir($root);
    }
})->with(['', '0'])->group('transport');

it('rejects unsupported methods and non-relative paths in request()', function ($method, $path) {
    $client = new OpenAi('unused-local-key');
    expect(fn () => $client->request($method, $path))->toThrow(Exception::class);
})->with([
    ['TRACE', '/containers'],
    ['GET', 'containers'],
    ['GET', ''],
    ['GET', '//evil.example/containers'],
    ['GET', '/x://evil.example'],
])->group('transport');

it('sends request() to a custom path through native cURL', function () {
    $root = sys_get_temp_dir() . '/openai-request-' . bin2hex(random_bytes(8));
    $directory = $root . '/api/v3/containers';
    mkdir($directory, 0700, true);
    file_put_contents($directory . '/abc', '{"id":"abc"}');

    try {
        $client = new OpenAi('unused-local-key');
        $normalizedPath = str_replace('\\', '/', $root);
        $baseUrl = 'file://' . ($normalizedPath[0] === '/' ? '' : '/') . $normalizedPath;
        $client->setBaseURL($baseUrl);
        $client->setApiVersion('api/v3');

        expect($client->request('get', '/containers/abc'))->toBe('{"id":"abc"}');
        expect($client->getCURLInfo()['url'])->toBe($baseUrl . '/api/v3/containers/abc');

        // Older libcurl versions remove file:// queries from CURLINFO_EFFECTIVE_URL.
        expect($client->request('GET', '/containers/abc', ['limit' => 5, 'include' => ['a', 'b']]))
            ->toBe('{"id":"abc"}');
    } finally {
        unlink($directory . '/abc');
        rmdir($directory);
        rmdir($root . '/api/v3');
        rmdir($root . '/api');
        rmdir($root);
    }
})->group('transport');

it('requires an API key for request()', function () {
    $client = new OpenAi();
    expect(fn () => $client->request('GET', '/containers'))->toThrow(Exception::class, 'API key');
})->group('transport');

it('records getLastResponse through native cURL', function () {
    $root = sys_get_temp_dir() . '/openai-response-' . bin2hex(random_bytes(8));
    $directory = $root . '/v1/files/test';
    mkdir($directory, 0700, true);
    file_put_contents($directory . '/content', '{"status":"ok"}');

    try {
        $client = new OpenAi('unused-local-key');
        expect($client->getLastResponse())->toBeNull();

        $normalizedPath = str_replace('\\', '/', $root);
        $client->setBaseURL('file://' . ($normalizedPath[0] === '/' ? '' : '/') . $normalizedPath);

        $result = $client->retrieveFileContent('test');
        $response = $client->getLastResponse();

        expect($response)->toBeInstanceOf(\Orhanerday\OpenAi\Response::class);
        expect($response->body())->toBe('{"status":"ok"}');
        expect($response->json())->toBe(['status' => 'ok']);
    } finally {
        unlink($directory . '/content');
        rmdir($directory);
        rmdir($root . '/v1/files');
        rmdir($root . '/v1');
        rmdir($root);
    }
})->group('transport');
