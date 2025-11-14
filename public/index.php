<?php
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
error_log("SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME']);

// require_once is used to load a physical file by its path.
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Config.php';
if (!class_exists('\services\ProductosService', false)) {
    require_once __DIR__ . '/../src/services/ProductosService.php';
}

require_once __DIR__ . '/../src/services/UsersService.php';
require_once __DIR__ . '/../src/services/SessionService.php';

// use is used to shorten class names by importing namespaces.
use config\Config;
use services\ProductosService;
use services\UsersService;
use services\SessionService;

// manually dependency injection.
$config = Config::getInstance();
$db = $config->db;
$productosService = new ProductosService($db);
$usersService = new UsersService($db);
$sessionService = SessionService::getInstance();

// This block parses the URL sent by the browser
// and extracts the actual path used for route matching.
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// basePath = website's root path. Used to prevent redirect errors.
$basePath = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
if (strpos($requestPath, $basePath) === 0) {
    $requestPath = substr($requestPath, strlen($basePath));
}

// If requestPath becomes empty after base path removal, set it to '/'
if (empty($requestPath)) {
    $requestPath = '/';
}

// Ensure requestPath starts with a leading slash for routing
if (substr($requestPath, 0, 1) !== '/') {
    $requestPath = '/' . $requestPath;
}

error_log("Calculated basePath: " . $basePath);
error_log("Calculated requestPath: " . $requestPath);


// Set roles similar static
$session = SessionService::getInstance();

$deny = function() {
    $basePath = rtrim((string)($GLOBALS['basePath'] ?? ''), '/');
    header('Location: ' . $basePath . '/?error=forbidden');
    exit;
};

// Define routes
$routes = [
    '/' => function() use ($productosService) {
        $productos = $productosService->findAllWithCategoryName();
        require_once __DIR__ . '/../app/header.php';
        // The main content of index.php (product list)
        ?>
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Listado de Productos</h1>
                <a href="/create" class="btn btn-primary">Nuevo producto</a>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Categoría</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td><?= htmlspecialchars($producto->marca) ?></td>
                            <td><?= htmlspecialchars($producto->modelo) ?></td>
                            <td><?= htmlspecialchars($producto->precio) ?></td>
                            <td><?= htmlspecialchars($producto->stock) ?></td>
                            <td><?= htmlspecialchars($producto->categoria_nombre ?? '') ?></td>
                            <td>
                                <a href="/details?id=<?= $producto->id ?>" class="btn btn-info btn-sm">Detalles</a>
                                <a href="/update?id=<?= $producto->id ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="/update-image?id=<?= $producto->id ?>" class="btn btn-secondary btn-sm">Imagen</a>
                                <a href="/delete?id=<?= $producto->id ?>" class="btn btn-danger btn-sm">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        require_once __DIR__ . '/../app/footer.php';
    },
    '/create' => function() use ($session, $deny) {
        if (!$session->hasRole('ADMIN')) return $deny();
        require_once __DIR__ . '/../app/create.php';
    },
    '/details' => function() {
        require_once __DIR__ . '/../app/details.php';
    },
    '/update' => function() use ($session, $deny) {
        if (!$session->hasRole('ADMIN')) return $deny();
        require_once __DIR__ . '/../app/update.php';
    },
    '/update-image' => function() use ($session, $deny) {
        if (!$session->hasRole('ADMIN')) return $deny();
        require_once __DIR__ . '/../app/update-image.php';
    },
     // ... routes
    '/update-image-file' => function() use ($session, $deny) {
        if (!$session->hasRole('ADMIN')) return $deny();
        require_once __DIR__ . '/../app/update_image_file.php';
    },

    // another name
    '/update_image_file.php' => function() use ($session, $deny) {
        if (!$session->hasRole('ADMIN')) return $deny();
        require_once __DIR__ . '/../app/update_image_file.php';
    },
    '/delete' => function() use ($session, $deny) {
        if (!$session->hasRole('ADMIN')) return $deny();
        require_once __DIR__ . '/../app/delete.php';
    },
    '/login' => function() {
        require_once __DIR__ . '/../app/login.php';
    },
    '/logout' => function() {
        require_once __DIR__ . '/../app/logout.php';
    },
    '/role_toggle' => function() {
        require_once __DIR__ . '/../app/role_toggle.php';
    },
];

// Dispatcher checks whether requestPath (e.g. "/", "/create", "/update-image")
// exists in the $routes array.
if (array_key_exists($requestPath, $routes)) {
    $routes[$requestPath]();
} else {
    // Handle 404 Not Found by redirecting to the homepage
    header('Location: ' . $basePath);
    exit;
}

