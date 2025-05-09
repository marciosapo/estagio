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
    
<h1 class="text-center text-primary mb-5 text-shadow">Posts do Blog</h1>

<div class="container">
<form method="POST" action="/Blog/" class="d-flex flex-wrap gap-2 justify-content-center mb-5">
<div class="col-12 col-md-4">
  <input class="form-control form-control-lg" name="pesquisa" type="search" placeholder="Pesquisar post..." aria-label="Pesquisa">
</div> 

  <button class="btn btn-success btn-lg" type="submit">
    <i class="bi bi-search"></i> Pesquisar
  </button>

  <input class="btn btn-primary btn-lg" name="recente" type="submit" value="Mais Recente">

  <button type="submit" name="doAntigo" class="btn btn-outline-primary btn-lg">
    <i class="bi bi-arrow-up me-1"></i> Antigos
  </button>

  <button type="submit" name="doRecente" class="btn btn-outline-primary btn-lg">
    <i class="bi bi-arrow-down me-1"></i> Recentes
  </button>

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
<?php if (isset($_SESSION['flash_sucesso'])): ?>
    <div class="alert alert-success" id="flash-sucesso">
        <?= $_SESSION['flash_sucesso']; unset($_SESSION['flash_sucesso']); ?>
    </div>
<?php 
    unset($_SESSION['flash_sucesso']);
    unset($_SESSION['flash_erro']);
    endif; 
?>

<?php if (isset($_SESSION['flash_erro'])): ?>
    <div class="alert alert-danger" id="flash-erro">
        <?= $_SESSION['flash_erro']; unset($_SESSION['flash_erro']); ?>
    </div>
    <?php 
    unset($_SESSION['flash_sucesso']);
    unset($_SESSION['flash_erro']);
    endif; 
?>
<?php if (isset($posts) && !empty($posts)): ?>
    <div class="row justify-content-center mt-4 g-4">
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
    </div>
<?php else: ?>
    <div class="container py-5">
        <p class="text-center text-muted fs-5">Nenhum post encontrado.</p>
    </div>
<?php endif; ?>