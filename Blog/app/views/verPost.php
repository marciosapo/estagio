<?php

$formatter = new IntlDateFormatter(
    'pt_PT',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    'Europe/Lisbon',
    IntlDateFormatter::GREGORIAN
);

function verRespostas($respostas, $post, $nivel = 1) {
    if (empty($respostas)) return;
    echo '<div class="respostas ml-' . ($nivel * 2) . ' border p-2">';
    foreach ($respostas as $resposta):
?>
    <div class="resposta mb-3 border p-2">
        <strong><?php echo htmlspecialchars($resposta['autor']) . " (" . tempoDecorrido($resposta['post_data']) . ")"; ?></strong>:
        <p class="text-muted"><?php echo nl2br(htmlspecialchars($resposta['comentario'])); ?></p>
        <?php if ($_SESSION['user'] != $resposta['autor'] && !isset($_POST['responderid']) && !isset($_POST['respondeid'])): ?>
            <form action="/Blog/verPost" method="POST" class="ml-3 mb-3">
                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                <input type="hidden" name="respondeid" value="<?php echo $resposta['id']; ?>">
                <input type="hidden" name="titulo" value="<?php echo $post['title']; ?>">
                <input type="submit" name="responder" value="Responder" class="btn btn-sm btn-secondary">
            </form>
        <?php endif; ?>
        <?php if (isset($_POST['respondeid']) && $_POST['respondeid'] == $resposta['id']): ?>
            <form action="/Blog/verPost" method="POST" class="ml-3 mb-3">
                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                <input type="hidden" name="id_parent" value="<?php echo $resposta['id']; ?>">
                <input type="hidden" name="titulo" value="<?php echo $post['title']; ?>">
                <div class="form-group">
                    <textarea name="comment" class="form-control" rows="3" placeholder="Escreva a sua resposta aqui..."></textarea>
                </div>
                <input type="submit" name="cancelar" value="Cancelar" class="btn btn-secondary">
                <input type="submit" name="novoComentario" value="Enviar Resposta" class="btn btn-secondary">
            </form>
        <?php endif; ?>
        <?php if (!empty($resposta['respostas'])): ?>
            <?php verRespostas($resposta['respostas'], $post, $nivel + 1); ?>
        <?php endif; ?>
    </div>
<?php
    endforeach;
    echo '</div>';
}
?>
<div class="container ml-5">
    <?php if (isset($post) && !empty($post)): ?>
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card border-dark custom-shadow">
                    <div class="card-body py-4">
                        <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($post['post'])); ?></p>
                        <div class="d-flex justify-content-end align-items-center gap-2 mt-3 mb-3 mr-3">
                            <p class="text-muted mb-0">
                                Postado por <strong><?php echo htmlspecialchars($post['postado']); ?></strong>
                                em <time datetime="<?php echo $post['post_data']; ?>"><?php echo $formatter->format(new DateTime($post['post_data'])); ?></time>
                            </p>
                        </div>
                        <?php if (!empty($post['comentarios'])): ?>
                            <div id="comment-section-<?php echo $post['id']; ?>" class="mt-4">
                                <h5>Comentários:</h5>
                                <?php foreach ($post['comentarios'] as $comentario): ?>
                                    <div class="comment mb-3 border p-2">
                                        <strong><?php echo htmlspecialchars($comentario['autor']) . " (" . tempoDecorrido($comentario['post_data']) . ")"; ?></strong>:
                                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                                        <?php if (isset($_SESSION['user'])): ?>
                                            <?php if ($_SESSION['user'] == $comentario['autor'] && !isset($_POST['editar'])): ?>
                                                <form action="/Blog/verPost" method="POST" class="ml-3 mb-3">
                                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                                    <input type="hidden" name="editarid" value="<?php echo $comentario['id']; ?>">
                                                    <input type="hidden" name="titulo" value="<?php echo $post['title']; ?>">
                                                    <input type="submit" name="editar" value="Editar" class="btn btn-sm btn-secondary">
                                                    <input type="submit" name="apagar" value="Apagar" class="btn btn-sm btn-secondary">
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($_SESSION['user'] == $comentario['autor'] && isset($_POST['editarid']) && $_POST['editarid'] == $comentario['id']): ?>
                                                <form action="/Blog/verPost" method="POST" class="ml-3 mb-3">
                                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                                    <input type="hidden" name="titulo" value="<?php echo $post['title']; ?>">
                                                    <div class="form-group">
                                                        <textarea name="comment" class="form-control" rows="3"><?php echo htmlspecialchars($comentario['comentario']); ?></textarea>
                                                    </div>
                                                    <input type="submit" name="cancelar" value="Cancelar" class="btn btn-secondary">
                                                    <input type="submit" name="editar" value="Gravar" class="btn btn-secondary">
                                                </form>
                                            <?php endif; ?>
                                            <?php if (isset($_POST['responder']) && $_POST['respondeid'] == $comentario['id']): ?>
                                                <form action="/Blog/verPost" method="POST" class="ml-3 mb-3">
                                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                                    <input type="hidden" name="id_parent" value="<?php echo $comentario['id']; ?>">
                                                    <input type="hidden" name="respondeid" value="<?php echo $comentario['id']; ?>">
                                                    <input type="hidden" name="titulo" value="<?php echo $post['title']; ?>">
                                                    <div class="form-group">
                                                        <textarea name="comment" class="form-control" rows="3" placeholder="Escreva a sua resposta aqui..."></textarea>
                                                    </div>
                                                    <input type="submit" name="cancelar" value="Cancelar" class="btn btn-secondary">
                                                    <input type="submit" name="novoComentario" value="Enviar Resposta" class="btn btn-secondary">
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($_SESSION['user'] != $comentario['autor'] && !isset($_POST['respondeid']) && !isset($_POST['responderid'])): ?>
                                                <form action="/Blog/verPost" method="POST" class="ml-3 mb-3">
                                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                                    <input type="hidden" name="respondeid" value="<?php echo $comentario['id']; ?>">
                                                    <input type="hidden" name="titulo" value="<?php echo $post['title']; ?>">
                                                    <input type="submit" name="responder" value="Responder" class="btn btn-sm btn-secondary">
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php verRespostas($comentario['respostas'], $post); ?>

                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['user']) && $_SESSION['user'] != $post['postado'] && !isset($_POST['respondeid'])): ?>
                            <form action="/Blog/verPost" method="POST" class="mt-3 mb-3 me-3">
                                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                <input type="hidden" name="titulo" value="<?php echo $post['title']; ?>">
                                
                                <!-- Formulário de comentário -->
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Adicionar um comentário</label>
                                    <textarea id="comment" name="comment" class="form-control" rows="3" placeholder="Escreva seu comentário aqui..."></textarea>
                                </div>
                                
                                <input type="submit" class="btn btn-secondary" name="novoComentario" value="Enviar Comentário">
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