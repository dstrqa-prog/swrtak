<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/telegram.php';

$settings = get_settings();
$id = sanitize_text($_GET['id'] ?? $_POST['id'] ?? '');
$product = find_by_id(get_products(), $id);
if (!$product) {
    http_response_code(404);
    exit('المنتج غير موجود');
}

$error = '';
$success = '';
$paymentCode = generate_payment_code();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'طلب غير صالح.';
    } else {
        $customer = sanitize_text($_POST['customer_name'] ?? '', 120);
        $phone = sanitize_phone($_POST['phone'] ?? '');
        $contact = in_array($_POST['contact_method'] ?? '', ['whatsapp','call'], true) ? $_POST['contact_method'] : 'call';
        $method = in_array($_POST['payment_method'] ?? '', ['cod','shamcash'], true) ? $_POST['payment_method'] : 'cod';

        if ($customer === '' || $phone === '') {
            $error = 'يرجى إدخال البيانات المطلوبة.';
        } elseif (($product['qty'] ?? 0) < 1) {
            $error = 'المنتج نفد من المخزون.';
        } elseif (($method === 'cod' && !$settings['cash_on_delivery_enabled']) || ($method === 'shamcash' && !$settings['shamcash_enabled'])) {
            $error = 'طريقة الدفع غير متاحة.';
        } else {
            $orders = get_orders();
            $op = 'OP-' . strtoupper(bin2hex(random_bytes(4)));
            $order = [
                'id' => generate_id('order_'),
                'operation_id' => $op,
                'customer_name' => $customer,
                'phone' => $phone,
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'payment_code' => $paymentCode,
                'contact_method' => $contact,
                'payment_method' => $method,
                'status' => 'new',
                'created_at' => date('c')
            ];
            $orders[] = $order;
            save_orders($orders);

            $products = get_products();
            foreach ($products as &$p) {
                if ($p['id'] === $product['id']) {
                    $p['qty'] = max(0, (int)$p['qty'] - 1);
                }
            }
            unset($p);
            save_products($products);
            send_order_to_telegram($order);
            $success = "تم إنشاء الطلب بنجاح. رقم العملية: {$op}";
        }
    }
}
?>
<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/css/style.css"><title>إتمام الطلب</title></head><body>
<main class="container form-wrap"><h1>إتمام الطلب - <?= e($product['name']) ?></h1>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<?php if ($success): ?><p class="success"><?= e($success) ?></p><?php endif; ?>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e($product['id']) ?>">
<label>الاسم الكامل<input name="customer_name" required></label>
<label>رقم الهاتف<input name="phone" required></label>
<label>طريقة التواصل<select name="contact_method"><option value="whatsapp">واتساب</option><option value="call">اتصال</option></select></label>
<label>طريقة الدفع<select name="payment_method">
<?php if ($settings['cash_on_delivery_enabled']): ?><option value="cod">الدفع عند الاستلام</option><?php endif; ?>
<?php if ($settings['shamcash_enabled']): ?><option value="shamcash">ShamCash</option><?php endif; ?>
</select></label>
<div class="notice">كود الدفع الخاص بك: <b><?= e($paymentCode) ?></b> (15 حرف فريد).<br>ShamCash ID: <?= e($settings['shamcash_account_id']) ?><br><?php if ($settings['shamcash_qr']): ?><img src="<?= e($settings['shamcash_qr']) ?>" class="qr" alt="qr"><?php endif; ?></div>
<button class="btn" type="submit">تأكيد الطلب</button>
</form></main></body></html>
