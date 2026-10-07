<?php

use Orhanerday\OpenAi\Webhook;

it('matches an independently generated webhook signature vector', function () {
    // Generated with Python's hmac/hashlib, independently of the PHP implementation.
    Webhook::verifySignature('{"type":"response.completed"}', [
        'webhook-id' => 'wh_vector',
        'webhook-timestamp' => '1700000000',
        'webhook-signature' => 'v1,6c2cO7l8hFaB0gig5H+Snqz6tdEFN/tx0f0O5fHSSwY=',
    ], 'whsec_AAECAwQFBgcICQoLDA0ODxAREhMUFRYXGBkaGxwdHh8=', PHP_INT_MAX);
    expect(true)->toBeTrue();
})->group('webhook');

it('verifies a signature produced by the Standard Webhooks scheme', function () {
    $payload = '{"type":"response.completed","data":{"id":"resp_test"}}';
    $timestamp = (string) time();
    $key = random_bytes(32);
    $signature = base64_encode(hash_hmac('sha256', 'wh_test.' . $timestamp . '.' . $payload, $key, true));
    $headers = [
        'Webhook-Id' => 'wh_test',
        'Webhook-Timestamp' => $timestamp,
        'Webhook-Signature' => 'v1,invalid v1,' . $signature,
    ];

    expect(Webhook::unwrap($payload, $headers, 'whsec_' . base64_encode($key))['type'])->toBe('response.completed');
    expect(fn () => Webhook::unwrap($payload . ' ', $headers, 'whsec_' . base64_encode($key)))
        ->toThrow(UnexpectedValueException::class, 'Invalid webhook signature.');
})->group('webhook');

it('rejects replayed and future-dated webhook requests', function ($offset) {
    $payload = '{}';
    $timestamp = (string) (time() + $offset);
    $key = random_bytes(32);
    $signature = base64_encode(hash_hmac('sha256', 'wh_test.' . $timestamp . '.' . $payload, $key, true));

    expect(fn () => Webhook::unwrap($payload, [
        'webhook-id' => 'wh_test',
        'webhook-timestamp' => $timestamp,
        'webhook-signature' => 'v1,' . $signature,
    ], 'whsec_' . base64_encode($key)))
        ->toThrow(UnexpectedValueException::class, 'Webhook timestamp is outside the allowed tolerance.');
})->with([-600, 600])->group('webhook');

it('rejects requests missing signature metadata', function () {
    expect(fn () => Webhook::unwrap('{}', [], 'whsec_' . base64_encode(random_bytes(32))))
        ->toThrow(UnexpectedValueException::class, 'Missing or invalid webhook signature headers.');
})->group('webhook');
