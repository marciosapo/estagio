<?php
$host = 'localhost';
$dbname = 'blog';
$user_db = 'root';
$pass_db = 'Marcio-158333';
$port_db = '3306';
try {
    $db = new PDO("mysql:host=$host;port=$port_db;dbname=$dbname;charset=utf8", $user_db, $pass_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $checkTokenQuery = "SELECT username, token FROM tokens WHERE expira <= NOW() AND apagar = 0";
    $checkTokenStmt = $db->prepare($checkTokenQuery);
    $checkTokenStmt->execute();
    $tokens = $checkTokenStmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$tokens) {
        echo json_encode(['mensagem' => 'Nenhum token expirado por marcar.']);
        exit;
    }
    $apagados = [];
    foreach ($tokens as $row) {
        $username = $row['username'];
        $token = $row['token'];
        $updateQuery = "UPDATE tokens SET apagar = 1, expira_apagar = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE token = :token";
        $stmt = $db->prepare($updateQuery);
        $stmt->bindParam(':token', $token);
        if ($stmt->execute()) {
            $apagados[] = ['username' => $username, 'token' => $token];
        }
    }
    echo json_encode(['mensagem' => 'Tokens expirados marcados para apagar.', 'marcados' => $apagados]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro na conexão: ' . $e->getMessage()]);
    exit;
}
?>