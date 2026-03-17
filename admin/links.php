<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$links=get_links(); $msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()){
  $action=$_POST['action']??'save';
  if($action==='delete'){$id=sanitize_text($_POST['id']??'');$links=array_values(array_filter($links,fn($l)=>$l['id']!==$id));$msg='تم الحذف';}
  else{
    $id=sanitize_text($_POST['id']??'');$name=sanitize_text($_POST['name']??'',80);$url=sanitize_url($_POST['url']??'');
    if($name!=='' && $url!==''){$rec=['id'=>$id?:generate_id('lnk_'),'name'=>$name,'url'=>$url];$found=false;foreach($links as &$l){if($l['id']===$rec['id']){$l=$rec;$found=true;}}unset($l);if(!$found)$links[]=$rec;$msg='تم الحفظ';}
  }
  save_links($links);$links=get_links();
}
$edit=!empty($_GET['edit'])?find_by_id($links,sanitize_text($_GET['edit'])):null;
?>
<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/css/admin.css"><title>روابط البائع</title></head><body><?php include __DIR__ . '/partials_nav.php'; ?><main class="admin-wrap"><h1>روابط البائع</h1><?php if($msg): ?><p class="success"><?= e($msg) ?></p><?php endif; ?><form method="post" class="panel"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e($edit['id']??'') ?>"><label>اسم الصفحة<input name="name" required value="<?= e($edit['name']??'') ?>"></label><label>الرابط<input name="url" required value="<?= e($edit['url']??'') ?>"></label><button>حفظ الرابط</button></form><ul><?php foreach($links as $l): ?><li><?= e($l['name']) ?> - <?= e($l['url']) ?> <a href="?edit=<?= urlencode($l['id']) ?>">تعديل</a><form method="post" style="display:inline"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($l['id']) ?>"><button>حذف</button></form></li><?php endforeach; ?></ul></main></body></html>
