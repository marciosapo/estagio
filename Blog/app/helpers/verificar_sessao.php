<?php
if (isset($_SESSION['user']) && !isset($_SESSION['token'])){
    session_unset();
    session_destroy();
    header("Location: /Blog");
    exit;
}
if (isset($_SESSION['user']) && isset($_SESSION['token'])) {
    require_once '../app/models/User.php';

    $userModel = new User();
    $id_user = verificarToken($_SESSION['token'], $userModel->db);

    if (!$id_user) {
        session_unset();
        session_destroy();
        header("Location: /Blog");
        exit;
    }
}
?>