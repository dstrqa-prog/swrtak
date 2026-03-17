<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_admin();

$products = get_products();
$categories = get_categories();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = sanitize_text($_POST['id'] ?? '');
        $products = array_values(array_filter($products, fn($p)=>$p['id']!==$id));
        save_products($products);
        $msg = 'تم حذف المنتج.';
    } else {
        $id = sanitize_text($_POST['id'] ?? '');
        $name = sanitize_text($_POST['name'] ?? '', 120);
        $price = sanitize_float($_POST['price'] ?? '0');
        $qty = sanitize_int($_POST['qty'] ?? '0');
        $desc = sanitize_multiline($_POST['description'] ?? '', 2000);
        $cat = sanitize_text($_POST['category'] ?? '', 80);
        $image = sanitize_text($_POST['existing_image'] ?? '');

        if (!empty($_FILES['image']['name'])) {
            $up = upload_image($_FILES['image'], 'products');
            if ($up['ok']) { $image = $up['path']; } else { $msg = $up['error']; }
        }

        if ($name !== '' && $msg === '') {
            $record = ['id'=>$id ?: generate_id('prd_'),'name'=>$name,'price'=>$price,'qty'=>$qty,'description'=>$desc,'category'=>$cat,'image'=>$image];
            $found = false;
            foreach ($products as &$p) {
                if ($p['id'] === $record['id']) { $p = $record; $found = true; break; }
            }
            unset($p);
            if (!$found) { $products[] = $record; }
            save_products($products);
            $msg = 'تم حفظ المنتج.';
        }
    }
    $products = get_products();
}

$edit = null;
if (!empty($_GET['edit'])) {
    $edit = find_by_id($products, sanitize_text($_GET['edit']));
}
?>
<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/css/admin.css"><title>إدارة المنتجات</title></head><body><?php include __DIR__ . '/partials_nav.php'; ?><main class="admin-wrap"><h1>إدارة المنتجات</h1><?php if($msg): ?><p class="success"><?= e($msg) ?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="panel"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>"><input type="hidden" name="existing_image" value="<?= e($edit['image'] ?? '') ?>"><label>الاسم<input name="name" required value="<?= e($edit['name'] ?? '') ?>"></label><label>السعر<input name="price" type="number" step="0.01" required value="<?= e((string)($edit['price'] ?? '')) ?>"></label><label>الكمية<input name="qty" type="number" min="0" required value="<?= e((string)($edit['qty'] ?? '')) ?>"></label><label>القسم<select name="category"><?php foreach($categories as $c): ?><option value="<?= e($c['name']) ?>" <?= (($edit['category'] ?? '')===$c['name'])?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></label><label>الوصف<textarea name="description"><?= e($edit['description'] ?? '') ?></textarea></label><label>الصورة<input type="file" name="image" accept="image/*"></label><button>حفظ</button></form>
<table><tr><th>الاسم</th><th>السعر</th><th>القسم</th><th>عمليات</th></tr><?php foreach($products as $p): ?><tr><td><?= e($p['name']) ?></td><td><?= e(format_price((float)$p['price'])) ?></td><td><?= e($p['category']) ?></td><td><a href="?edit=<?= urlencode($p['id']) ?>">تعديل</a><form method="post" style="display:inline"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($p['id']) ?>"><button onclick="return confirm('حذف؟')">حذف</button></form></td></tr><?php endforeach; ?></table>
</main></body></html>
