<nav class="navbar navbar-dark bg-dark text-light">
  <div class="container-fluid pr-0">
    <a class="navbar-brand" href="/Blog/">Blog</a>
    <form method="POST" action="/Blog/" class="d-flex align-items-center">
    <?php if (!isset($_SESSION['user'])): ?>
        <!-- Se não estiver logado, mostra Login e Registar -->
        <a href="/Blog/login" class="btn btn-secondary ml-2">Login</a>
        <a href="/Blog/registar" class="btn btn-secondary ml-2 mr-2">Registar</a>

      <?php else: ?>
        <!-- Se estiver logado, mostra ícone e dropdown -->
        <div class="dropdown mr-2">
  <button class="btn btn-secondary dropdown-toggle bg-dark border-0 " type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
  <img src="https://cdn-icons-png.flaticon.com/512/9187/9187604.png" alt="User" width="30" height="30" class="me-2">
  </button>
  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
    <a class="dropdown-item" href="/Blog/dados">Ver Dados</a>
    <a class="dropdown-item" href="/Blog/logout">Logout</a>
  </div>
</div>
      <?php endif; ?>
      <input class="form-control me-2" name="pesquisa" type="search" placeholder="post" aria-label="Pesquisa">
      <button class="btn btn-outline-success ml-2" type="submit">Pesquisar</button>
    </form>
  </div>
</nav>
<img src="https://www.freeprivacypolicy.com/public/uploads/2020/04/personal-general-blogs-02.jpg" width="100%" height="250px">