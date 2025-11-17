<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/services/SessionService.php';
use services\SessionService;

$session = SessionService::getInstance();
$userName = $session->getNombre();
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tienda Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
      <div class="container">
        <a class="navbar-brand" href="/">🛍️ Tienda</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
          <?php $isAdmin = \services\SessionService::getInstance()->hasRole('ADMIN'); ?>
          <script>
            
          // Block non-admin users—show alert and stop the action.
          (function(){
            const IS_ADMIN = <?= $isAdmin ? 'true' : 'false' ?>;
            if (IS_ADMIN) return;

            const SELECTOR = [
              'a[href^="/create"]',
              'a[href^="/update?"]',
              'a[href^="/update-image"]',
              'a[href^="/delete?"]',
              'form[action^="/update-image-file"] button[type="submit"]'
            ].join(',');

            // This JS blocks unauthorized clicks and shows a "forbidden" message.
            document.addEventListener('click', function(e){
              const el = e.target.closest(SELECTOR); // SELECTOR marks elements that require admin permission.
              if (!el) return;
              e.preventDefault();
              alert('Acceso denegado por falta de permisos.');
            });

            // Prevent users from cheating by 
            // sending direct POST/GET requests to backend APIs.
            document.addEventListener('submit', function(e){
              const f = e.target;
              const action = (f.getAttribute('action') || '');
              if (!IS_ADMIN && (
                  action.startsWith('/create') ||
                  action.startsWith('/update') ||
                  action.startsWith('/update-image-file') ||
                  action.startsWith('/delete') ||
              )) {
                e.preventDefault();
                alert('Acceso denegado por falta de permisos.');
              }
            }, true);
          })();
          </script>

        
        </button>
        <div class="collapse navbar-collapse" id="nav">
          <div class="d-flex align-items-center ms-auto">
            <?php if ($session->isLogged()): ?>
              <form action="/role_toggle" method="post" class="d-inline">
                <button type="submit" class="btn btn-sm <?php echo $session->isAdmin() ? 'btn-warning' : 'btn-outline-warning'; ?>">
                  <?php echo $session->isAdmin() ? 'Admin ON' : 'Admin OFF'; ?>
                </button>
              </form>
              <a class="btn btn-sm btn-outline-light ms-2" href="/logout">Logout</a>
            <?php else: ?>
              <a class="btn btn-sm btn-warning ms-2" href="/login">Login</a>
            <?php endif; ?>
          </div>
      </div>
     </div>
</nav>
    <main class="container">
      <script>
    const sp = new URLSearchParams(location.search);

    // If the error is "forbidden", it shows an alert message.
    // This code does not check permissions — it only displays the message.
    if (sp.get('error') === 'forbidden') { alert('Acceso denegado por falta de permisos'); sp.delete('error'); history.replaceState(null,'', location.pathname + (sp.toString()?('?'+sp.toString()):'')); }
</script>

