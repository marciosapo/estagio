<?php 
$posts = $resultado['posts'];
$paginaAtual = $resultado['pagina_atual'];
$porPagina = $resultado['por_pagina'];
$totalPosts = $resultado['total_posts'];
$totalPaginas = ceil($totalPosts / $porPagina);
?>
<?php if (
      isset($_SESSION['user']) &&
      isset($_SESSION['nivel']) &&
      ($_SESSION['nivel'] == "Owner" || $_SESSION['nivel'] == "Admin")
  ): ?>
    <a class="btn btn-outline-dark btn-lg d-flex align-items-center" href="/Blog/novoPost/">
      <i class="bi bi-pencil-square me-2"></i> Novo Post
    </a>
  <?php endif; ?>
</form>
</div>
<?php require_once __DIR__ . '/../blocks/flash.php'; ?>
<?php if (isset($posts) && !empty($posts)): ?>
    <div class="row justify-content-center mt-4 g-4 border-dark custom-shadow">
        <?php foreach ($posts as $post): ?>
            <div class="col-12 col-md-10">
                <form method="POST" action="/Blog/verPost" class="h-100">
                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                    <div class="card shadow-sm border-0 bg-light h-100">
    <div class="d-flex">
        <div class="w-25">
            <img src="https://www.blogtyrant.com/wp-content/uploads/2020/02/how-long-should-a-blog-post-be.png" 
                 alt="Imagem do post" 
                 class="img-fluid object-fit-cover rounded" 
                 style="height: 250px;">
        </div>
        <div class="card-body w-75">
            <h4 class="card-title fw-semibold text-primary"><?php echo htmlspecialchars($post['title']); ?></h4>
            <p class="card-text text-dark mt-4"><?php echo nl2br(htmlspecialchars($post['post'])); ?></p>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center bg-white border-0 pt-0 px-4 pb-4">
        <small class="text-muted mt-4">
            Postado por <strong><?php echo htmlspecialchars($post['postado']); ?></strong> 
            em <time datetime="<?php echo $post['post_data']; ?>">
                <?php echo $formatter->format(new DateTime($post['post_data'])); ?>
            </time>
        </small>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-outline-secondary btn-sm" name="verComentarios">
                Comentários (<?php echo $post['nComentarios']; ?>)
            </button>

            <?php if (isset($_SESSION['user']) && $_SESSION['user'] === $post['postado']): ?>
                <button type="submit" class="btn btn-outline-warning btn-sm" name="editarPost">
                    Editar
                </button>
                <button type="submit" class="btn btn-outline-danger btn-sm" name="apagarPost" onclick="return confirm('Tem certeza que deseja apagar este post?');">
                    Apagar
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
                </form>
            </div>
        <?php endforeach; ?>
        <?php if ($totalPaginas > 1): ?>
<div class="d-flex justify-content-center mt-5">
    <nav>
        <ul class="pagination">
            <?php if ($paginaAtual > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?php echo $paginaAtual - 1; ?>">Anterior</a>
                </li>
            <?php else: ?>
                <li class="page-item disabled"><span class="page-link">Anterior</span></li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?php echo ($i == $paginaAtual) ? 'active' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($paginaAtual < $totalPaginas): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?php echo $paginaAtual + 1; ?>">Próxima</a>
                </li>
            <?php else: ?>
                <li class="page-item disabled"><span class="page-link">Próxima</span></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<?php endif; ?>
    </div>
<?php else: ?>
    <div class="container py-5">
        <p class="text-center text-muted fs-5">Nenhum post encontrado.</p>
    </div>
<?php endif; ?>