<?php 

function verificarToken($token, $db) {
    $token = trim($token);
    if (empty($token)) {
        return false;
    }
    $query = "
        SELECT users.id AS id_user
        FROM tokens
        JOIN users ON tokens.username = users.username
        WHERE tokens.token = CONVERT(:token USING utf8mb4)
        LIMIT 1
    ";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':token', trim($token), PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id_user'];
    }
    return false;
}

function generateToken($length = 32, $db = null) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_-=[]{}|;:.?';
    do {
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $randomIndex = random_int(0, strlen($characters) - 1);
            $token .= $characters[$randomIndex];
        }
        if ($db) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM tokens WHERE token = :token");
            $stmt->bindValue(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            $exists = $stmt->fetchColumn();
        }else {
            $exists = false;
        }
    } while ($exists);
    return $token;
}

function getTokenUser($user){
        $query = "
            SELECT users.id AS user_id
            FROM users
            WHERE username = :username
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':username', trim($user), PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['user_id'];
        }
        return false;
    } 

?>