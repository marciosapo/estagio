<?php

require_once '../app/core/Database.php';
require_once '../app/helpers/tokens.php';

class Post {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance();
    }
    public function verificarTokenUser($token) {
        return verificarToken($token, $this->db);
    } 
    public function getAllPosts($de = 'ASC') {
        $ordem = strtoupper($de);
        $query = "
            SELECT 
                posts.id AS post_id,
                posts.title,
                posts.post AS conteudo,
                posts.post_data AS post_data_post,
                autor_post.username AS nome_autor_post,
                comentarios.id AS comentario_id,
                comentarios.comentario,
                comentarios.post_data AS post_data_comentario,
                comentarios.id_parent,
                autor_coment.username AS nome_autor_comentario

            FROM posts
            LEFT JOIN users AS autor_post ON posts.id_user = autor_post.id
            LEFT JOIN comentarios ON comentarios.id_post = posts.id
            LEFT JOIN users AS autor_coment ON comentarios.id_user = autor_coment.id
            ORDER BY posts.id $ordem, comentarios.id
        ";
        $stmt = $this->db->query($query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $posts = [];
        foreach ($rows as $row) {
            $postId = $row['post_id'];
            if (!isset($posts[$postId])) {
                $posts[$postId] = [
                    'id' => $postId,
                    'title' => $row['title'],
                    'post' => $row['conteudo'],
                    'post_data' => $row['post_data_post'],
                    'postado' => $row['nome_autor_post'], 
                    'comentarios_map' => [],
                    'comentarios' =>[],
                    'nComentarios' => 0
                ];
            }
            if (!empty($row['comentario_id'])) {
                $posts[$postId]['nComentarios'] += 1;
                $comentarioId = $row['comentario_id'];
                $comentario = [
                    'id' => $comentarioId,
                    'comentario' => $row['comentario'],
                    'autor' => $row['nome_autor_comentario'],
                    'post_data' => $row['post_data_comentario']
                ];
                if (!is_null($row['id_parent'])) {
                    $comentario['id_parent'] = $row['id_parent'];
                }
                $comentario['respostas'] = [];
                $posts[$postId]['comentarios_map'][$comentarioId] = $comentario;  
            }
        }
        foreach ($posts as &$post) {
            foreach ($post['comentarios_map'] as $comentarioId => &$comentario) {
                if (isset($comentario['id_parent']) && isset($post['comentarios_map'][$comentario['id_parent']])) {
                    $parentId = $comentario['id_parent'];
                    if (isset($post['comentarios_map'][$parentId])){
                        $post['comentarios_map'][$parentId]['respostas'][] = &$comentario;
                    } 
                } else {
                    $post['comentarios'][] = &$comentario;
                }
            }
            unset($post['comentarios_map']);
        }
        foreach ($posts as &$post) {
            unset($post['comentarios_map']);
        } 
        return array_values($posts);
    }
    public function getPostById($id) {
        $query = "
            SELECT 
                posts.id AS post_id,
                posts.title,
                posts.post AS conteudo,
                posts.post_data AS post_data_post,
                autor_post.username AS nome_autor_post,
                comentarios.id AS comentario_id,
                comentarios.comentario,
                comentarios.post_data AS post_data_comentario,
                comentarios.id_parent,
                autor_coment.username AS nome_autor_comentario

            FROM posts
            LEFT JOIN users AS autor_post ON posts.id_user = autor_post.id
            LEFT JOIN comentarios ON comentarios.id_post = posts.id
            LEFT JOIN users AS autor_coment ON comentarios.id_user = autor_coment.id
            WHERE posts.id = ?
            ORDER BY posts.id, comentarios.id
        ";
    
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            return null;
        }
        $post = [
            'id' => $rows[0]['post_id'],
            'title' => $rows[0]['title'],
            'post' => $rows[0]['conteudo'],
            'post_data' => $rows[0]['post_data_post'],
            'postado' => $rows[0]['nome_autor_post'], 
            'comentarios_map' => [],
            'comentarios' => [],
        ];
        foreach ($rows as $row) {
            if (!empty($row['comentario_id'])) {
                $comentarioId = $row['comentario_id'];
                $comentario = [ 
                    'id' => $comentarioId,
                    'comentario' => $row['comentario'],
                    'autor' => $row['nome_autor_comentario'],
                    'post_data' => $row['post_data_comentario']
                ];
                if (!is_null($row['id_parent'])) {
                    $comentario['id_parent'] = $row['id_parent'];
                }
                $comentario['respostas'] = [];
                $post['comentarios_map'][$comentarioId] = $comentario;  
            }
        }
        foreach ($post['comentarios_map'] as $comentarioId => &$comentario){
            if (isset($comentario['id_parent']) && isset($post['comentarios_map'][$comentario['id_parent']])) {
                $parentId = $comentario['id_parent'];
                if (isset($post['comentarios_map'][$parentId])){
                    $post['comentarios_map'][$parentId]['respostas'][] = &$comentario;    
                } 
            } else{
                $post['comentarios'][] = &$comentario; 
            } 
        } 
        unset($post['comentarios_map']);
        return $post;
    }


    public function getPost($search) {
        $pesquisa = '%' . strtolower($search) . '%';
        $query = "
            SELECT 
                posts.id AS post_id,
                posts.title,
                posts.post AS conteudo,
                posts.post_data AS post_data_post,
                autor_post.username AS nome_autor_post,
                comentarios.id AS comentario_id,
                comentarios.comentario,
                comentarios.post_data AS post_data_comentario,
                comentarios.id_parent,
                autor_coment.username AS nome_autor_comentario
            FROM posts
            LEFT JOIN users AS autor_post ON posts.id_user = autor_post.id
            LEFT JOIN comentarios ON comentarios.id_post = posts.id
            LEFT JOIN users AS autor_coment ON comentarios.id_user = autor_coment.id
            WHERE posts.title like ?
            ORDER BY posts.id, comentarios.id
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$pesquisa]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            return null;
        }
        $posts = [];
        foreach ($rows as $row) {
            $postId = $row['post_id'];
            if (!isset($posts[$postId])) {
                $posts[$postId] = [
                    'id' => $postId,
                    'title' => $row['title'],
                    'post' => $row['conteudo'],
                    'post_data' => $row['post_data_post'],
                    'postado' => $row['nome_autor_post'], 
                    'comentarios_map' => [],
                    'comentarios' =>[], 
                    'nComentarios' => 0
                ];
            }
            if (!empty($row['comentario_id'])) {
                $posts[$postId]['nComentarios'] += 1;
                $comentarioId = $row['comentario_id'];
                $comentario = [
                    'id' => $comentarioId,
                    'comentario' => $row['comentario'],
                    'autor' => $row['nome_autor_comentario'],
                    'post_data' => $row['post_data_comentario']
                ];
                if (!is_null($row['id_parent'])) {
                    $comentario['id_parent'] = $row['id_parent'];
                }
                $comentario['respostas'] = [];
                $posts[$postId]['comentarios_map'][$comentarioId] = $comentario;  
            }
        }
        foreach ($posts as &$post) {
            foreach ($post['comentarios_map'] as $comentarioId => &$comentario) {
                if (isset($comentario['id_parent']) && isset($post['comentarios_map'][$comentario['id_parent']])) {
                    $parentId = $comentario['id_parent'];
                    if (isset($post['comentarios_map'][$parentId])){
                        $post['comentarios_map'][$parentId]['respostas'][] = &$comentario;
                    } 
                } else {
                    $post['comentarios'][] = &$comentario;
                }
            }
            unset($post['comentarios_map']);
        }
        foreach ($posts as &$post) {
            unset($post['comentarios_map']);
        } 
        return array_values($posts);
    }

    public function getRecentPost() {
        $query = "
            SELECT 
                posts.id AS post_id,
                posts.title,
                posts.post AS conteudo,
                posts.post_data AS post_data_post,
                autor_post.username AS nome_autor_post,
                comentarios.id AS comentario_id,
                comentarios.comentario,
                comentarios.post_data AS post_data_comentario,
                comentarios.id_parent,
                autor_coment.username AS nome_autor_comentario
            FROM posts
            LEFT JOIN users AS autor_post ON posts.id_user = autor_post.id
            LEFT JOIN comentarios ON comentarios.id_post = posts.id
            LEFT JOIN users AS autor_coment ON comentarios.id_user = autor_coment.id
            ORDER BY posts.post_data DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            return null;
        }
        $posts = [];
        foreach ($rows as $row) {
            $postId = $row['post_id'];
            if (!isset($posts[$postId])) {
                $posts[$postId] = [
                    'id' => $postId,
                    'title' => $row['title'],
                    'post' => $row['conteudo'],
                    'post_data' => $row['post_data_post'],
                    'postado' => $row['nome_autor_post'], 
                    'comentarios_map' => [],
                    'comentarios' =>[], 
                    'nComentarios' => 0
                ];
            }
            if (!empty($row['comentario_id'])) {
                $posts[$postId]['nComentarios'] += 1;
                $comentarioId = $row['comentario_id'];
                $comentario = [
                    'id' => $comentarioId,
                    'comentario' => $row['comentario'],
                    'autor' => $row['nome_autor_comentario'],
                    'post_data' => $row['post_data_comentario']
                ];
                if (!is_null($row['id_parent'])) {
                    $comentario['id_parent'] = $row['id_parent'];
                }
                $comentario['respostas'] = [];
                $posts[$postId]['comentarios_map'][$comentarioId] = $comentario;  
            }
        }
        foreach ($posts as &$post) {
            foreach ($post['comentarios_map'] as $comentarioId => &$comentario) {
                if (isset($comentario['id_parent']) && isset($post['comentarios_map'][$comentario['id_parent']])) {
                    $parentId = $comentario['id_parent'];
                    if (isset($post['comentarios_map'][$parentId])){
                        $post['comentarios_map'][$parentId]['respostas'][] = &$comentario;
                    } 
                } else {
                    $post['comentarios'][] = &$comentario;
                }
            }
            unset($post['comentarios_map']);
        }
        foreach ($posts as &$post) {
            unset($post['comentarios_map']);
        } 
        return array_values($posts);
    }


    public function criarPost($title, $post, $token) {
        $id_user = $this->verificarTokenUser($token);
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
        $stmt = $this->db->prepare("INSERT INTO posts (title, post, id_user, post_data) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$title, $post, $id_user]);
    
        return $this->db->lastInsertId();
    }

    public function editarPost($id_post, $title, $post_content, $token) {
        $id_user = $this->verificarTokenUser($token);
        if (!$id_user) {
            return ['erro' => 'Token inválido ou expirado'];
        }
        $checkQuery = "SELECT nivel FROM users WHERE id = :id_user";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(':id_user', $id_user);
        $checkStmt->execute();
        $nivel = $checkStmt->fetchColumn();
        if ($nivel !== 'Owner' && $nivel !== 'Admin') {
            return ['erro' => 'Apenas utilizadores com nível Owner ou Admin podem editar posts.'];
        }
        $stmt = $this->db->prepare("UPDATE posts SET title = ?, post = ? WHERE id = ?");
        $success = $stmt->execute([$title, $post_content, $id_post]);
        if ($success) {
            return ['sucesso' => 'Post editado com sucesso'];
        } else {
            return ['erro' => 'Erro ao editar o post'];
        }
    }

    public function apagarPost($id_post, $token) {
        $id_user = $this->verificarTokenUser($token);
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
        $verifica = $this->db->prepare("SELECT id, id_user FROM posts WHERE id = :id_post");
        $verifica->bindValue(':id_post', $id_post, PDO::PARAM_INT);
        $verifica->execute();
        if ($verifica->rowCount() == 0) {
            return ['erro' => 'Post não encontrado'];
        }
        $post = $verifica->fetch(PDO::FETCH_ASSOC);
        $apagarComentarios = $this->db->prepare("DELETE FROM comentarios WHERE id_parent = :id_post");
        $apagarComentarios->bindParam(':id_post', $id_post, PDO::PARAM_INT);
        $apagarComentarios->execute();
        $apagarPost = $this->db->prepare("DELETE FROM posts WHERE id = :id_post");
        $apagarPost->bindValue(':id_post', $id_post, PDO::PARAM_INT);
        $apagarPost->execute();
        return ['sucesso' => 'Post e comentários apagados com sucesso'];
    }

    public function criarComentario($comentario, $id_post, $token, $id_parent = null) {
        $id_user = $this->verificarTokenUser($token);
        if (!$id_user) {
            return ['erro' => 'Token inválido ou expirado'];
        }
        $verifica = $this->db->prepare("SELECT id FROM posts WHERE id = :id_post");
        $verifica->bindValue(':id_post', $id_post, PDO::PARAM_INT);
        $verifica->execute();
        if ($verifica->rowCount() == 0) {
            return 'POST_NOT_FOUND';
        }
        $query = "INSERT INTO comentarios (comentario, id_user, id_post, id_parent, post_data)
                 VALUES(:comentario, :id_user, :id_post, :id_parent, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':comentario', trim($comentario), PDO::PARAM_STR);
        $stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->bindValue(':id_post', $id_post, PDO::PARAM_INT);
        if($id_parent !== null){
            $stmt->bindValue(':id_parent', $id_parent, PDO::PARAM_INT);
        } else{
            $null = null;
            $stmt->bindValue(':id_parent', $null, PDO::PARAM_NULL);
        } 
        return $stmt->execute() ? $this->db->lastInsertId() : false;
    }

    public function apagarComentario($id_comentario, $token) {
        $id_user = $this->verificarTokenUser($token);
        if (!$id_user) {
            return ['erro' => 'Token inválido ou expirado'];
        }
        $verifica = $this->db->prepare("SELECT id, id_user, id_parent FROM comentarios WHERE id = :id");
        $verifica->bindValue(':id', $id_comentario, PDO::PARAM_INT);
        $verifica->execute();
        if ($verifica->rowCount() == 0) {
            return ['erro' => 'Comentário não encontrado ' . $id_comentario];
        }
        $comentario = $verifica->fetch(PDO::FETCH_ASSOC);
        if (!empty($comentario['id_parent'])) {
            $id_parent = $comentario['id_parent'];
            if ($id_parent) {
                $verificaPai = $this->db->prepare("SELECT id_user FROM comentarios WHERE id = :id_parent");
                $verificaPai->bindValue(':id_parent', $id_parent, PDO::PARAM_INT);
                $verificaPai->execute();
                if ($verificaPai->rowCount() == 0) {
                    return ['erro' => 'Comentário pai não encontrado'];
                }
            } else {
                return ['erro' => 'Comentário pai não foi especificado.'];
            }
        } else {
            if ($comentario['id_user'] != $id_user) {
                return ['erro' => 'Acesso negado. Não és o dono do comentário.'];
            }
        }
        if (empty($comentario['id_parent'])) {
            $apagarFilhos = $this->db->prepare("DELETE FROM comentarios WHERE id_parent = :id_comentario");
            $apagarFilhos->bindValue(':id_comentario', $id_comentario, PDO::PARAM_INT);
            $apagarFilhos->execute();
        }
        $apagarComentario = $this->db->prepare("DELETE FROM comentarios WHERE id = :id_comentario");
        $apagarComentario->bindValue(':id_comentario', $id_comentario, PDO::PARAM_INT);
        $apagarComentario->execute();
        $mensagem = empty($comentario['id_parent']) ? 'Comentário e respostas apagados com sucesso' : 'Resposta apagada com sucesso';
        return ['sucesso' => $mensagem];
    }

    public function atualizarComentario($id_comentario, $comentario, $token) {
        $id_user = $this->verificarTokenUser($token);
        if (!$id_user) {
            return ['erro' => 'Token inválido ou expirado'];
        }
        $verifica = $this->db->prepare("SELECT id_user FROM comentarios WHERE id = :id_comentario");
        $verifica->bindValue(':id_comentario', $id_comentario, PDO::PARAM_INT);
        $verifica->execute();
        if ($verifica->rowCount() == 0) {
            return ['erro' => 'Comentário não encontrado'];
        }
        $comentario_data = $verifica->fetch(PDO::FETCH_ASSOC);
        if ($comentario_data['id_user'] !== $id_user) {
            return ['erro' => 'Não és o dono do comentário'];
        }
        $query = "UPDATE comentarios SET comentario = :comentario, post_data = NOW() WHERE id = :id_comentario";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':comentario', trim($comentario), PDO::PARAM_STR);
        $stmt->bindValue(':id_comentario', $id_comentario, PDO::PARAM_INT);
        if($stmt->execute()){
            return ['sucesso' => 'Comentário atualizado com sucesso'];
        }else{
            return ['erro' => 'Falha a atualizar o comentário'];
        }    
    }

    public function getTokenUser($user){
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
}

?>