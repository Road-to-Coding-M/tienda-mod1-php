<?php
// app/login.php — final

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/services/UsersService.php';
require_once __DIR__ . '/../src/services/SessionService.php';

use config\Config;
use services\UsersService;
use services\SessionService;

$config       = Config::getInstance();
$usersService = new UsersService($config->db);
$session      = SessionService::getInstance();

$error = null;

// Handle login before any output (including header.php)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = (string)($_POST['password'] ?? '');

    try {
        // authenticate() internally handles $2a$ → $2y$ compatibility for bcrypt hashes
        $user = $usersService->authenticate($u, $p);
        $session->login($user);
        header('Location: /');
        exit; // stop execution to avoid sending further output
    } catch (Throwable $e) {
        // do not leak hash or technical details
        $error = 'Usuario o contraseña inválidos';
    }
}

// Only render the page after handling the login logic
require_once __DIR__ . '/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h1 class="h4 mb-3">Login</h1>
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" novalidate>
          <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input name="username" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">Entrar</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
