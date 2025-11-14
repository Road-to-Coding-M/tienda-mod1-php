<?php
// app/update.php — final

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Config.php';
if (!class_exists('\services\ProductosService', false)) {
    require_once __DIR__ . '/../src/services/ProductosService.php';
}
require_once __DIR__ . '/../src/models/Producto.php';

use config\Config;
use services\ProductosService;
use models\Producto;

$config = Config::getInstance();
$db = $config->db;
$productosService = new ProductosService($db);

// 查 categorias：回傳關聯陣列（使用全域 \PDO 常量，避免 use 造成警告輸出）
$categorias = $db
  ->query("SELECT id, nombre FROM categorias WHERE is_deleted = FALSE ORDER BY nombre")
  ->fetchAll(\PDO::FETCH_ASSOC);

$errors = [];
$producto = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $id           = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $marca        = htmlspecialchars(trim($_POST['marca'] ?? ''), ENT_QUOTES, 'UTF-8');
    $modelo       = htmlspecialchars(trim($_POST['modelo'] ?? ''), ENT_QUOTES, 'UTF-8');
    $precio       = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $stock        = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $categoria_id = trim((string)($_POST['categoria_id'] ?? ''));

    if ($id === false || $id === null)           $errors['id'] = 'ID inválido.';
    if ($marca === '')                            $errors['marca'] = 'La marca es obligatoria.';
    if ($modelo === '')                           $errors['modelo'] = 'El modelo es obligatorio.';
    if ($precio === false || $precio < 0)         $errors['precio'] = 'El precio no es válido.';
    if ($stock === false  || $stock < 0)          $errors['stock'] = 'El stock no es válido.';
    if ($categoria_id === '')                     $errors['categoria_id'] = 'Seleccione una categoría.';

    if (!$errors) {
        $producto = $productosService->findById((int)$id);
        if ($producto) {
            // 以關聯陣列比對取得分類名稱
            $categoria_nombre = '';
            foreach ($categorias as $cat) {
                $cid = (string)($cat['id'] ?? '');
                if ($cid === (string)$categoria_id) {
                    $categoria_nombre = (string)($cat['nombre'] ?? '');
                    break;
                }
            }

            $producto->marca            = $marca;
            $producto->modelo           = $modelo;
            $producto->precio           = (float)$precio;
            $producto->stock            = (int)$stock;
            $producto->categoriaId      = $categoria_id;
            $producto->categoria_nombre = $categoria_nombre;

            // 注意：update() 內已移除 descripcion 欄位
            $productosService->update($producto);

            header('Location: /');
            exit;
        }
        header('Location: /');
        exit;
    }

    if (!empty($id)) {
        $producto = $productosService->findById((int)$id);
    }
} else {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) { header('Location: /'); exit; }
    $producto = $productosService->findById((int)$id);
}

require_once __DIR__ . '/header.php';
?>
<div class="container">
  <h2>Editar Producto</h2>
  <?php if ($producto): ?>
  <form action="/update" method="POST" novalidate>
    <input type="hidden" name="id" value="<?= (int)$producto->id ?>">

    <div class="mb-3">
      <label for="marca" class="form-label">Marca</label>
      <input type="text" class="form-control" id="marca" name="marca" value="<?= htmlspecialchars((string)$producto->marca) ?>" required>
      <?php if (isset($errors['marca'])): ?><div class="text-danger"><?= $errors['marca'] ?></div><?php endif; ?>
    </div>

    <div class="mb-3">
      <label for="modelo" class="form-label">Modelo</label>
      <input type="text" class="form-control" id="modelo" name="modelo" value="<?= htmlspecialchars((string)$producto->modelo) ?>" required>
      <?php if (isset($errors['modelo'])): ?><div class="text-danger"><?= $errors['modelo'] ?></div><?php endif; ?>
    </div>

    <div class="mb-3">
      <label for="precio" class="form-label">Precio</label>
      <input type="number" step="0.01" class="form-control" id="precio" name="precio" value="<?= htmlspecialchars((string)$producto->precio) ?>" required>
      <?php if (isset($errors['precio'])): ?><div class="text-danger"><?= $errors['precio'] ?></div><?php endif; ?>
    </div>

    <div class="mb-3">
      <label for="stock" class="form-label">Stock</label>
      <input type="number" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars((string)$producto->stock) ?>" required>
      <?php if (isset($errors['stock'])): ?><div class="text-danger"><?= $errors['stock'] ?></div><?php endif; ?>
    </div>

    <div class="mb-3">
      <label for="categoria_id" class="form-label">Categoría</label>
      <select class="form-control" id="categoria_id" name="categoria_id" required>
        <option value="">Seleccione una categoría</option>
        <?php foreach ($categorias as $cat): ?>
          <?php
            $cid = (string)($cat['id'] ?? '');
            $sel = ((string)$producto->categoriaId === $cid) ? 'selected' : '';
          ?>
          <option value="<?= htmlspecialchars($cid) ?>" <?= $sel ?>>
            <?= htmlspecialchars((string)($cat['nombre'] ?? '')) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if (isset($errors['categoria_id'])): ?><div class="text-danger"><?= $errors['categoria_id'] ?></div><?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="/" class="btn btn-secondary">Cancelar</a>
  </form>
  <?php else: ?>
    <p>Producto no encontrado.</p>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
