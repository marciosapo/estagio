<?php

require_once '../app/core/Database.php';
require_once '../app/helpers/tokens.php';
require_once '../app/helpers/comentarios.php';

class Post {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance();
    }
    public function verificarTokenUser($token) {
        return verificarToken($token, $this->db);
    }
    public function getAllPosts($de = 'ASC', $pagina = 1, $porPagina = 5) {
        $offset = ($pagina - 1) * $porPagina;
        $ordem = strtoupper($de);
        $query = "
            SELECT 
                posts.id AS post_id,
                posts.title,
                posts.post AS conteudo,
                posts.post_data AS post_data_post,
                autor_post.username AS nome_autor_post
            FROM posts
            LEFT JOIN users AS autor_post ON posts.id_user = autor_post.id
            ORDER BY posts.id $ordem
        ";
        if ($porPagina > 0) {
            $query .= " LIMIT :limite OFFSET :offset";
        }
        $stmt = $this->db->prepare($query);
        if ($porPagina > 0) {
            $stmt->bindValue(':limite', (int)$porPagina, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $posts = [];
        foreach ($rows as $row) {
            $postId = $row['post_id'];
            $comentarios = getComentarios($this->db, $postId);
            $posts[] = [
                'id' => $postId,
                'title' => $row['title'],
                'post' => $row['conteudo'],
                'post_data' => $row['post_data_post'],
                'postado' => $row['nome_autor_post'],
                'comentarios' => $comentarios,
                'nComentarios' => nComentarios($comentarios)
            ];
        }
        return [
            'posts' => $posts,
            'pagina_atual' => $pagina,
            'por_pagina' => $porPagina,
            'total_posts' => $this->getTotalPosts()
        ];
    }

    public function getPost($search, $pagina = 1, $porPagina = 5) {
        $pesquisa = '%' . strtolower($search) . '%';
        $offset = ($pagina - 1) * $porPagina;

        $countQuery = "
            SELECT COUNT(*) AS total
            FROM posts
            WHERE LOWER(title) LIKE ?
        ";
        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute([$pesquisa]);
        $totalPosts = $countStmt->fetchColumn();

        if ($totalPosts == 0) {
            return [
                'posts' => [],
                'pagina_atual' => $pagina,
                'por_pagina' => $porPagina,
                'total_posts' => 0
            ];
        }

        $query = "
            SELECT 
                posts.id AS post_id,
                posts.title,
                posts.post AS conteudo,
                posts.post_data AS post_data_post,
                autor_post.username AS nome_autor_post
            FROM posts
            LEFT JOIN users AS autor_post ON posts.id_user = autor_post.id
            WHERE LOWER(posts.title) LIKE :pesquisa
            ORDER BY posts.id
        ";
        if ($porPagina > 0) {
            $query .= " LIMIT :limite OFFSET :offset";
        }
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':pesquisa', $pesquisa, PDO::PARAM_STR);
        if ($porPagina > 0) {
            $stmt->bindValue(':limite', (int)$porPagina, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        } 
        $stmt->execute();
        $postRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $posts = [];
        $postIds = [];
        foreach ($postRows as $row) {
            $postId = $row['post_id'];
            $postIds[] = $postId;
            $comentarios = getComentarios($this->db, $postId);
            $posts[$postId] = [
                'id' => $postId,
                'title' => $row['title'] ?? 'Sem título',
                'post' => $row['conteudo'] ?? '',
                'post_data' => $row['post_data_post'] ?? date('Y-m-d H:i:s'),
                'postado' => $row['nome_autor_post'] ?? 'Anônimo',
                'comentarios' => $comentarios,
                'nComentarios' => nComentarios($comentarios)
            ];
        }
        return [
            'posts' => array_values($posts),
            'pagina_atual' => $pagina,
            'por_pagina' => $porPagina,
            'total_posts' => (int)$totalPosts
        ];
    }
    public function getTotalPosts() {
        $query = "SELECT COUNT(*) FROM posts";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
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
            WHERE posts.id = :id
            ORDER BY posts.id, comentarios.id
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            return null;
        }
        $comentarios = getComentarios($this->db, $rows[0]['post_id']);
        $post = [
            'id' => $rows[0]['post_id'],
            'title' => $rows[0]['title'],
            'post' => $rows[0]['conteudo'],
            'post_data' => $rows[0]['post_data_post'],
            'postado' => $rows[0]['nome_autor_post'], 
            'comentarios' => $comentarios,
            'nComentarios' => nComentarios($comentarios)
        ];
        return $post;
    }
    public function getRecentPost() {
        $query = "
            SELECT 
                posts.id AS post_id,
                posts.title,
                posts.post AS conteudo,
                posts.post_data AS post_data_post,
                autor_post.username AS nome_autor_post
            FROM posts
            LEFT JOIN users AS autor_post ON posts.id_user = autor_post.id
            ORDER BY posts.post_data DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $postRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$postRow) {
            return [
                'posts' => [],
                'pagina_atual' => 1,
                'por_pagina' => 1,
                'total_posts' => 0
            ];
        }
        $postId = $postRow['post_id'];
        $comentarios = getComentarios($this->db, $postId);
        $post = [
            'id' => $postId,
            'title' => $postRow['title'],
            'post' => $postRow['conteudo'],
            'post_data' => $postRow['post_data_post'],
            'postado' => $postRow['nome_autor_post'],
            'comentarios' => $comentarios,
            'nComentarios' => nComentarios($comentarios)
        ];
        return [
            'posts' => [$post],
            'pagina_atual' => 1,
            'por_pagina' => 1,
            'total_posts' => 1
        ];
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
        $success = $stmt->execute([$title, $post, $id_user]);
        if ($success) {
            return ['mensagem' => 'Post criado com sucesso'];
        } else {
            return ['erro' => 'Erro ao criar o post'];
        }
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
            return ['mensagem' => 'Post editado com sucesso'];
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
        return ['mensagem' => 'Post e comentários apagados com sucesso'];
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
        return ['mensagem' => $mensagem];
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
            return ['mensagem' => 'Comentário atualizado com sucesso'];
        }else{
            return ['erro' => 'Falha a atualizar o comentário'];
        }    
    }
    
}

?>