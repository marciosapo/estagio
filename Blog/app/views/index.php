<?php
$formatter = new IntlDateFormatter(
    'pt_PT',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    'Europe/Lisbon',
    IntlDateFormatter::GREGORIAN
);
?>
<div class="container-fluid ml-5 mr-8">
    
<h1 class="text-center text-primary mb-4 text-shadow">Posts do Blog</h1>
<form method="POST" action="/Blog/" class="d-flex justify-content-center mb-4">
  <input class="form-control me-2 w-25" name="pesquisa" type="search" placeholder="post" aria-label="Pesquisa">
  <button class="btn btn-outline-success me-2" type="submit">Pesquisar</button>
  <input class="btn btn-primary btn-lg me-2" name="recente" type="submit" value="Mais Recente">
  <button type="submit" name="doAntigo" class="btn btn-outline-primary btn-sm me-2">
    <i class="bi bi-arrow-up"></i> Antigos
  </button>
  <button type="submit" name="doRecente" class="btn btn-outline-primary btn-sm me-2">
    <i class="bi bi-arrow-down"></i> Recentes
  </button>
  <?php if (isset($_SESSION['user'])): ?>
    <a class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center me-2" href="/Blog/novoPost/">Novo Post</a>
  <?php endif; ?>
</form>
    <?php if (isset($posts) && !empty($posts)): ?>
        <div class="row justify-content-center mt-4">
        <div class="w-100"></div>
            <?php foreach ($posts as $post): ?>
                <form method="POST" action="/Blog/verPost" class="col-10 mb-4">
                    <input type=hidden name="id" value="<?php echo $post['id']; ?>">
                <div class="col-12 mb-1">
                    <div class="card border-dark custom-shadow">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $post['title']; ?></h5>
                            <p class="card-text"><?php echo $post['post']; ?></p>
                           
                        </div>
                        <div class="d-flex justify-content-end align-items-center gap-2 mt-3 mb-3 me-3">
                        <p class="text-muted mb-0">
                            Postado por <strong><?php echo $post['postado']; ?></strong> 
                            em <time datetime="<?php echo $post['post_data']; ?>">
                            <?php echo $formatter->format(new DateTime($post['post_data'])); ?>
                            </time>
                            /
                            <input type=submit class="btn btn-sm btn-outline-secondary"
                                value="Comentários (<?php echo $post['nComentarios']; ?>)">
                        </p>
                        </div>
                    </div>    
                </div>
                </form>
            <?php endforeach; ?>
        </div>
<?php else: ?>
    <div class="container py-5">
        <p class="text-center">Nenhum post encontrado.</p>
    </div>
<?php endif; ?>
</div>