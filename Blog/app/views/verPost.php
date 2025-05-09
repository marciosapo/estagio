<?php

$formatter = new IntlDateFormatter(
    'pt_PT',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    'Europe/Lisbon',
    IntlDateFormatter::GREGORIAN
);

if(isset($_POST['apagarPost'])){
    if($_SESSION['nivel'] != "Owner" && $_SESSION['nivel'] != "Admin"){
        header("Location: /Blog/");
        exit;
    }else{
        $token = $_SESSION['token'];
        $id_user = $this->userModel->verificarToken($token);
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
            <div class="d-flex gap-2">
                <?php if ($_SESSION['user'] != $resposta['autor'] && !isset($_POST['responderid']) && !isset($_POST['respondeid'])): ?>
                    <form method="POST" action="/Blog/verPost"  class="mt-3 mb-3 w-100 h-auto">
                        <input type="hidden" name="id" value="<?= $post['id'] ?>">
                        <input type="hidden" name="respondeid" value="<?= $resposta['id'] ?>">
                        <input type="hidden" name="titulo" value="<?= $post['title'] ?>">
                        <button type="submit" name="responder" class="btn btn-sm btn-outline-secondary">Responder</button>
                    </form>
                <?php endif; ?>
            </div>
        
            <?php if (isset($_POST['respondeid']) && $_POST['respondeid'] == $resposta['id']): ?>
                <form method="POST" action="/Blog/verPost" class="mt-3 mb-3 w-100">
                    <input type="hidden" name="id" value="<?= $post['id'] ?>">
                    <input type="hidden" name="id_parent" value="<?= $resposta['id'] ?>">
                    <input type="hidden" name="titulo" value="<?= $post['title'] ?>">
                    <textarea name="comment" class="form-control mb-2 w-100" placeholder="Escreva sua resposta..."></textarea>
                    <div class="d-flex gap-2">
                        <button type="submit" name="cancelar" class="btn btn-outline-secondary btn-sm">Cancelar</button>
                        <button type="submit" name="novoComentario" class="btn btn-outline-secondary btn-sm">Enviar</button>
                    </div>
                </form>
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
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                    <h3 class="text-primary fw-semibold"><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p class="mt-4 mb-4 text-break"><?php echo nl2br(htmlspecialchars($post['post'])); ?></p>
                        <p class="text-muted text-end">Postado por <strong><?php echo htmlspecialchars($post['postado']); ?></strong> em 
                            <time datetime="<?php echo $post['post_data']; ?>"><?php echo $formatter->format(new DateTime($post['post_data'])); ?></time>
                        </p>
                        <hr>
                        <?php if (!empty($post['comentarios'])): ?>
                            <h5 class="mb-4">Comentários</h5>
                            
                                <?php foreach ($post['comentarios'] as $comentario): ?>
                                    <div class="bg-light rounded p-3 mb-3 shadow-sm">
                                        <strong class="text-primary"><?php echo htmlspecialchars($comentario['autor']); ?></strong>
                                        <small class="text-muted"> • <?php echo tempoDecorrido($comentario['post_data']); ?></small>
                                        <p class="mt-2 text-break"><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>

                                        <?php if (isset($_SESSION['user'])): ?>
                                            <?php if ($_SESSION['user'] == $comentario['autor'] && !isset($_POST['editar'])): ?>
                                                <form action="/Blog/verPost" method="POST" class="mt-3 mb-3 w-100">
                                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                                    <input type="hidden" name="editarid" value="<?php echo $comentario['id']; ?>">
                                                    <input type="hidden" name="titulo" value="<?php echo $post['title']; ?>">
                                                    <div class="d-flex gap-2">
                                                        <input type="submit" name="editar" value="Editar" class="btn btn-sm btn-outline-secondary">
                                                        <input type="submit" name="apagar" value="Apagar" class="btn btn-sm btn-outline-secondary">
                                                    </div>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($_SESSION['user'] == $comentario['autor'] && isset($_POST['editarid']) && $_POST['editarid'] == $comentario['id']): ?>
                                                <form action="/Blog/verPost" method="POST" class="mt-3 mb-3 w-100">
                                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                                    <input type="hidden" name="titulo" value="<?php echo $post['title']; ?>">
                                                    <textarea name="comment" class="form-control mb-2 w-100"><?php echo htmlspecialchars($comentario['comentario']); ?></textarea>
                                                    <div class="d-flex gap-2">
                                                        <input type="submit" name="cancelar" value="Cancelar" class="btn btn-sm btn-outline-secondary">
                                                        <input type="submit" name="editar" value="Gravar" class="btn btn-sm btn-outline-secondary">
                                                    </div>
                                                </form>
                                            <?php endif; ?>
                                            <?php if (isset($_POST['responder']) && $_POST['respondeid'] == $comentario['id']): ?>
                                                <form action="/Blog/verPost" method="POST" class="mt-3 mb-3 w-100">
                                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                                    <input type="hidden" name="id_parent" value="<?php echo $comentario['id']; ?>">
                                                    <input type="hidden" name="respondeid" value="<?php echo $comentario['id']; ?>">
                                                    <input type="hidden" name="titulo" value="<?php echo $post['title']; ?>">
                                                    <textarea name="comment" class="form-control mb-2 w-100" placeholder="Escreva a sua resposta aqui..."></textarea>
                                                    <div class="d-flex gap-2">
                                                        <input type="submit" name="cancelar" value="Cancelar" class="btn btn-sm btn-outline-secondary">
                                                        <input type="submit" name="novoComentario" value="Enviar Resposta" class="btn btn-sm btn-outline-secondary">
                                                    </div>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($_SESSION['user'] != $comentario['autor'] && !isset($_POST['respondeid']) && !isset($_POST['responderid'])): ?>
                                                <form action="/Blog/verPost" method="POST" class="m-0 p-0 d-inline-block align-self-start h-auto">
                                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                                    <input type="hidden" name="respondeid" value="<?php echo $comentario['id']; ?>">
                                                    <input type="hidden" name="titulo" value="<?php echo $post['title']; ?>">
                                                    <input type="submit" name="responder" value="Responder" class="btn btn-sm btn-outline-secondary">
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php verRespostas($comentario['respostas'], $post); ?>

                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['user']) && $_SESSION['user'] != $post['postado'] && !isset($_POST['respondeid'])): ?>
                            <form action="/Blog/verPost" method="POST" class="mb-3 me-3 w-100">
                                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                <input type="hidden" name="titulo" value="<?php echo $post['title']; ?>">
                                <label for="comment" class="form-label">Adicionar um comentário</label>
                                <textarea id="comment" name="comment" class="form-control mb-2 w-100" placeholder="Escreva seu comentário aqui..."></textarea>
                                <div class="d-flex gap-2">
                                    <input type="submit" class="btn btn-sm btn-outline-secondary" name="novoComentario" value="Enviar Comentário">
                                </div>
                            </form>
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