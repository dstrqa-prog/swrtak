<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$categories = get_categories();
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()){
  $action=$_POST['action']??'save';
  if($action==='delete'){
    $id=sanitize_text($_POST['id']??'');
    $categories=array_values(array_filter($categories,fn($c)=>$c['id']!==$id));
    save_categories($categories);$msg='تم الحذف';
  }else{
    $id=sanitize_text($_POST['id']??'');$name=sanitize_text($_POST['name']??'',80);
    if($name!==''){
      $rec=['id'=>$id?:generate_id('cat_'),'name'=>$name];$found=false;
      foreach($categories as &$c){if($c['id']===$rec['id']){$c=$rec;$found=true;}}
      unset($c); if(!$found)$categories[]=$rec; save_categories($categories); $msg='تم الحفظ';
    }
  }
  $categories=get_categories();
}
$edit=!empty($_GET['edit'])?find_by_id($categories,sanitize_text($_GET['edit'])):null;
?>
<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/css/admin.css"><title>الأقسام</title></head><body><?php include __DIR__ . '/partials_nav.php'; ?><main class="admin-wrap"><h1>إدارة الأقسام</h1><?php if($msg): ?><p class="success"><?= e($msg) ?></p><?php endif; ?><form method="post" class="panel"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e($edit['id']??'') ?>"><label>اسم القسم<input name="name" required value="<?= e($edit['name']??'') ?>"></label><button>حفظ القسم</button></form><ul><?php foreach($categories as $c): ?><li><?= e($c['name']) ?> - <a href="?edit=<?= urlencode($c['id']) ?>">تعديل</a><form method="post" style="display:inline"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($c['id']) ?>"><button>حذف</button></form></li><?php endforeach; ?></ul></main></body></html>
