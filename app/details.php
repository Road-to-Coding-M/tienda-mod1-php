<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Config.php';
if (!class_exists('\services\ProductosService', false)) {
    require_once __DIR__ . '/../src/services/ProductosService.php';
}

require_once __DIR__ . '/header.php';

use config\Config;
use services\ProductosService;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /');
    exit;
}

$config = Config::getInstance();
$db = $config->db;
$productosService = new ProductosService($db);
$producto = $productosService->findById($id);

if (!$producto) {
    header('Location: /');
    exit;
}
?>
<div class="container">
  <h2>Detalles del Producto</h2>
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">
        <?= htmlspecialchars($producto->marca) ?> <?= htmlspecialchars($producto->modelo) ?>
      </h5>
      <p class="card-text"><strong>Precio:</strong> <?= htmlspecialchars($producto->precio) ?> &euro;</p>
      <p class="card-text"><strong>Stock:</strong> <?= htmlspecialchars($producto->stock) ?> unidades</p>
      <p class="card-text"><strong>Categoría:</strong> <?= htmlspecialchars($producto->categoria_nombre) ?></p>

      <div class="my-3">
        <?php
        // Dynamically determine the image source
        $rawImg    = $producto->imagen ?? '';
        $uploadUrl = rtrim($config->uploadUrl ?? '/uploads', '/');

        if ($rawImg === '' || $rawImg === null) {
            // No image available → generate a placeholder using the brand name
            $placeholderText = trim((string)($producto->marca ?? '')) ?: 'Producto';
            $imgSrc = 'https://via.placeholder.com/300x200?text=' . urlencode($placeholderText);
        } else {
            // image →  URL or / ，otherwise use uploads 
            if (preg_match('~^https?://~', $rawImg) || str_starts_with($rawImg, '/')) {
                $imgSrc = $rawImg;
            } else {
                $imgSrc = $uploadUrl . '/' . ltrim($rawImg, '/');
            }
        }

        // Cache-busting to ensure the updated image is shown immediately
        $stamp  = $producto->updated_at ?? time();
        $imgSrc = $imgSrc . (str_contains($imgSrc, '?') ? '&' : '?') . 'v=' . urlencode((string)$stamp);
        ?>
        <img src="<?= htmlspecialchars($imgSrc) ?>" alt="Imagen del producto" style="max-width:300px;height:auto;">
      </div>

      <a href="/" class="btn btn-primary">Volver</a>
      <?php if ($session->hasRole('ADMIN')): ?>
        <a href="/update-image?id=<?= $producto->id ?>" class="btn btn-secondary">Actualizar Imagen</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
