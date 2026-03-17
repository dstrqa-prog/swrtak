<?php

declare(strict_types=1);

require_once __DIR__ . '/json.php';
require_once __DIR__ . '/security.php';

function get_settings(): array
{
    $defaults = [
        'store_name' => 'Neon Store',
        'admin_username' => 'admin',
        'admin_password_hash' => password_hash('ChangeMe123!', PASSWORD_DEFAULT),
        'admin_access_key' => 'change-this-secret-key',
        'about_editable' => 'هذا النص قابل للتعديل من لوحة الإدارة لعرض قصة البائع ورسالة المتجر.',
        'privacy_policy' => 'نحن نحترم خصوصيتك، ونستخدم بياناتك فقط لتنفيذ الطلبات وخدمة العملاء.',
        'cash_on_delivery_enabled' => true,
        'shamcash_enabled' => true,
        'shamcash_account_name' => '',
        'shamcash_account_id' => '',
        'shamcash_qr' => '',
        'telegram_bot_token' => '',
        'telegram_chat_id' => ''
    ];

    return array_replace($defaults, read_json('settings.json', $defaults));
}

function save_settings(array $settings): bool { return write_json('settings.json', $settings); }
function get_categories(): array { return read_json('categories.json', []); }
function save_categories(array $items): bool { return write_json('categories.json', array_values($items)); }
function get_products(): array { return read_json('products.json', []); }
function save_products(array $items): bool { return write_json('products.json', array_values($items)); }
function get_orders(): array { return read_json('orders.json', []); }
function save_orders(array $items): bool { return write_json('orders.json', array_values($items)); }
function get_links(): array { return read_json('links.json', []); }
function save_links(array $items): bool { return write_json('links.json', array_values($items)); }

function generate_id(string $prefix = ''): string
{
    return $prefix . bin2hex(random_bytes(6)) . time();
}

function generate_payment_code(): string
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%^&*';
    $max = strlen($chars) - 1;

    do {
        $code = '';
        for ($i = 0; $i < 15; $i++) {
            $code .= $chars[random_int(0, $max)];
        }
        $existing = array_column(get_orders(), 'payment_code');
    } while (in_array($code, $existing, true));

    return $code;
}

function find_by_id(array $items, string $id): ?array
{
    foreach ($items as $item) {
        if (($item['id'] ?? '') === $id) {
            return $item;
        }
    }
    return null;
}

function format_price(float $price): string
{
    return '$' . number_format($price, 2);
}
