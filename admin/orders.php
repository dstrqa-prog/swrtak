<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$orders=get_orders();
if($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()){
  $action=$_POST['action']??''; $id=sanitize_text($_POST['id']??'');
  if($action==='delete'){$orders=array_values(array_filter($orders,fn($o)=>$o['id']!==$id));}
  if($action==='delivered'){foreach($orders as &$o){if($o['id']===$id){$o['status']='delivered';}} unset($o);} 
  save_orders($orders); $orders=get_orders();
}
?>
<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/css/admin.css"><title>الطلبات</title></head><body><?php include __DIR__ . '/partials_nav.php'; ?><main class="admin-wrap"><h1>إدارة الطلبات</h1><table><tr><th>رقم العملية</th><th>العميل</th><th>الهاتف</th><th>المنتج</th><th>كود الدفع</th><th>الحالة</th><th>إجراءات</th></tr><?php foreach($orders as $o): ?><tr><td><?= e($o['operation_id']) ?></td><td><?= e($o['customer_name']) ?></td><td><?= e($o['phone']) ?></td><td><?= e($o['product_name']) ?></td><td><?= e($o['payment_code']) ?></td><td><?= e($o['status']) ?></td><td><form method="post" style="display:inline"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e($o['id']) ?>"><input type="hidden" name="action" value="delivered"><button>تم التسليم</button></form><form method="post" style="display:inline"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e($o['id']) ?>"><input type="hidden" name="action" value="delete"><button>حذف</button></form></td></tr><?php endforeach; ?></table></main></body></html>
