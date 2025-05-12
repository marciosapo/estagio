<?php if (isset($_SESSION['flash_sucesso'])): ?>
    <div class="alert alert-success popup" id="flash-sucesso">
        <?= $_SESSION['flash_sucesso']; unset($_SESSION['flash_sucesso']); ?>
    </div>
<?php 
    unset($_SESSION['flash_sucesso']);
    unset($_SESSION['flash_erro']);
    endif; 
?>

<?php if (isset($_SESSION['flash_erro'])): ?>
    <div class="alert alert-danger popup" id="flash-erro">
        <?= $_SESSION['flash_erro']; unset($_SESSION['flash_erro']); ?>
    </div>
    <?php 
    unset($_SESSION['flash_sucesso']);
    unset($_SESSION['flash_erro']);
    endif; 
?>