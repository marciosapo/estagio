<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../app/helpers/verificar_sessao.php';
require_once '../app/helpers/avatar.php';
require_once '../app/core/Router.php';

$router = new Router();
$router->route();

?>