<?php

use Orhanerday\OpenAi\OpenAi;

it('rejects requests without an API key before opening a connection', function () {
    $client = new OpenAi();
    $client->setBaseURL('unsupported-protocol://localhost');

    expect(fn () => $client->listModels())
        ->toThrow(Exception::class, 'Please provide an API key using the constructor or setApiKey().');
    expect($client->getCURLInfo())->toBe([]);
})->group('transport', 'api-keys');

it('rejects blank and newline-containing API keys', function ($key) {
    $client = new OpenAi();
    expect(fn () => $client->setApiKey($key))->toThrow(InvalidArgumentException::class);
})->with(['', '   ', "key\r\nInjected: value"])->group('transport', 'api-keys');

it('authenticates a real API request with constructor deferred and replaced keys', function ($configuration) {
    $key = getenv('OPENAI_API_KEY');
    if (! $key) {
        $this->markTestSkipped('Set OPENAI_API_KEY in the environment or .env to run real API tests.');
    }

    $client = new OpenAi($configuration === 'constructor' ? $key : '');
    if ($configuration === 'deferred') {
        $client->setApiKey($key);
    } elseif ($configuration === 'replaced') {
        // Exercise case-insensitive replacement of an existing Authorization header.
        $client->setHeader(['authorization' => 'Bearer unused-old-key']);
        $client->setApiKey($key);
        // A rejected update must leave the configured credential usable.
        expect(fn () => $client->setApiKey(''))->toThrow(InvalidArgumentException::class);
    } elseif ($configuration === 'header') {
        $client->setHeader(['Authorization' => 'Bearer ' . $key]);
    }
    $client->setTimeout(30);
    $body = json_decode($client->listModels(), true, 512, JSON_THROW_ON_ERROR);

    expect($client->getCURLInfo()['http_code'])->toBe(200);
    expect($body['object'] ?? null)->toBe('list');
    expect($body['data'] ?? null)->toBeArray()->not->toBeEmpty();
})->with(['constructor', 'deferred', 'replaced', 'header'])->group('live', 'api-keys');
