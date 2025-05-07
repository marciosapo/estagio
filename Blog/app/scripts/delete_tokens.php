<?php
// Configuração da base de dados
$host = 'localhost';
$dbname = 'blog';
$user_db = 'root';
$pass_db = 'Marcio-158333';
$port_db = '3306';

try {
    $db = new PDO("mysql:host=$host;port=$port_db;dbname=$dbname;charset=utf8", $user_db, $pass_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar todos os tokens válidos
    $checkTokenQuery = "SELECT DISTINCT username, token FROM tokens WHERE expira <= NOW()";
    $checkTokenStmt = $db->prepare($checkTokenQuery);
    $checkTokenStmt->execute();

    $tokens = $checkTokenStmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$tokens) {
        echo json_encode(['message' => 'Nenhum token ativo encontrado que tenha expirado.']);
        exit;
    }

    $apagados = [];
    foreach ($tokens as $row) {
        $username = $row['username'];
        $token = $row['token'];

        // Apagar token
        $tokenQuery = "DELETE FROM tokens WHERE username = :username";
        $tokenStmt = $db->prepare($tokenQuery);
        $tokenStmt->bindParam(':username', $username);

        if ($tokenStmt->execute()) {
            $apagados[] = ['username' => $username, 'token' => $token];
        }
    }

    echo json_encode(['mensagem' => 'Tokens expirados apagados com sucesso', 'apagados' => $apagados]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro na conexão: ' . $e->getMessage()]);
    exit;
}
?>