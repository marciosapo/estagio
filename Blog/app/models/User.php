<?php 

require_once '../app/core/Database.php';

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
                users.pass,
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
                'token' => $row['user_token'],
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

        $query = "INSERT INTO users(username, nome, email, pass) VALUES(:user, :nome, :email, :pass)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user', $username);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':pass', $pass);
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
                pass = :pass
            WHERE id = :id
        ";
    
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':pass', $data['pass']);
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
        $query = "SELECT id, username, pass FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $user);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userData || $pass !== $userData['pass']) {
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
            return ['message' => 'Token gerado com sucesso', 'token' => $token];
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
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=[]{}|;:,.<>?';
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
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['id_user'];
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