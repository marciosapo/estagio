<?php require_once __DIR__ . '/blocks/flash.php'; ?>
<form action="/Blog/novoPost" method="POST" class="p-4 rounded bg-light shadow-sm">
  <h3 class="mb-4 text-primary">Criar Novo Post</h3>

  <div class="form-group">
    <label for="titulo">Título</label>
    <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Insira o título do post" required>
  </div>

  <div class="form-group">
    <label for="conteudo">Conteúdo</label>
    <textarea class="form-control no-resize" id="conteudo" name="conteudo" rows="6" placeholder="Escreva o conteúdo aqui..." required></textarea>
  </div>

  <div class="d-flex align-items-center gap-2 mt-3">
    <button type="submit" class="btn btn-primary">Publicar</button>
</div>
</form>