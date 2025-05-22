<?php if (!empty($post['comentarios'])): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-4">Comentários</h5>
            <?php foreach ($post['comentarios'] as $comentario): ?>
                <div class="bg-light rounded p-3 mb-3 shadow-sm">
                    <strong class="text-primary">
                    <a href="/Blog/verUser?userId=<?php echo urlencode($comentario['id_user']); ?>&username=<?php echo urlencode($comentario['autor']); ?>">    
                    <?php echo htmlspecialchars($comentario['autor']); ?>
                    </a>
                    </strong>
                    <small class="text-muted"> • <?php echo tempoDecorrido($comentario['post_data']); ?></small>
                    <p class="mt-2 text-break"><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                    <?php if (isset($_SESSION['user'])): ?>
                        <?php if ($_SESSION['user'] == $comentario['autor'] && !isset($_POST['editar'])): ?>
                            <?php editForm($post['id'], $comentario['id'], $comentario['comentario']); ?>
                        <?php endif; ?>
                        <?php if ($_SESSION['user'] == $comentario['autor'] && isset($_POST['editarid']) && $_POST['editarid'] == $comentario['id'] && !isset($_POST['apagarComentario'])): ?>
                            <?php editComentarioForm($post['id'], $comentario['id'], $comentario['comentario']) ?>
                        <?php endif; ?>
                        <?php if (isset($_POST['responder']) && $_POST['respondeid'] == $comentario['id']): ?>
                            <?php novoComentarioForm($post['id'], $comentario['id'], $comentario['id']); ?>
                        <?php endif; ?>
                        <?php if ($_SESSION['user'] != $comentario['autor'] && !isset($_POST['respondeid']) && !isset($_POST['responderid'])): ?>
                            <?php responderForm($post['id'], $comentario['id']); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php verRespostas($comentario['respostas'], $post); ?>
            <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>