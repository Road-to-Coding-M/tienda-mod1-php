<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/services/SessionService.php';

$session = \services\SessionService::getInstance();
if (!$session->isLogged()) {
    header('Location: /');
    exit;
}
$session->toggleAdmin();
$back = $_SERVER['HTTP_REFERER'] ?? '/';
header("Location: $back");
exit;
