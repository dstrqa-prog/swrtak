<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function ensure_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function admin_logged_in(): bool
{
    ensure_session();
    return !empty($_SESSION['admin_logged']);
}

function admin_login(string $username, string $password): bool
{
    ensure_session();
    $settings = get_settings();

    $valid = hash_equals((string)$settings['admin_username'], $username)
        && password_verify($password, (string)$settings['admin_password_hash']);

    if ($valid) {
        $_SESSION['admin_logged'] = true;
        session_regenerate_id(true);
        return true;
    }

    return false;
}

function require_admin(): void
{
    if (!admin_logged_in()) {
        $settings = get_settings();
        $key = urlencode((string)$settings['admin_access_key']);
        header('Location: login.php?key=' . $key);
        exit;
    }
}
