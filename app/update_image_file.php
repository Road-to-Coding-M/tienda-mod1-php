<?php
// require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Config.php';
if (!class_exists('\services\ProductosService', false)) {
    require_once __DIR__ . '/../src/services/ProductosService.php';
}


use config\Config;
use services\ProductosService;

$config    = Config::getInstance();
$pdo       = $config->db;
$productos = new ProductosService($pdo);

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: /update-image?id=0&error=bad_id'); exit; }

if (!isset($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    header('Location: /update-image?id=' . $id . '&error=no_file'); exit;
}

$f = $_FILES['image'];
if ($f['error'] !== UPLOAD_ERR_OK) {
    header('Location: /update-image?id=' . $id . '&error=upload_' . (int)$f['error']); exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $f['tmp_name']);
finfo_close($finfo);
$ext = $mime === 'image/jpeg' ? 'jpg' : ($mime === 'image/png' ? 'png' : null);
if (!$ext) { header('Location: /update-image?id=' . $id . '&error=bad_type'); exit; }

$uploadsFs = realpath(__DIR__ . '/../public') . '/uploads';
if (!is_dir($uploadsFs)) { @mkdir($uploadsFs, 0775, true); }

$filename = 'p' . $id . '_' . date('Ymd_His') . '.' . $ext;
$destFs   = $uploadsFs . '/' . $filename;
$destUrl  = '/uploads/' . $filename;

if (!move_uploaded_file($f['tmp_name'], $destFs)) {
    header('Location: /update-image?id=' . $id . '&error=move_failed'); exit;
}

try {
    $productos->updateImage((int)$id, $destUrl);
    header('Location: /update-image?id=' . $id . '&ok=1'); exit;
} catch (\Throwable $e) {
    @unlink($destFs);
    header('Location: /update-image?id=' . $id . '&error_dbg=db_exception'); exit;
}