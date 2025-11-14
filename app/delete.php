<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Config.php';
if (!class_exists('\services\ProductosService', false)) {
    if (!class_exists('\services\ProductosService', false)) {
    require_once __DIR__ . '/../src/services/ProductosService.php';
}

}

use config\Config;
use services\ProductosService;

$config = Config::getInstance();
$db = $config->db;
$productos = new ProductosService($db);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id === false || $id === null) {
        header('Location: /');
        exit;
    }
    // 刪除（軟刪）
    $productos->softDelete((int)$id);
    header('Location: /');
    exit;
}

// GET：顯示確認
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: /'); exit; }

require_once __DIR__ . '/header.php';
?>
<div class="container">
  <h2>Eliminar producto</h2>
  <p>¿Seguro que quieres eliminar este producto?</p>
  <form method="post" action="/delete">
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <button type="submit" class="btn btn-danger">Eliminar</button>
    <a href="/" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
