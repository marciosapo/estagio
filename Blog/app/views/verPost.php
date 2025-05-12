<?php

$formatter = new IntlDateFormatter(
    'pt_PT',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    'Europe/Lisbon',
    IntlDateFormatter::GREGORIAN
);

include 'blocks/editForm.php';
include 'blocks/editarComentario.php';
include 'blocks/responderForm.php';
include 'blocks/novoComentario.php';
include 'blocks/editarPost.php';

if(isset($_POST['cancelar'])){
    unset($_POST['responder']);
    unset($_POST['editar']);
    unset($_POST['responderid']);
    unset($_POST['respondeid']);
    unset($_POST['editarid']);
} 
if(isset($_POST['apagarPost'])){
    if($_SESSION['nivel'] != "Owner" && $_SESSION['nivel'] != "Admin"){
        header("Location: /Blog/");
        exit;
    }else{
        $token = $_SESSION['token'];
        $id_user = $this->userModel->verificarTokenUser($token);
        if (!$id_user) {
            header("Location: /Blog/");
            exit;
        }
        $id = $_POST['id']; 
        $resultado = $this->postModel->apagarPost($id, $token);
        if ($resultado) {
            $_SESSION['flash_sucesso'] = 'Post apagado com sucesso!';
        } else {
            $_SESSION['flash_erro'] = 'Erro ao apagar o post';
        }

        header("Location: /Blog/");
        exit;
    }  
}

if(isset($_POST['apagarEdicao'])){
    header("Location: /Blog/");
    exit;
}

if(isset($_POST['salvarEdicao'])){
    if($_SESSION['nivel'] != "Owner" && $_SESSION['nivel'] != "Admin"){
        header("Location: /Blog/");
        exit;
    }else{
        $token = $_SESSION['token'];
        $id_user = $this->userModel->verificarTokenUser($token);
        if (!$id_user) {
            header("Location: /Blog/");
            exit;
        }
        $id = $_POST['id'];
        $novoTitulo = trim($_POST['tituloEditado']);
        $novoConteudo = trim($_POST['conteudoEditado']);  
        $resultado = $this->postModel->editarPost($id, $novoTitulo, $novoConteudo, $token);
        if ($resultado) {
            $_SESSION['flash_sucesso'] = 'Post editado com sucesso!';
        } else {
            $_SESSION['flash_erro'] = 'Erro ao editar o post';
        }
        refresh_pagina($id);
        exit;
    }  
}
if (isset($_POST['novoComentario'])) {
    if (!isset($_SESSION['user'], $_SESSION['token'])) {
        header("Location: /Blog/");
        exit;
    }
    $token = $_SESSION['token'];
    $id_user = $this->userModel->verificarTokenUser($token);
    if (!$id_user) {
        $erro = 'Token inválido ou expirado';
    } else {
        $conteudo = trim($_POST['comment']);
        $id = $_POST['id'];
        $id_parent = null;
        if(isset($_POST['id_parent'])) { 
            $id_parent = $_POST['id_parent'];
        } 
        if (empty($conteudo)) {
            $erro = 'Título e conteúdo não podem estar vazios';
        } else {
            $resultado = $this->postModel->criarComentario($conteudo, $id, $token, $id_parent, $id_parent = null);
            if ($resultado) {
                $_SESSION['flash_sucesso'] = 'Comentário criado com sucesso!';
            } else {
                $_SESSION['flash_erro'] = 'Erro ao criar comentário';
            }
            refresh_pagina($id);
            exit;
        }
    }
} 

