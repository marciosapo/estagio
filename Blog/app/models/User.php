<?php 

require_once '../app/core/Database.php';
require_once '../app/config/config.php';
require_once '../app/helpers/tokens.php';

class User {
    public $db;
    public function __construct() {
        $this->db = Database::getInstance();
    }
    public function verificarTokenUser($token) {
        return verificarToken($token, $this->db);
    } 
    public function getUsers() {
        $query = "
            SELECT 
                users.id,
                users.username AS user,
                users.email,
                users.nome,
                users.criado,
                users.nivel,
                tokens.token As user_token,
                tokens.expira AS user_token_expira
            FROM users
            LEFT JOIN tokens ON tokens.username = users.username
            ORDER BY users.id
        ";
        $stmt = $this->db->query($query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = [];
        foreach ($rows as $row) {
            $userId = $row['id'];
            if (!isset($users[$userId])) {
                $status = $this->checkToken($row['user']);
                $users[$userId] = [
                    'id' => $userId,
                    'username' => $row['user'],
                    'email' => $row['email'],
                    'nome' => $row['nome'],
                    'criado' => $row['criado'],
                    'nivel' => $row['nivel'], 
                    'token' => $row['user_token'],
                    'token_expira' => $row['user_token_expira'],
                    'token_status' => $status['status']    
                ];
            }
        } 
        return array_values($users);
    }

    public function getUser($username) {
        $query = "
            SELECT 
                users.id,
                users.username AS user,
                users.email,
                users.nome,
                users.criado,
                users.nivel,
                users.pass,
                users.imagem,
                tokens.token AS user_token,
                tokens.expira AS user_token_expira
            FROM users
            LEFT JOIN tokens ON tokens.username = users.username
            WHERE users.username = :user
            ORDER BY users.id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user', trim($username), PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($row) {
            $status = $this->checkToken($username);
            return [
                'id' => $row['id'],
                'username' => $row['user'],
                'pass' => $row['pass'],
                'email' => $row['email'],
                'nome' => $row['nome'],
                'criado' => $row['criado'],
                'nivel' => $row['nivel'], 
                'token' => $row['user_token'],
                'imagem' => $row['imagem'], 
                'token_expira' => $row['user_token_expira'],
                'token_status' => $status['status'] ?? null
            ];
        }
    
        return null;
    }

