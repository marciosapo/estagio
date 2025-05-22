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
    
<h1 class="text-center text-primary mb-5 text-shadow">&#128218; Posts do Blog</h1>

<div class="container">
  <form method="POST" action="/Blog" class="row g-3 justify-content-center align-items-center mb-5 p-4 rounded bg-light shadow-sm">
    <div class="col-12 col-md-6 col-lg-3">
      <input 
        class="form-control form-control-lg border-primary shadow-sm" 
        name="pesquisa" 
        type="search" 
        placeholder="🔍 Pesquisar post..." 
        aria-label="Pesquisa">
    </div> 
    <div class="col-4 col-md-auto">
      <button class="btn btn-success btn-lg w-100 shadow-sm" type="submit">
        <i class="bi bi-search me-1"></i> Pesquisar
      </button>
    </div>
    <div class="col-4 col-md-auto">
      <input class="btn btn-primary btn-lg w-100 shadow-sm" name="recente" type="submit" value="Mais Recente">
    </div>
    <div class="col-4 col-md-auto">
      <button type="submit" name="doAntigo" class="btn btn-outline-primary btn-lg w-100 shadow-sm">
        <i class="bi bi-arrow-up me-1"></i> Antigos
      </button>
    </div>
    <div class="col-4 col-md-auto">
      <button type="submit" name="doRecente" class="btn btn-outline-primary btn-lg w-100 shadow-sm">
        <i class="bi bi-arrow-down me-1"></i> Recentes
      </button>
    </div>
  <?php include 'posts/lista.php'; ?>
