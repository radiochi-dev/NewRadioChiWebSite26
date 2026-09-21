<?php

namespace App\Support;

class AutomationSignature
{
    public static function make(string $secret, string $timestamp, string $body): string
    {
        return hash_hmac('sha256', $timestamp.'.'.$body, $secret);
    }

    public static function verify(string $secret, string $timestamp, string $body, string $signature): bool
    {
        $expectedSignature = self::make($secret, $timestamp, $body);

        return hash_equals($expectedSignature, $signature);
    }

    public static function headers(string $secret, array $payload, ?string $event = null): array
    {
        $timestamp = (string) now()->timestamp;
        $body = $payload === []
            ? ''
            : (json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');

        return array_filter([
            'X-Radiochi-Timestamp' => $timestamp,
            'X-Radiochi-Signature' => self::make($secret, $timestamp, $body),
            'X-Radiochi-Event' => $event,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
    }
}
