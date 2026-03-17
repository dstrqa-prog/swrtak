<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

$id = sanitize_text($_GET['id'] ?? '');
$product = find_by_id(get_products(), $id);
if (!$product) {
    http_response_code(404);
    exit('المنتج غير موجود');
}
?>
<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/css/style.css"><title><?= e($product['name']) ?></title></head>
<body><main class="container single">
<a href="index.php">← العودة</a>
<img class="single-image" src="<?= e($product['image'] ?: '/assets/images/placeholder.svg') ?>" alt="">
<h1><?= e($product['name']) ?></h1>
<p><?= nl2br(e($product['description'])) ?></p>
<p>السعر: <strong><?= e(format_price((float)$product['price'])) ?></strong></p>
<p>المتوفر: <?= e((string)$product['qty']) ?></p>
<a class="btn" href="checkout.php?id=<?= urlencode($product['id']) ?>">شراء الآن</a>
</main></body></html>
