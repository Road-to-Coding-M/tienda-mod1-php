<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/services/ProductosService.php';
require_once __DIR__ . '/../src/services/CategoriasService.php';
require_once __DIR__ . '/../src/models/Producto.php';
require_once __DIR__ . '/../src/services/SessionService.php';

use config\Config;
use services\ProductosService;
use services\CategoriasService;
use models\Producto;
use services\SessionService;
use Ramsey\Uuid\Uuid;

$session = SessionService::getInstance();
if (!$session->isAdmin()) {
    header('Location: /');
    exit;
}

$config = Config::getInstance();
$db = $config->db;

$categoriasService = new CategoriasService($db);
$categorias = $categoriasService->findAll();

$errors = [];

// --- Added: handle optional image upload and set $_POST['imagen'] automatically ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && isset($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
    $file = $_FILES['image'];
    $allowed = ['image/jpeg'=>'.jpg','image/png'=>'.png','image/webp'=>'.webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (isset($allowed[$mime])) {
        $ext = $allowed[$mime];
        // prefer uuid for filename if present, otherwise generate one
        $uuid = $_POST['uuid'] ?? (class_exists('Ramsey\Uuid\Uuid') ? \Ramsey\Uuid\Uuid::uuid4()->toString() : bin2hex(random_bytes(8)));
        $filename = 'product_' . $uuid . $ext;
        $uploadDir = $config->__get('uploadPath') ?: (__DIR__ . '/../public/uploads/');
        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
        $destPath = rtrim($uploadDir,'/').'/'.$filename;
        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $baseUrl = $config->__get('uploadUrl') ?: '/uploads/';
            $_POST['imagen'] = rtrim($baseUrl,'/') . '/' . $filename;
            // keep the uuid in post so downstream save can use it if applicable
            $_POST['uuid'] = $uuid;
        }
    }
}
// --- end added block ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = htmlspecialchars(trim($_POST['marca'] ?? ''));
    $modelo = htmlspecialchars(trim($_POST['modelo'] ?? ''));
    $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $categoria_id = htmlspecialchars(trim($_POST['categoria_id'] ?? ''));

    if (empty($marca)) {
        $errors['marca'] = 'La marca es obligatoria.';
    }
    if (empty($modelo)) {
        $errors['modelo'] = 'El modelo es obligatorio.';
    }
    if ($precio === false || $precio < 0) {
        $errors['precio'] = 'El precio no es válido.';
    }
    if ($stock === false || $stock < 0) {
        $errors['stock'] = 'El stock no es válido.';
    }
    if (empty($categoria_id)) {
        $errors['categoria_id'] = 'Seleccione una categoría.';
    }

    if (count($errors) === 0) {
        $categoria = null;
        foreach ($categorias as $cat) {
            if ($cat->id == $categoria_id) {
                $categoria = $cat;
                break;
            }
        }

        if ($categoria === null) {
            $errors['categoria_id'] = 'La categoría seleccionada no es válida.';
        } else {
            $producto = new Producto(
                null, Uuid::uuid4()->toString(), $marca, $modelo, $precio, $stock, $_POST['imagen'] ?? null, $categoria->id, $categoria->nombre
            );

            $productosService = new ProductosService($db);
            $productosService->save($producto);

            header('Location: /');
            exit;
        }
    }
}
?>

<?php require_once __DIR__ . '/header.php'; ?>

<div class="container">
    <h2>Nuevo Producto</h2>
    <form enctype="multipart/form-data" action="/create" method="POST">
        <div class="mb-3">
            <label for="marca" class="form-label">Marca</label>
            <input type="text" class="form-control" id="marca" name="marca" required value="<?= htmlspecialchars($marca ?? '') ?>">
            <?php if (isset($errors['marca'])): ?>
                <div class="text-danger"><?= $errors['marca'] ?></div>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="modelo" class="form-label">Modelo</label>
            <input type="text" class="form-control" id="modelo" name="modelo" required value="<?= htmlspecialchars($modelo ?? '') ?>">
            <?php if (isset($errors['modelo'])): ?>
                <div class="text-danger"><?= $errors['modelo'] ?></div>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="precio" class="form-label">Precio</label>
            <input type="number" step="0.01" class="form-control" id="precio" name="precio" required value="<?= htmlspecialchars((string)($precio ?? '')) ?>">
            <?php if (isset($errors['precio'])): ?>
                <div class="text-danger"><?= $errors['precio'] ?></div>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="stock" class="form-label">Stock</label>
            <input type="number" class="form-control" id="stock" name="stock" required value="<?= htmlspecialchars((string)($stock ?? '')) ?>">
            <?php if (isset($errors['stock'])): ?>
                <div class="text-danger"><?= $errors['stock'] ?></div>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="categoria_id" class="form-label">Categoría</label>
            <select class="form-control" id="categoria_id" name="categoria_id" required>
                <option value="">Seleccione una categoría</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= $categoria->id ?>" <?= (isset($categoria_id) && $categoria_id == $categoria->id) ? 'selected' : '' ?>><?= htmlspecialchars($categoria->nombre) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['categoria_id'])): ?>
                <div class="text-danger"><?= $errors['categoria_id'] ?></div>
            <?php endif; ?>
        </div>
        <div class="mb-3"><label for="image" class="form-label">Imagen (opcional)</label><input type="file" class="form-control" id="image" name="image" accept="image/*"></div>
<button type="submit" class="btn btn-primary">Crear</button>
        <a href="/" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
