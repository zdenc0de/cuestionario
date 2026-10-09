<?php require_once INCLUDES . 'admin/publicTop.php'; ?>

<div class="row justify-content-center">
  <div class="col-xl-10 col-lg-12 col-md-9">
    <div class="card o-hidden border-0 shadow-lg my-5" style="background-color: #FAF8F5;">
      <div class="card-body p-0">
        <div class="row">

          <!-- Panel izquierdo: fondo crema con logo -->
          <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center"
               style="background-color: #FAF8F5; min-height: 500px; border-right: 1px solid #e5e5e5;">
            <div class="text-center p-5">
              <img src="<?php echo URL; ?>assets/images/escudoEdomex.svg" 
                alt="Estado de México" 
                class="img-fluid mb-4" 
                style="width: 100%; max-width: 350px;">
              <h2 class="h5 font-weight-bold text-dark mb-2">
                Estado de México
              </h2>
              <p class="small text-muted mb-0">
                Secretaría de Cultura y Turismo
              </p>
            </div>
          </div>

          <!-- Panel derecho: formulario -->
          <div class="col-lg-6" style="background-color: #FAF8F5;">
            <div class="p-5">
              <div class="text-center mb-4">
                <span class="d-block text-uppercase small font-weight-bold text-muted mb-2" 
                      style="letter-spacing: 1px;">
                  Gobierno del Estado de México
                </span>
                <h1 class="h4 text-dark mb-2 font-weight-bold">
                  Acceso Administrativo
                </h1>
                <p class="small text-muted mb-0">
                  Plataforma de Gestión y Evaluación NOM-035-STPS-2018
                </p>
              </div>

              <form class="user" action="login/post_login" method="post" novalidate>
                <?php echo insert_inputs(); ?>

                <div class="row">
                  <div class="col-12 mb-3">
                    <?php echo Flasher::flash(); ?>
                  </div>

                  <div class="col-12 mb-3">
                    <label class="form-label text-dark" for="usuario">
                      Usuario <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="usuario" name="usuario" 
                           placeholder="ej. administrador o superusuario" required autocomplete="username">
                  </div>

                  <div class="col-12 mb-3">
                    <label class="form-label text-dark" for="password">
                      Contraseña <span class="text-danger">*</span>
                    </label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="••••••••" required autocomplete="current-password">
                  </div>
                </div>

                <div class="text-center mt-4">
                  <button class="btn btn-primary btn-block" type="submit">
                    Ingresar al panel
                  </button>
                </div>
              </form>

              <hr class="my-4">

              <div class="text-center">
                <a class="small text-muted" href="<?php echo get_base_url(); ?>cuestionario">
                  <i class="fas fa-arrow-left mr-1"></i>
                  Ir al acceso del cuestionario (encuestado)
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/publicBottom.php'; ?>