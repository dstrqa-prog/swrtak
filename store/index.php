<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

$products = get_products();
$categories = get_categories();
$settings = get_settings();
$categoryFilter = sanitize_text($_GET['category'] ?? '');
if ($categoryFilter !== '') {
    $products = array_values(array_filter($products, fn($p) => ($p['category'] ?? '') === $categoryFilter));
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($settings['store_name']) ?></title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="topbar"><h1><?= e($settings['store_name']) ?></h1><nav>
<a href="index.php">الرئيسية</a><a href="about.php">من نحن</a><a href="links.php">روابط البائع</a><a href="privacy.php">الخصوصية</a><a href="developer.php">المطور</a></nav></header>
<main class="container">
<section class="filters"><a class="chip" href="index.php">الكل</a>
<?php foreach ($categories as $cat): ?><a class="chip" href="?category=<?= urlencode($cat['name']) ?>"><?= e($cat['name']) ?></a><?php endforeach; ?>
</section>
<section class="grid">
<?php foreach ($products as $p): ?>
<article class="card">
<img src="<?= e($p['image'] ?: '/assets/images/placeholder.svg') ?>" alt="<?= e($p['name']) ?>">
<h3><?= e($p['name']) ?></h3>
<p class="price"><?= e(format_price((float)$p['price'])) ?></p>
<a class="btn" href="product.php?id=<?= urlencode($p['id']) ?>">عرض المنتج</a>
</article>
<?php endforeach; if (!$products): ?><p>لا توجد منتجات حالياً.</p><?php endif; ?>
</section>
</main>
<footer>جميع الحقوق محفوظة للبائع وللمطور.</footer>
<script src="/assets/js/main.js"></script>
</body></html>
