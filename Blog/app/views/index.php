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
<form method="POST" action="/Blog" class="d-flex flex-wrap gap-2 justify-content-center mb-5">
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
  <?php include 'posts/lista.php'; ?>