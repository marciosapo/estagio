<?php 

function responderForm($comentID, $respondeid){
    ?>
    <form action="/Blog/verPost" method="POST" class="mt-3 mb-3 w-100">
        <input type="hidden" name="id" value="<?php echo $comentID; ?>">
        <input type="hidden" name="respondeid" value="<?php echo $respondeid ?>">
        <button type="submit" name="responder" class="btn btn-sm btn-outline-secondary">Responder</button>
    </form>
    <?php
}
?>
