<?php

namespace Orhanerday\OpenAi;

use InvalidArgumentException;
use UnexpectedValueException;

class Webhook
{
    /**
     * Verify the original request bytes before decoding the event.
     * https://github.com/standard-webhooks/standard-webhooks/blob/main/spec/standard-webhooks.md
     */
    public static function unwrap(string $payload, array $headers, string $secret, int $tolerance = 300): array
    {
        self::verifySignature($payload, $headers, $secret, $tolerance);
        $event = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($event) || substr(ltrim($payload), 0, 1) !== '{') {
            throw new UnexpectedValueException('Webhook payload must be a JSON object.');
        }

        return $event;
    }

    public static function verifySignature(string $payload, array $headers, string $secret, int $tolerance = 300): void
    {
        if ($tolerance < 0) {
            throw new InvalidArgumentException('Webhook timestamp tolerance cannot be negative.');
        }
        $normalized = [];
        foreach ($headers as $name => $value) {
            $normalized[strtolower($name)] = is_array($value) ? implode(' ', $value) : (string) $value;
        }
        $id = $normalized['webhook-id'] ?? '';
        $timestamp = $normalized['webhook-timestamp'] ?? '';
        $signatures = $normalized['webhook-signature'] ?? '';
        if (! preg_match('/^[A-Za-z0-9_-]+$/', $id) || ! preg_match('/^[0-9]+$/', $timestamp) || $signatures === '') {
            throw new UnexpectedValueException('Missing or invalid webhook signature headers.');
        }
        if (abs(time() - (int) $timestamp) > $tolerance) {
            throw new UnexpectedValueException('Webhook timestamp is outside the allowed tolerance.');
        }
        if (strpos($secret, 'whsec_') === 0) {
            $secret = substr($secret, 6);
        }
        $key = base64_decode($secret, true);
        if ($key === false || $key === '') {
            throw new InvalidArgumentException('Webhook secret must be a base64-encoded signing key.');
        }
        $expected = base64_encode(hash_hmac('sha256', $id . '.' . $timestamp . '.' . $payload, $key, true));
        foreach (preg_split('/\s+/', trim($signatures)) as $signature) {
            $parts = explode(',', $signature, 2);
            if (count($parts) === 2 && $parts[0] === 'v1' && hash_equals($expected, $parts[1])) {
                return;
            }
        }

        throw new UnexpectedValueException('Invalid webhook signature.');
    }
}
