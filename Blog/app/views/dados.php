<div class="container-fluid">
<div class="row">
    <div class="col-md-6 d-none d-md-block image-side"><img src="https://www.itinsight.pt/img/uploads/750x421_abbe7a45b9bf7edce6472496ff1614f5.jpg" width="100%" height="100%"></div>
    <div class="col-md-6 d-flex align-items-center justify-content-center">
      <div class="form-card w-75">
        <h4 class="text-center mb-4 text-primary">Dados da Conta</h4>
        <form method="POST" action="/Blog/atualizarDados/">
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" value="<?php echo $result['username']; ?>" name="username" class="form-control" id="username" required maxlength="50" readonly>
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
            <label for="pass" class="form-label">Palavra-passe</label>
            <input type="password" value="<?php echo htmlspecialchars($result['pass']); ?>" name="pass" class="form-control" id="pass" required maxlength="255">
          </div>
          <div class="mb-3">
            <label for="token" class="form-label">Token Ativo</label>
            <input type="text" value="<?php echo htmlspecialchars($result['token']); ?>" name="token" class="form-control" id="token" required maxlength="255" readonly>
          </div>
          <?php if(isset($erro) && $erro): ?>
          <div class="ms-auto">
          <p class="text-center text-danger"><?php echo 'ERRO: ' . $erro ?></p>
          </div> 
<?php elseif(isset($sucesso) && $sucesso): ?>
          <div class="ms-auto">
          <p class="text-center text-success">Dados atualizados com sucesso...</p>
          </div> 
          <?php endif; ?>
          <input type="submit" class="btn btn-primary w-100" name="atualizarDados" value="Atualizar">
        </form>
      </div>
    </div>
  </div>
</div>