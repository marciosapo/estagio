<?php 

function editarPostForm($postID, $titulo, $conteudo){
    ?>
    <form method="POST" action="/Blog/verPost" class="mb-4">
        <input type="hidden" name="id" value="<?= $postID; ?>">
        <div class="mb-3">
            <label for="tituloEditado" class="form-label">Editar Título</label>
            <input type="text" class="form-control" id="tituloEditado" name="tituloEditado" value="<?= htmlspecialchars($titulo); ?>" required>
        </div>
        <div class="mb-3">
            <label for="conteudoEditado" class="form-label">Editar Conteúdo</label>
            <textarea class="form-control" id="conteudoEditado" name="conteudoEditado" rows="6" required><?= htmlspecialchars($conteudo); ?></textarea>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" name="salvarEdicao" class="btn btn-sm btn-outline-success">Salvar</button>
            <button type="submit" name="apagarEdicao" class="btn btn-sm btn-outline-secondary">Cancelar</button>
        </div>
    </form>
<?php
}
?>