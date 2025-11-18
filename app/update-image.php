<?php
// require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Config.php';
if (!class_exists('\services\ProductosService', false)) {
    require_once __DIR__ . '/../src/services/ProductosService.php';
}

use config\Config;
use services\ProductosService;

$config  = Config::getInstance();
$pdo     = $config->db;
$service = new ProductosService($pdo);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: /'); exit; }

$producto = $service->findById((int)$id);
if (!$producto) { header('Location: /'); exit; }

$ok    = isset($_GET['ok']);
$error = $_GET['error'] ?? ($_GET['error_dbg'] ?? null);

/* ===== 正規化圖片 URL（關鍵修正）===== */
$rawImg = trim((string)($producto->imagen ?? ''));
$publicPrefix = '/var/www/html/public';
if ($rawImg !== '' && str_starts_with($rawImg, $publicPrefix)) {
    // 存成實體路徑時改成相對 URL
    $rawImg = substr($rawImg, strlen($publicPrefix)); // -> /uploads/xxx.jpg
}
if ($rawImg !== '' && !str_starts_with($rawImg, '/uploads/')) {
    // 少了前綴時補上
    $rawImg = '/uploads/' . ltrim($rawImg, '/');
}
$imgUrl = $rawImg !== '' ? $rawImg . (str_contains($rawImg, '?') ? '&' : '?') . 'v=' . time()
                         : 'https://via.placeholder.com/300x200?text=Imagen';
/* =================================== */

require_once __DIR__ . '/header.php';
?>
<div class="container my-4">
  <h1 class="mb-4">Actualizar imagen</h1>

  <?php if ($ok): ?>
    <div class="alert alert-success">Imagen actualizada correctamente.</div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger">Error al subir la imagen: <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-md-4">
      <h5>Imagen actual</h5>
      <img src="<?= htmlspecialchars($imgUrl) ?>" class="img-fluid rounded border" alt="Imagen actual">
      <dl class="mt-3">
        <dt>ID</dt><dd><?= (int)$producto->id ?></dd>
        <dt>Marca</dt><dd><?= htmlspecialchars((string)$producto->marca) ?></dd>
        <dt>Modelo</dt><dd><?= htmlspecialchars((string)$producto->modelo) ?></dd>
      </dl>
    </div>

    <div class="col-md-8">
      <h5>Subir nueva imagen (JPEG/PNG)</h5>
      <form action="/update_image_file.php" method="post" enctype="multipart/form-data" class="p-3 border rounded">
        <input type="hidden" name="id" value="<?= (int)$producto->id ?>">
        <div class="mb-3">
          <input type="file" name="image" class="form-control" accept="image/jpeg,image/png" required>
        </div>
        <p class="text-muted">Tamaño sugerido ≤ 5MB（según configuración del servidor）。</p>
        <button class="btn btn-primary">Actualizar</button>
        <a class="btn btn-secondary" href="/details?id=<?= (int)$producto->id ?>">Volver</a>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
