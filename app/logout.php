<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/services/SessionService.php';

use services\SessionService;
$session = SessionService::getInstance();
$session->logout();
header('Location: /');
exit;
