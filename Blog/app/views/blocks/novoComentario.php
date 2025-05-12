<?php 

function novoComentarioForm($responderID, $id_parent, $respondeId = null){
    ?>
    <form method="POST" action="/Blog/verPost" class="mt-3 mb-3 w-100">
        <input type="hidden" name="id" value="<?= $responderID ?>">
        <input type="hidden" name="id_parent" value="<?= $id_parent ?>">
        <?php if (!is_null($respondeId)): ?>
            <input type="hidden" name="respondeid" value="<?= htmlspecialchars($respondeId, ENT_QUOTES, 'UTF-8'); ?>">
        <?php endif; ?>
        <textarea name="comment" class="form-control mb-2 w-100" placeholder="Escreva sua resposta..."></textarea>
        <div class="d-flex gap-2">
            <button type="submit" name="cancelar" class="btn btn-outline-secondary btn-sm">Cancelar</button>
            <button type="submit" name="novoComentario" class="btn btn-outline-secondary btn-sm">Enviar</button>
        </div>
    </form>
    <?php
}

function novoComentarioBaseForm($comentarID){
    ?>
    <form method="POST" action="/Blog/verPost" class="mt-3 mb-3 w-100">
        <input type="hidden" name="id" value="<?= $comentarID ?>">
        <label for="comment" class="form-label">Adicionar um comentário</label>
        <textarea name="comment" class="form-control mb-2 w-100" placeholder="Escreva sua resposta..."></textarea>
        <div class="d-flex gap-2">
            <input type="submit" class="btn btn-sm btn-outline-secondary" name="novoComentario" value="Enviar Comentário">
        </div>
    </form>
    <?php
}
?>