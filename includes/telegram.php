<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function send_order_to_telegram(array $order): bool
{
    $settings = get_settings();
    $token = trim((string)($settings['telegram_bot_token'] ?? ''));
    $chatId = trim((string)($settings['telegram_chat_id'] ?? ''));

    if ($token === '' || $chatId === '') {
        return false;
    }

    $text = "طلب جديد\n"
        . "رقم العملية: {$order['operation_id']}\n"
        . "العميل: {$order['customer_name']}\n"
        . "الهاتف: {$order['phone']}\n"
        . "المنتج: {$order['product_name']}\n"
        . "كود الدفع: {$order['payment_code']}\n"
        . "طريقة الدفع: {$order['payment_method']}\n";

    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $payload = http_build_query(['chat_id' => $chatId, 'text' => $text]);

    $opts = ['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $payload,
        'timeout' => 8
    ]];

    $context = stream_context_create($opts);
    $result = @file_get_contents($url, false, $context);
    return $result !== false;
}
