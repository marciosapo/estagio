<?php 

require_once '../app/core/Database.php';
require_once '../app/config/config.php';

class User {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance();
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
        $stmt->bindParam(':user', $username);
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
        $checkStmt->bindParam(':user', $username);
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();
        $exists = $checkStmt->fetchColumn();

        if ($exists > 0) {
            return ['erro' => 'Nome de utilizador ou email já existe'];
        }
        $hash_password = password_hash($pass, PASSWORD_DEFAULT);
        $query = "INSERT INTO users(username, nome, email, pass, nivel) VALUES(:user, :nome, :email, :pass, :nivel)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user', $username);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':pass', $hash_password);
        $stmt->bindParam(':nivel', "User");
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
    
    public function updateUser($data) {
        $id_user = $this->verificarToken($data['token']);
        if (!$id_user) {
            return ['erro' => 'Token inválido ou expirado'];
        }
        $checkQuery = "SELECT COUNT(*) FROM users WHERE email = :email AND id != :id";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(':email', $data['email']);
        $checkStmt->bindParam(':id', $id_user);
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
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':pass', $hash_password);
        if (isset($data['imagem']) && $data['imagem'] !== null) {
            $stmt->bindParam(':imagem', $data['imagem'], PDO::PARAM_LOB);
        } else {
            $stmt->bindValue(':imagem', null, PDO::PARAM_NULL);
        }
        $stmt->bindParam(':id', $id_user);
    
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
        $id_user = $this->verificarToken($data['token']);
        if (!$id_user) {
            return ['erro' => 'Token inválido ou expirado'];
        }
        $checkQuery = "SELECT nivel FROM users WHERE id = :id_user";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(':id_user', $id_user);
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
        $checkTokenStmt->bindParam(':username', $user);
        $checkTokenStmt->execute();
        $existingToken = $checkTokenStmt->fetch(PDO::FETCH_ASSOC);
        if ($existingToken) {
            $datetime = new DateTime('now', new DateTimeZone('Europe/Lisbon'));
            $datetime->add(new DateInterval('PT1H'));
            $termina = $datetime->format('Y-m-d H:i:s');
            $tokenQuery = "UPDATE tokens set expira = :expira WHERE username = :username";
            $tokenStmt = $this->db->prepare($tokenQuery);
            $tokenStmt->bindParam(':username', $user);
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
        $stmt->bindParam(':username', $user);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userData || !password_verify($pass, $userData['pass'])) {
            return ['error' => 'Login incorrecto!'];
        }
        $checkTokenQuery = "SELECT * FROM tokens WHERE username = :username AND expira > NOW() LIMIT 1";
        $checkTokenStmt = $this->db->prepare($checkTokenQuery);
        $checkTokenStmt->bindParam(':username', $userData['username']);
        $checkTokenStmt->execute();
        $existingToken = $checkTokenStmt->fetch(PDO::FETCH_ASSOC);
        if ($existingToken) {
            return ['error' => 'Já existe um token ativo para este usuário'];
        }
        $token = $this->generateToken(32);
        $datetime = new DateTime('now', new DateTimeZone('Europe/Lisbon'));
        $datetime->add(new DateInterval('PT1H'));
        $termina = $datetime->format('Y-m-d H:i:s');
        $tokenQuery = "INSERT INTO tokens (username, token, expira) VALUES (:username, :token, :expira)";
        $tokenStmt = $this->db->prepare($tokenQuery);
        $tokenStmt->bindParam(':username', $userData['username']);
        $tokenStmt->bindParam(':token', $token);
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
        $checkTokenStmt->bindParam(':username', $user);
        $checkTokenStmt->execute();
        $existingToken = $checkTokenStmt->fetch(PDO::FETCH_ASSOC);
        if (!$existingToken) {
            return ['error' => 'Não existe um token ativo para este usuário'];
        }
        $token = $existingToken['token']; 
        $tokenQuery = "DELETE FROM tokens WHERE username = :username";
        $tokenStmt = $this->db->prepare($tokenQuery);
        $tokenStmt->bindParam(':username', $user);
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
        $stmt->bindParam(':user', $user);
        $stmt->execute();
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$tokenData) {
            return ['status' => 'Nenhum token encontrado.'];
        }
    
        $expiresAt = $tokenData['expira'];
        if (strtotime($expiresAt) <= time()) {
            $deleteQuery = "DELETE FROM tokens WHERE token = :token";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->bindParam(':token', $tokenData['token']);
            $deleteStmt->execute();
            return ['status' => 'Token expirado e removido.'];
        }
    
        return ['status' => 'Token ainda válido.'];
    }
    function generateToken($length = 32) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_-=[]{}|;:.?';
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $randomIndex = array_rand(str_split($characters));
            $token .= $characters[$randomIndex];
        }
        return $token;
    }


    public function verificarToken($token) {
        $query = "
            SELECT users.id AS id_user
            FROM tokens
            JOIN users ON tokens.username = users.username
            WHERE tokens.token = :token AND tokens.expira > NOW()
            LIMIT 1
        ";
        $token = trim($token);
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['id_user'];
        }else {
            echo 'Token inválido ou expirado. <br>';
        }
        return false;
    }
    private function sendJsonResponse($data, $status = 200) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}
?> 