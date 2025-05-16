<?php require_once __DIR__ . '/blocks/flash.php'; ?>
<div class="container-fluid">
<div class="row">
    <div class="col-md-6 d-none d-md-block image-side"><img src="https://www.itinsight.pt/img/uploads/750x421_abbe7a45b9bf7edce6472496ff1614f5.jpg" width="100%" height="100%"></div>
    <div class="col-md-6 d-flex align-items-center justify-content-center">
      <div class="form-card w-75">
        <h4 class="text-center mb-5 text-primary">Modificar Administrador para User</h4>
        <form method="POST" action="/Blog/removerAdmin/">
        <div class="mb-3 mt-5">
          </div>
          <div class="mb-3">
                <label for="users" class="form-label">Escolhe o Administrador</label>
                <select id="users" name="users" class="form-control mb-5" aria-label="Escolher usuário">
                <?php if (isset($users) && !empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo htmlspecialchars($user['username']); ?>">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="">Nenhum Administrador encontrado</option>
                <?php endif; ?>
            </select>
          </div>
          <input type="submit" class="btn btn-primary w-100" name="removerAdmin" value="Alterar para User">
        </form>
      </div>
    </div>
  </div>
</div>