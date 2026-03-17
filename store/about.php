<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
$s = get_settings();
?>
<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/css/style.css"><title>من نحن</title></head><body><main class="container">
<h1>من نحن</h1>
<section class="panel"><h2>قسم ثابت</h2><p>نحن متجر إلكتروني احترافي نركز على الجودة والسرعة وتجربة شراء آمنة.</p></section>
<section class="panel"><h2>رسالة البائع (قابلة للتعديل)</h2><p><?= nl2br(e($s['about_editable'])) ?></p></section>
</main></body></html>
