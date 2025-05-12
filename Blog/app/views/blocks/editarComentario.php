<?php 

function editarComentarioForm($comentID, $editEid, $conteudo){
    ?>
    <form action="/Blog/verPost" method="POST" class="mt-3 mb-3 w-100">
    <input type="hidden" name="id" value="<?php echo $comentID; ?>">
    <input type="hidden" name="editarid" value="<?php echo $editEid; ?>">
    <textarea name="comment" class="form-control mb-2 w-100"><?php echo htmlspecialchars($conteudo); ?></textarea>
    <div class="d-flex gap-2">
        <input type="submit" name="cancelar" value="Cancelar" class="btn btn-sm btn-outline-secondary">
        <input type="submit" name="editarComentario" value="Gravar" class="btn btn-sm btn-outline-secondary">
    </div>
</form>
<?php
}
?>