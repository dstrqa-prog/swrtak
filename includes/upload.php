<?php

declare(strict_types=1);

require_once __DIR__ . '/security.php';

function upload_image(array $file, string $folder): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'فشل رفع الملف.'];
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'الحد الأقصى للصورة 2MB.'];
    }

    $tmp = $file['tmp_name'] ?? '';
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = is_string($tmp) ? finfo_file($finfo, $tmp) : '';
    finfo_close($finfo);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        return ['ok' => false, 'error' => 'نوع الملف غير مسموح.'];
    }

    $targetDir = __DIR__ . '/../uploads/' . trim($folder, '/');
    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
        return ['ok' => false, 'error' => 'تعذر إنشاء مجلد الرفع.'];
    }

    $name = 'img_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $targetPath = $targetDir . '/' . $name;

    if (!move_uploaded_file($tmp, $targetPath)) {
        return ['ok' => false, 'error' => 'تعذر حفظ الصورة.'];
    }

    return ['ok' => true, 'path' => '/uploads/' . trim($folder, '/') . '/' . $name];
}
