<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

$settings = get_settings();
$key = sanitize_text($_GET['key'] ?? '');
if ($key === '' || !hash_equals((string)$settings['admin_access_key'], $key)) {
    http_response_code(404);
    exit('404');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'رمز أمان غير صالح.';
    } else {
        $u = sanitize_text($_POST['username'] ?? '');
        $p = (string)($_POST['password'] ?? '');
        if (admin_login($u, $p)) {
            header('Location: dashboard.php');
            exit;
        }
        $error = 'بيانات الدخول غير صحيحة.';
    }
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <title>دخول الإدارة</title>
</head>
<body>
<main class="admin-wrap">
    <h1>دخول الإدارة</h1>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>اسم المستخدم<input name="username" required></label>
        <label>كلمة المرور<input type="password" name="password" required></label>
        <button type="submit">دخول</button>
    </form>
</main>
</body>
</html>
