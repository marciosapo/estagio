<?php

function avatar() {
    $db = Database::getInstance(); 
    $user = $_SESSION['user'];
    $stmt = $db->prepare("SELECT id FROM users WHERE username = :user");
    $stmt->bindValue(':user', $user, PDO::PARAM_STR);
    $stmt->execute();
    $id_user = $stmt->fetchColumn();
    if (!$id_user) {
        return null;
    }
    $stmt = $db->prepare("SELECT imagem FROM users WHERE id = :id");
    $stmt->bindValue(':id', $id_user, PDO::PARAM_INT);
    $stmt->execute();
    $imagem_binaria = $stmt->fetchColumn();
    if ($imagem_binaria) {
        $mime = "image/jpeg";
        $data = base64_encode($imagem_binaria);
        return "data:$mime;base64,$data";
    } else {
        return "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=";
    }
}

?>