    public function novoUser($username, $nome, $email, $pass) {
        $checkQuery = "SELECT COUNT(*) FROM users WHERE username = :user OR email = :email";
        $checkStmt = $this->db->prepare($checkQuery);
        $stmt->bindValue(':user', trim($username), PDO::PARAM_STR);
        $stmt->bindValue(':email', trim($email), PDO::PARAM_STR);
        $checkStmt->execute();
        $exists = $checkStmt->fetchColumn();
        if ($exists > 0) {
            return ['erro' => 'Nome de utilizador ou email já existe'];
        }
        $hash_password = password_hash($pass, PASSWORD_DEFAULT);
        $query = "INSERT INTO users(username, nome, email, pass, nivel) VALUES(:user, :nome, :email, :pass, 'User')";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':username', trim($username), PDO::PARAM_STR);
        $stmt->bindValue(':nome', trim($nome), PDO::PARAM_STR);
        $stmt->bindValue(':email', trim($email), PDO::PARAM_STR);
        $stmt->bindValue(':password', $hash_password, PDO::PARAM_STR);
        try {
            if ($stmt->execute()) {
                return ['mensagem' => 'Novo utilizador registado com sucesso'];
            } else {
                return ['erro' => 'Erro ao registar novo utilizador'];
            }
        } catch (Exception $e) {
            return ['erro' => 'Erro: ' . $e->getMessage()];
        }
    }

    public function apagarUser($username, $token) {
        $id_user = $this->verificarTokenUser($token);
        if (!$id_user) {
            return ['erro' => 'Token inválido ou expirado'];
        }
        $checkQuery = "SELECT nivel FROM users WHERE id = :id_user";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindValue(':id_user', trim($id_user), PDO::PARAM_INT);
        $checkStmt->execute();
        $nivel = $checkStmt->fetchColumn();
        if ($nivel !== 'Owner') {
            return ['erro' => 'Apenas utilizadores com nível Owner podem realizar esta operação.'];
        }
        $checkQuery = "SELECT COUNT(*) FROM users WHERE username = :user";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindValue(':user', trim($username), PDO::PARAM_STR);
        $checkStmt->execute();
        $exists = $checkStmt->fetchColumn();
        if ($exists <= 0) {
            return ['erro' => 'Nome de utilizador não existe'];
        }
        $query = "DELETE FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':username', trim($username), PDO::PARAM_STR);
        try {
            if ($stmt->execute()) {
                return ['mensagem' => 'Utilizador apagado com sucesso ' . $username];
            } else {
                return ['erro' => 'Erro ao apagar utilizador'];
            }
        } catch (Exception $e) {
            return ['erro' => 'Erro: ' . $e->getMessage()];
        }
    }
    
    public function addAdmin($user, $token){
        $id_user = $this->verificarTokenUser($token);
        if (!$id_user) {
            return ['erro' => 'Token inválido ou expirado'];
        }
        $checkQuery = "SELECT nivel FROM users WHERE id = :id_user";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindValue(':id_user', trim($id_user), PDO::PARAM_INT);
        $checkStmt->execute();
        $nivel = $checkStmt->fetchColumn();
        if ($nivel !== 'Owner') {
            return ['erro' => 'Apenas utilizadores com nível Owner podem realizar esta operação.'];
        }
        $checkQuery = "SELECT COUNT(*) FROM users WHERE username = :user ";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindValue(':user', trim($user), PDO::PARAM_STR);
        $checkStmt->execute();
        $exists = $checkStmt->fetchColumn();
        if ($exists <= 0) {
            return ['erro' => 'O utilizador ' . $user . ' não existe'];
        }
        $checkQuery = "SELECT nivel FROM users WHERE username = :user";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindValue(':user', trim($user), PDO::PARAM_STR);
        $checkStmt->execute();
        $nivel = $checkStmt->fetchColumn();
        if ($nivel == 'Owner') {
            return ['erro' => 'Não podes alterar o ' . $user . ' de Owner para Admin'];
        }
        if ($nivel == 'Admin') {
            return ['erro' => 'O ' . $user . ' já é Admin'];
        }
        $query = "
            UPDATE users SET 
                nivel = 'Admin'
            WHERE username = :user
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user', trim($user), PDO::PARAM_STR);
        try {
            if ($stmt->execute()) {
                return ['mensagem' => $user . ' alterado para Admin com sucesso!'];
            } else {
                return ['erro' => 'Erro ao alterar o ' . $user . ' para Admin'];
            }
        } catch (Exception $e) {
            return ['erro' => 'Erro: ' . $e->getMessage()];
        }
    } 
    public function updateUser($data) {
        $id_user = $this->verificarTokenUser($data['token']);
        if (!$id_user) {
            return ['erro' => 'Token inválido ou expirado'];
        }
        $checkQuery = "SELECT COUNT(*) FROM users WHERE email = :email AND id != :id";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindValue(':email', trim($data['email']), PDO::PARAM_STR);
        $checkStmt->bindValue(':id', trim($id_user), PDO::PARAM_INT);
        $checkStmt->execute();
        $exists = $checkStmt->fetchColumn();
        if ($exists > 0) {
            return ['erro' => 'Email já existe'];
        }
        $query = "
            UPDATE users SET 
                nome = :nome,
                email = :email,
                pass = :pass,
                imagem = :imagem
            WHERE id = :id
        ";
        if (!empty($data['pass'])) {
            if (!preg_match('/^\$2y\$/', $data['pass'])) {
                $hash_password = password_hash($data['pass'], PASSWORD_DEFAULT);
            } else {
                $hash_password = $data['pass'];
            }
        }
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':nome', trim($data['nome']), PDO::PARAM_STR);
        $stmt->bindValue(':email', trim($data['email']), PDO::PARAM_STR);
        $stmt->bindValue(':pass', $hash_password, PDO::PARAM_STR);

        if (isset($data['imagem']) && $data['imagem'] !== null) {
            $stmt->bindValue(':imagem', trim($data['imagem']), PDO::PARAM_LOB);
        } else {
            $stmt->bindValue(':imagem', null, PDO::PARAM_NULL);
        }
        $stmt->bindValue(':id', $id_user, PDO::PARAM_INT);
    
        try {
            if ($stmt->execute()) {
                return ['mensagem' => 'Dados do utilizador atualizados com sucesso'];
            } else {
                return ['erro' => 'Erro ao atualizar os dados do utilizador'];
            }
        } catch (Exception $e) {
            return ['erro' => 'Erro: ' . $e->getMessage()];
        }
    }
    
    public function recriarDB($data){
        $id_user = $this->verificarTokenUser($data['token']);
        if (!$id_user) {
            return ['erro' => 'Token inválido ou expirado'];
        }
        $checkQuery = "SELECT nivel FROM users WHERE id = :id_user";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
        $checkStmt->execute();
        $nivel = $checkStmt->fetchColumn();
        if ($nivel !== 'Owner') {
            return ['erro' => 'Apenas utilizadores com nível Owner podem realizar esta operação.'];
        }
        $dsn = 'mysql:host=localhost';
        $user = 'root';
        $pass = '';
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME;
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("DROP DATABASE IF EXISTS blog");
            $pdo->exec("CREATE DATABASE IF NOT EXISTS blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE blog");
            $pdo->exec("
                DROP TABLE IF EXISTS users;
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    nome VARCHAR(100) NOT NULL,
                    pass VARCHAR(255) NOT NULL,
                    criado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    nivel ENUM('Owner', 'Admin', 'User'),
                    imagem LONGBLOB
                );
            ");
            $pdo->exec("
                DROP TABLE IF EXISTS posts;
                CREATE TABLE IF NOT EXISTS posts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_user INT,
                    title VARCHAR(255),
                    post TEXT NOT NULL,
                    post_data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (id_user) REFERENCES users(id)
                );
            ");
            $pdo->exec("
                DROP TABLE IF EXISTS comentarios;
                CREATE TABLE IF NOT EXISTS comentarios (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_user INT,
                    id_post INT,
                    id_parent INT,
                    comentario TEXT NOT NULL,
                    post_data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (id_user) REFERENCES users(id),
                    FOREIGN KEY (id_post) REFERENCES posts(id)
                );
            ");
            $pdo->exec("
                DROP TABLE IF EXISTS tokens;
                CREATE TABLE IF NOT EXISTS tokens (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    token VARCHAR(255) NOT NULL,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    expira DATETIME NOT NULL
                );
            ");
            $stmt = $pdo->prepare("INSERT INTO users (username, email, nome, pass, nivel) VALUES (?, ?, ?, ?, ?)");
            $users = [
                ['root', 'root@root.com', 'root', password_hash('1234', PASSWORD_DEFAULT), 'Owner'],
                ['marcio', 'marcio@root.com', 'marcio', password_hash('1234', PASSWORD_DEFAULT), 'Owner'],
                ['rui', 'rui@root.com', 'rui', password_hash('1234', PASSWORD_DEFAULT), 'User']
            ];

            foreach ($users as $user) {
                $stmt->execute($user);
            }
            return ['mensagem' => 'Base de dados e tabelas criadas com sucesso. Utilizadores inseridos com password segura.'];
        } catch (PDOException $e) {
            return ['erro' => 'ERRO: ' . $e->getMessage()];
        }
    } 
    public function renovarToken($user){
        $checkTokenQuery = "SELECT * FROM tokens WHERE username = :username AND expira > NOW() LIMIT 1";
        $checkTokenStmt = $this->db->prepare($checkTokenQuery);
        $checkTokenStmt->bindValue(':username', trim($user), PDO::PARAM_STR);
        $checkTokenStmt->execute();
        $existingToken = $checkTokenStmt->fetch(PDO::FETCH_ASSOC);
        if ($existingToken) {
            $datetime = new DateTime('now', new DateTimeZone('Europe/Lisbon'));
            $datetime->add(new DateInterval('PT1H'));
            $termina = $datetime->format('Y-m-d H:i:s');
            $tokenQuery = "UPDATE tokens set expira = :expira WHERE username = :username";
            $tokenStmt = $this->db->prepare($tokenQuery);
            $tokenStmt->bindValue(':username', trim($user), PDO::PARAM_STR);
            $tokenStmt->bindParam(':expira', $termina);
            if ($tokenStmt->execute()) {
                return ['messagem' => 'Token renovado com sucesso', 'token' => $_SESSION['token']];
            } else {
                return ['error' => 'Erro ao renovar o token'];
            }
        } 
    }
    public function getToken($user, $pass){
        $this->checkToken($user);
        $query = "SELECT id, username, pass, nivel FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':username', trim($user), PDO::PARAM_STR);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userData || !password_verify($pass, $userData['pass'])) {
            return ['error' => 'Login incorrecto!'];
        }
        $checkTokenQuery = "SELECT * FROM tokens WHERE username = :username AND expira > NOW() LIMIT 1";
        $checkTokenStmt = $this->db->prepare($checkTokenQuery);
        $checkTokenStmt->bindValue(':username', trim($userData['username']), PDO::PARAM_STR);
        $checkTokenStmt->execute();
        $existingToken = $checkTokenStmt->fetch(PDO::FETCH_ASSOC);
        if ($existingToken) {
            return ['error' => 'Já existe um token ativo para este usuário'];
        }
        $token = generateToken(32);
        $datetime = new DateTime('now', new DateTimeZone('Europe/Lisbon'));
        $datetime->add(new DateInterval('PT1H'));
        $termina = $datetime->format('Y-m-d H:i:s');
        $tokenQuery = "INSERT INTO tokens (username, token, expira) VALUES (:username, :token, :expira)";
        $tokenStmt = $this->db->prepare($tokenQuery);
        $tokenStmt->bindValue(':username', trim($userData['username']), PDO::PARAM_STR);
        $tokenStmt->bindValue(':token', trim($token), PDO::PARAM_STR);
        $tokenStmt->bindParam(':expira', $termina);
        if ($tokenStmt->execute()) {
            return [
                'message' => 'Token gerado com sucesso',
                'token' => $token,
                'nivel' => $userData['nivel']
            ];
        } else {
            return ['error' => 'Erro ao gerar o token'];
        }
    }
    public function deleteToken($user){
        $this->checkToken($user);
        $checkTokenQuery = "SELECT * FROM tokens WHERE username = :username AND expira > NOW() LIMIT 1";
        $checkTokenStmt = $this->db->prepare($checkTokenQuery);
        $checkTokenStmt->bindValue(':username', trim($user), PDO::PARAM_STR);
        $checkTokenStmt->execute();
        $existingToken = $checkTokenStmt->fetch(PDO::FETCH_ASSOC);
        if (!$existingToken) {
            return ['error' => 'Não existe um token ativo para este usuário'];
        }
        $token = $existingToken['token']; 
        $tokenQuery = "DELETE FROM tokens WHERE username = :username";
        $tokenStmt = $this->db->prepare($tokenQuery);
        $tokenStmt->bindValue(':username', trim($user), PDO::PARAM_STR);
        if ($tokenStmt->execute()) {
            return ['message' => 'Token apagado com sucesso', 'token' => $token];
        } else {
            return ['error' => 'Erro ao apagar o token'];
        }
        $this->checkToken($user);
    }
     
    public function checkToken($user) {
        $query = "
            SELECT token, expira 
            FROM tokens 
            WHERE username = :user
            ORDER BY expira DESC 
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user', trim($user), PDO::PARAM_STR);
        $stmt->execute();
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$tokenData) {
            return ['status' => 'Nenhum token encontrado.'];
        }
        $expiresAt = $tokenData['expira'];
        if (strtotime($expiresAt) <= time()) {
            $deleteQuery = "DELETE FROM tokens WHERE token = :token";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->bindValue(':token', trim($tokenData['token']), PDO::PARAM_STR);
            $deleteStmt->execute();
            return ['status' => 'Token expirado e removido.'];
        }
    
        return ['status' => 'Token ainda válido.'];
    }
    
    private function sendJsonResponse($data, $status = 200) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}
?> 