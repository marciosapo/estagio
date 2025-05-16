<?php require_once __DIR__ . '/blocks/flash.php'; ?>
<section class="vh-100">
  <div class="container-fluid h-custom">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-md-9 col-lg-6 col-xl-5">
        <img src="/imgs/login.webp"
          class="img-fluid" alt="Sample image">
      </div>
      <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
        <form action="/Blog/login" method="POST">
          <div data-mdb-input-init class="form-outline mb-4">
          <label class="form-label mb-0"> Username</label>
            <input type="text" id="form3Example3" name="user" class="form-control form-control-lg"
              placeholder="username" />
          </div>
          <div data-mdb-input-init class="form-outline mb-0">
            <label class="form-label mb-0">Palavra-passe</label>  
            <input type="password" id="form3Example4" name="pass" class="form-control form-control-lg"
                placeholder="palavra-passe" />
          </div>
          <div class="text-center text-lg-start mt-5 pt-2">
            <button  type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
              style="padding-left: 2.5rem; padding-right: 2.5rem;">Login</button>
            <p class="small fw-bold mt-2 pt-1 mb-0">Não tem conta? <a href="/Blog/registar"
                class="link-danger">Registar</a></p>
          </div>

        </form>
      </div>
    </div>
  </div>
</section>