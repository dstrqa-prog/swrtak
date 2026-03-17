<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sanitize_text(string $value, int $maxLen = 255): string
{
    $value = trim(strip_tags($value));
    $value = preg_replace('/\s+/u', ' ', $value) ?? '';
    return mb_substr($value, 0, $maxLen);
}

function sanitize_multiline(string $value, int $maxLen = 5000): string
{
    $value = trim(strip_tags($value));
    $value = preg_replace('/\r\n?|\n/u', "\n", $value) ?? '';
    return mb_substr($value, 0, $maxLen);
}

function sanitize_phone(string $value): string
{
    $value = preg_replace('/[^0-9+]/', '', trim($value)) ?? '';
    return mb_substr($value, 0, 20);
}

function sanitize_url(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    if (!preg_match('#^https?://#i', $value)) {
        $value = 'https://' . $value;
    }

    if (!filter_var($value, FILTER_VALIDATE_URL)) {
        return '';
    }

    return $value;
}

function sanitize_float(string $value, float $min = 0): float
{
    $num = filter_var($value, FILTER_VALIDATE_FLOAT);
    if ($num === false || $num < $min) {
        return $min;
    }
    return round((float)$num, 2);
}

function sanitize_int(string $value, int $min = 0): int
{
    $num = filter_var($value, FILTER_VALIDATE_INT);
    if ($num === false || $num < $min) {
        return $min;
    }
    return (int)$num;
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $token = $_POST['csrf_token'] ?? '';
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
