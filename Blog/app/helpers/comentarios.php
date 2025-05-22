<?php

function nComentarios($comentarios) {
    $total = 0;
    foreach ($comentarios as $comentario) {
        $total++;
        if (!empty($comentario['respostas'])) {
            $total += nComentarios($comentario['respostas']);
        }
    }
    return $total;
}

function getComentarios($db, $postId) {
    $stmt = $db->prepare("
        SELECT 
            c.id AS comentario_id,
            c.comentario,
            c.id_user,
            c.post_data,
            c.id_parent,
            u.username AS autor
        FROM comentarios c
        LEFT JOIN users u ON c.id_user = u.id
        WHERE c.id_post = :postId
        ORDER BY c.id
    ");
    $stmt->bindValue(':postId', $postId, PDO::PARAM_INT);
    $stmt->execute();
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $respostas = [];
    $resultado = [];
    foreach ($comentarios as $comentario) {
        $comentarioId = $comentario['comentario_id'];
        $comentario['id'] = $comentarioId;
        $comentario['respostas'] = [];
        $respostas[$comentarioId] = $comentario;
    }
    foreach ($respostas as $id => &$comentario) {
        if (!empty($comentario['id_parent']) && isset($respostas[$comentario['id_parent']])) {
            $respostas[$comentario['id_parent']]['respostas'][] = &$comentario;
        } else {
            $resultado[] = &$comentario;
        }
    }
    return $resultado;
}

?>