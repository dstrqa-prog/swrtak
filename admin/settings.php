<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';

require_admin();
$s = get_settings();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $s['store_name'] = sanitize_text($_POST['store_name'] ?? '', 120);
    $s['about_editable'] = sanitize_multiline($_POST['about_editable'] ?? '', 2500);
    $s['privacy_policy'] = sanitize_multiline($_POST['privacy_policy'] ?? '', 5000);
    $s['cash_on_delivery_enabled'] = isset($_POST['cash_on_delivery_enabled']);
    $s['shamcash_enabled'] = isset($_POST['shamcash_enabled']);
    $s['shamcash_account_name'] = sanitize_text($_POST['shamcash_account_name'] ?? '', 120);
    $s['shamcash_account_id'] = sanitize_text($_POST['shamcash_account_id'] ?? '', 120);
    $s['telegram_bot_token'] = sanitize_text($_POST['telegram_bot_token'] ?? '', 300);
    $s['telegram_chat_id'] = sanitize_text($_POST['telegram_chat_id'] ?? '', 80);

    $adminKey = sanitize_text($_POST['admin_access_key'] ?? '', 120);
    if ($adminKey !== '') {
        $s['admin_access_key'] = $adminKey;
    }

    if (!empty($_POST['new_admin_password'])) {
        $s['admin_password_hash'] = password_hash((string)$_POST['new_admin_password'], PASSWORD_DEFAULT);
    }

    if (!empty($_FILES['shamcash_qr']['name'])) {
        $up = upload_image($_FILES['shamcash_qr'], 'qrcode');
        if ($up['ok']) {
            $s['shamcash_qr'] = $up['path'];
        }
    }

    save_settings($s);
    $msg = 'تم حفظ الإعدادات';
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <title>الإعدادات</title>
</head>
<body>
<?php include __DIR__ . '/partials_nav.php'; ?>
<main class="admin-wrap">
    <h1>الإعدادات</h1>
    <?php if ($msg): ?><p class="success"><?= e($msg) ?></p><?php endif; ?>
    <p>رابط دخول الإدارة الحالي: <code>/admin/login.php?key=<?= e($s['admin_access_key']) ?></code></p>

    <form method="post" enctype="multipart/form-data" class="panel">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>اسم المتجر<input name="store_name" value="<?= e($s['store_name']) ?>"></label>
        <label>مفتاح دخول صفحة الإدارة السرية<input name="admin_access_key" value="<?= e($s['admin_access_key']) ?>"></label>
        <label>قسم من نحن القابل للتعديل<textarea name="about_editable"><?= e($s['about_editable']) ?></textarea></label>
        <label>سياسة الخصوصية<textarea name="privacy_policy"><?= e($s['privacy_policy']) ?></textarea></label>
        <label><input type="checkbox" name="cash_on_delivery_enabled" <?= $s['cash_on_delivery_enabled'] ? 'checked' : ''; ?>> تفعيل الدفع عند الاستلام</label>
        <label><input type="checkbox" name="shamcash_enabled" <?= $s['shamcash_enabled'] ? 'checked' : ''; ?>> تفعيل ShamCash</label>
        <label>اسم حساب ShamCash<input name="shamcash_account_name" value="<?= e($s['shamcash_account_name']) ?>"></label>
        <label>ID حساب ShamCash<input name="shamcash_account_id" value="<?= e($s['shamcash_account_id']) ?>"></label>
        <label>QR Code<input type="file" name="shamcash_qr" accept="image/*"></label>
        <label>Telegram Bot Token<input name="telegram_bot_token" value="<?= e($s['telegram_bot_token']) ?>"></label>
        <label>Telegram Chat ID<input name="telegram_chat_id" value="<?= e($s['telegram_chat_id']) ?>"></label>
        <label>كلمة مرور جديدة للإدارة (اختياري)<input type="password" name="new_admin_password"></label>
        <button>حفظ</button>
    </form>
</main>
</body>
</html>
