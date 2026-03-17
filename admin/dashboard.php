<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';
require_admin();
$products = get_products();
$orders = get_orders();
$categories = get_categories();
$newOrders = count(array_filter($orders, fn($o)=>($o['status']??'')==='new'));
?>
<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/css/admin.css"><title>لوحة الإدارة</title></head><body>
<?php include __DIR__ . '/partials_nav.php'; ?>
<main class="admin-wrap"><h1>لوحة التحكم</h1><div class="stats"><div>المنتجات: <?= count($products) ?></div><div>الأقسام: <?= count($categories) ?></div><div>الطلبات الجديدة: <?= $newOrders ?></div><div>إجمالي الطلبات: <?= count($orders) ?></div></div></main></body></html>
