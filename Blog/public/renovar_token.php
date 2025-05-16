<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}
try {
    $db = new PDO("mysql:host=localhost;dbname=blog;charset=utf8", 'root', 'Marcio-158333');
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM tokens 
        WHERE username = :user 
    ");
    $stmt->bindValue(':user', $_SESSION['user']);
    $stmt->execute();
    $temToken = $stmt->fetchColumn();
    if ($temToken == 0) {
        unset($_SESSION['user']);
        unset($_SESSION['token']);
        unset($_SESSION['nivel']);
        header("Location: /Blog");
        exit;
    }
    $datetime = new DateTime('now', new DateTimeZone('Europe/Lisbon'));
    $datetime->add(new DateInterval('PT1H'));
    $termina = $datetime->format('Y-m-d H:i:s');
    $stmt = $db->prepare("UPDATE tokens SET expira = :expira, apagar = 0, expira_apagar = NULL WHERE username = :user");
    $stmt->bindValue(':user', trim($_SESSION['user']), PDO::PARAM_STR);
    $stmt->bindParam(':expira', $termina);
    if ($stmt->execute()) {
        echo json_encode(['mensagem' => 'Token renovado com sucesso.']);
    } else {
        echo json_encode(['erro' => 'Erro ao renovar token.']);
    }

} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro na base de dados: ' . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    echo json_encode(['erro' => 'Erro interno: ' . $e->getMessage()]);
    exit;
}
?>