if(isset($_POST['apagarComentario'])){
    $token = $_SESSION['token'];
    $id_user = $this->userModel->verificarTokenUser($token);
    if (!$id_user) {
        header("Location: /Blog/");
        exit;
    }
    $id = $_POST['editarid'];
    $id_retorno = $_POST['id']; 
    $resultado = $this->postModel->apagarComentario($id, $token);
    if ($resultado) {
        $_SESSION['flash_sucesso'] = 'Comentário apagado com sucesso!';
    } else {
        $_SESSION['flash_erro'] = 'Erro ao apagar o comentário';
    }
    refresh_pagina($id_retorno);
    exit;
}
if(isset($_POST['editarComentario'])){
    $token = $_SESSION['token'];
    $id_user = $this->userModel->verificarTokenUser($token);
    if (!$id_user) {
        header("Location: /Blog/");
        exit;
    }
    $id = $_POST['editarid'];
    $id_retorno = $_POST['id'];
    $conteudo = $_POST['comment'];
    $resultado = $this->postModel->atualizarComentario($id, $conteudo, $token);
    if ($resultado) {
        $_SESSION['flash_sucesso'] = 'Comentário editado com sucesso!';
    } else {
        $_SESSION['flash_erro'] = 'Erro ao editar o comentário';
    }
    refresh_pagina($id_retorno);
    exit;
}  
function verRespostas($respostas, $post, $nivel = 1) {
    if (empty($respostas)) return;
    echo '<div class="ms-' . min($nivel * 2, 5) . ' border-start ps-3 mt-3">';
    foreach ($respostas as $resposta):
?>
<div class="mb-3">
    <div class="bg-white border rounded p-3 shadow-sm">
            <strong class="text-primary"><?php echo htmlspecialchars($resposta['autor']); ?></strong>
            <small class="text-muted"> • <?php echo tempoDecorrido($resposta['post_data']); ?></small>
            <p class="text-break mt-2 mb-2"><?php echo nl2br(htmlspecialchars($resposta['comentario'])); ?></p>
            <?php if ($_SESSION['user'] == $resposta['autor']): ?>
                <?php editForm($post['id'], $resposta['id']); ?>
            <?php endif; ?>
            <div class="d-flex gap-2">
                <?php if ($_SESSION['user'] != $resposta['autor'] && !isset($_POST['responderid']) && !isset($_POST['respondeid'])): ?>
                    <?php responderForm($post['id'], $resposta['id']); ?>
                <?php endif; ?>
            </div>
        
            <?php if (isset($_POST['respondeid']) && $_POST['respondeid'] == $resposta['id']): ?>
                <?php novoComentarioForm($post['id'], $resposta['id']); ?>
            <?php endif; ?>

            <?php if (isset($_POST['editarid']) && $_POST['editarid'] == $resposta['id']): ?>
                <?php editarComentarioForm($post['id'], $resposta['id'], $resposta['comentario']); ?>
            <?php endif; ?>

            <?php if (!empty($resposta['respostas'])) {
                verRespostas($resposta['respostas'], $post, $nivel + 1);
            } ?>
        </div>
    </div>
<?php
    endforeach;
    echo '</div>';
}
?>
<div class="container mt-5">
    <?php if (isset($post) && !empty($post)): ?>
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <?php require_once __DIR__ . '/blocks/flash.php'; ?>
                <div class="card shadow-sm border-0">
                <div class="card-body d-flex gap-3 align-items-stretch">
                <div class="w-25">
                    <img src="https://www.blogtyrant.com/wp-content/uploads/2020/02/how-long-should-a-blog-post-be.png" class="img-fluid object-fit-cover rounded" style="height: 250px;" alt="Imagem do post">
                </div>
                <div class="flex-grow-1 d-flex flex-column justify-content-between">
                    <h3 class="text-primary fw-semibold"><?php echo htmlspecialchars($post['title']); ?></h3>
                    <p class="mt-4 mb-4 text-break"><?php echo nl2br(htmlspecialchars($post['post'])); ?></p>
                    <p class="text-muted text-end">
                        Postado por <strong><?php echo htmlspecialchars($post['postado']); ?></strong> em 
                        <time datetime="<?php echo $post['post_data']; ?>"><?php echo $formatter->format(new DateTime($post['post_data'])); ?></time>
                    </p>
                        <?php if (isset($_SESSION['user']) && $_SESSION['user'] == $post['postado'] && isset($_POST['editarPost'])): ?>
                            <?php editarPostForm($post['id'], $post['title'], $post['post']) ?>
                        <?php endif; ?>
                        <?php include 'comentarios/lista.php'; ?>
                        <?php if (isset($_SESSION['user']) && $_SESSION['user'] != $post['postado'] && !isset($_POST['respondeid'])): ?>
                            <hr>
                            <?php novoComentarioBaseForm($post['id']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="container py-5">
            <p class="text-center">Nenhum post encontrado.</p>
        </div>
    <?php endif; ?>
</div>