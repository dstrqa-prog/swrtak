<?php require_once __DIR__ . '/../includes/functions.php'; require_once __DIR__ . '/../includes/security.php'; $links=get_links(); ?>
<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/css/style.css"><title>روابط البائع</title></head><body><main class="container"><h1>روابط البائع</h1><div class="grid">
<?php foreach($links as $l): ?><a class="card" href="<?= e($l['url']) ?>" target="_blank" rel="noopener"><h3><?= e($l['name']) ?></h3><p><?= e($l['url']) ?></p></a><?php endforeach; if(!$links): ?><p>لا توجد روابط حالياً.</p><?php endif; ?>
</div></main></body></html>
