<?php 

require_once '../app/core/Database.php';

class Users {
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
    
    public function getToken($user, $pass){
        $this->checkToken($user);
        $query = "SELECT id, username, pass FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $user);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userData || $pass !== $userData['pass']) {
            return ['error' => 'Credenciais inválidas'];
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
}
?> 