<?php
$host = 'localhost';
$dbname = 'blog';
$user_db = 'root';
$pass_db = 'Marcio-158333';
$port_db = '3306';

try {
    $db = new PDO("mysql:host=$host;port=$port_db;dbname=$dbname;charset=utf8", $user_db, $pass_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $deleteQuery = "
        DELETE FROM tokens 
        WHERE apagar = 1 AND TIMESTAMPDIFF(MINUTE, data_marcado_apagar, NOW()) >= 15
    ";
    $stmt = $db->prepare($deleteQuery);
    $stmt->execute();
    echo json_encode(['mensagem' => 'Tokens apagados com sucesso.']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro na conexão: ' . $e->getMessage()]);
    exit;
}
?>