<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
$token = $_SESSION['token'] ?? null;


if (!$token) {
    echo json_encode(['erro' => 'Sem token']);
    exit;
}

try {
    $db = new PDO("mysql:host=localhost;dbname=blog;charset=utf8", 'root', 'Marcio-158333');
    $stmt = $db->prepare("SELECT apagar FROM tokens WHERE token = :token");
    $stmt->execute(['token' => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode(['apagar' => $row['apagar']]);
    } else {
        echo json_encode(['erro' => 'Token não encontrado']);
    }

} catch (PDOException $e) {
    echo json_encode(['erro' => $e->getMessage()]);
}
?>