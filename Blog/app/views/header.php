<nav class="navbar navbar-dark bg-dark text-light">
  <div class="container-fluid p-0">
    <a class="navbar-brand" href="/Blog/">Blog</a>
    
    
    <form method="POST" action="/Blog/" class="d-flex align-items-center ml-auto">
      <?php if (!isset($_SESSION['user'])): ?>
        <a href="/Blog/login" class="btn btn-secondary ml-2">Login</a>
        <a href="/Blog/registar" class="btn btn-secondary ml-2 mr-2">Registar</a>
      <?php else: ?>
        <div class="dropdown mr-1">
          <button class="btn btn-secondary dropdown-toggle bg-dark border-0 " type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <img src="https://cdn-icons-png.flaticon.com/512/9187/9187604.png" alt="User" width="30" height="30" class="me-2">
            <?php echo htmlspecialchars($_SESSION['user']); ?>
          </button>
          <div class="dropdown-menu p-0" aria-labelledby="dropdownMenuButton">
            <div class="dropdown-divider m-0"></div>
            <a class="dropdown-item" href="/Blog/dados">
              <i class="bi bi-person-lines-fill me-2"></i> Ver Dados
            </a>
            <a class="dropdown-item" href="/Blog/logout">
              <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
          </div>
        </div>
      <?php endif; ?>
    </form>
  </div>
</nav>
<img src="https://www.freeprivacypolicy.com/public/uploads/2020/04/personal-general-blogs-02.jpg" width="100%" height="250px">