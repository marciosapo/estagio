<?php 

function editForm($comentID, $editEid){
    ?>
    <form action="/Blog/verPost" method="POST" class="mt-3 mb-3 w-100">
        <input type="hidden" name="id" value="<?php echo $comentID; ?>">
        <input type="hidden" name="editarid" value="<?php echo $editEid ?>">
        <div class="d-flex gap-2">
            <input type="submit" name="editar" value="Editar" class="btn btn-sm btn-outline-secondary">
            <input type="submit" onclick="return confirm('Tem certeza que deseja apagar este comentário?');" name="apagarComentario" value="Apagar" class="btn btn-sm btn-outline-secondary">
        </div>
    </form>
    <?php
}
?>