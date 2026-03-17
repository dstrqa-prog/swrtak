<?php

declare(strict_types=1);

const STORAGE_PATH = __DIR__ . '/../storage';

function json_path(string $file): string
{
    return STORAGE_PATH . '/' . basename($file);
}

function read_json(string $file, array $default = []): array
{
    $path = json_path($file);
    if (!is_file($path)) {
        write_json($file, $default);
        return $default;
    }

    $raw = file_get_contents($path);
    if ($raw === false || $raw === '') {
        return $default;
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : $default;
}

function write_json(string $file, array $data): bool
{
    $path = json_path($file);
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($encoded === false) {
        return false;
    }

    $tmp = $path . '.tmp';
    if (file_put_contents($tmp, $encoded, LOCK_EX) === false) {
        return false;
    }

    return rename($tmp, $path);
}
