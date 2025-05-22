<?php require_once __DIR__ . '/blocks/flash.php'; ?>
<div class="container-fluid">
<div class="row">
    <div class="col-md-6 d-none d-md-block image-side"><img src="https://www.itinsight.pt/img/uploads/750x421_abbe7a45b9bf7edce6472496ff1614f5.jpg" width="100%" height="100%"></div>
    <div class="col-md-6 d-flex align-items-center justify-content-center">
      <div class="form-card w-75">
        <h4 class="text-center mb-4 text-primary">Dados do <?php echo $result['username']; ?></h4>
        <div class="mb-3">
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" value="<?php echo htmlspecialchars($result['email']); ?>" name="email" class="form-control" id="email" required maxlength="100">
          </div>
          <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" value="<?php echo htmlspecialchars($result['nome']); ?>"name="nome" class="form-control" id="nome" required maxlength="100">
          </div>
          <div class="mb-3">
            <label for="token" class="form-label">Último Login</label>
            <input type="text" value="<?php echo htmlspecialchars($result['Ultimo_Login']); ?>" name="Ultimo_Login" class="form-control" id="Ultimo_Login" maxlength="255" readonly>
          </div>
          <div class="mb-3">
            <label for="token" class="form-label">Total de Posts criados: </label>
            <input type="text" value="<?php echo htmlspecialchars($resultData['total_posts']); ?>" name="total_posts" class="form-control" id="total_posts" maxlength="255" readonly>
          </div>
          <div class="mb-3">
            <label for="token" class="form-label">Total de Comentários: </label>
            <input type="text" value="<?php echo htmlspecialchars($resultData['total_comments']); ?>" name="total_comments" class="form-control" id="total_comments" maxlength="255" readonly>
          </div>
      </div>
    </div>
  </div>
</div>