<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loginModalLabel">Login</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
      <div class="modal-body">
        <div id="loginAlert" class="mt-2"></div>
        <form id="login-form" action="login.php" method="POST" novalidate>
          <div class="mb-3 position-relative">
            <input 
              type="text" 
              id="username_or_email" 
              name="username_or_email" 
              class="form-control" 
              placeholder="Username oder E-Mail" 
              required>
            <i class="bx bxs-user position-absolute top-50 end-0 translate-middle-y pe-3"></i>
          </div>

          <div class="mb-3 position-relative">
            <input 
              type="password" 
              id="loginPassword" 
              name="password" 
              class="form-control" 
              placeholder="Passwort" 
              required>
            <i class="bx bxs-lock-alt position-absolute top-50 end-0 translate-middle-y pe-3"></i>
          </div>

          <input type="hidden" name="recaptcha_token" id="login-recaptcha-token">

          <button type="submit" class="btn btn-primary w-100 position-relative">
            <span id="login-spinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
            Login
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Registration Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registerModalLabel">Registrierung</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
      <div class="modal-body">
        <div id="globalAlert" class="mt-2"></div>
        <form id="registerForm" action="register.php" method="POST" novalidate>
          <div class="mb-3 position-relative">
            <input 
              type="text" 
              id="username" 
              name="username" 
              class="form-control" 
              placeholder="Username" 
              required>
            <i class="bx bxs-user position-absolute top-50 end-0 translate-middle-y pe-3"></i>
          </div>

          <div class="mb-3 position-relative">
            <input 
              type="email" 
              id="email" 
              name="email" 
              class="form-control" 
              placeholder="E-Mail" 
              required>
            <i class="bx bxs-envelope position-absolute top-50 end-0 translate-middle-y pe-3"></i>
          </div>

          <div class="mb-3 position-relative pass-field">
            <input 
              type="password" 
              id="password" 
              name="password" 
              class="form-control" 
              placeholder="Passwort" 
              required>
            <i class="bx bxs-lock-alt position-absolute top-50 end-0 translate-middle-y pe-3 eye-icon"></i>
          </div>

          <ul class="requirement-list mb-3" style="display: none;">
            <li data-requirement="minlength"><i class="fa-solid fa-circle"></i><span>Mindestens 8 Zeichen</span></li>
            <li data-requirement="maxlength"><i class="fa-solid fa-circle"></i><span>Maximal 128 Zeichen</span></li>
            <li data-requirement="number"><i class="fa-solid fa-circle"></i><span>Mindestens eine Zahl</span></li>
            <li data-requirement="lowercase"><i class="fa-solid fa-circle"></i><span>Mindestens ein Kleinbuchstabe</span></li>
            <li data-requirement="special"><i class="fa-solid fa-circle"></i><span>Mindestens ein Sonderzeichen</span></li>
            <li data-requirement="uppercase"><i class="fa-solid fa-circle"></i><span>Mindestens ein Großbuchstabe</span></li>
          </ul>

          <div class="mb-3 position-relative pass-field">
            <input 
              type="password" 
              id="password_confirm" 
              name="password_confirm" 
              class="form-control" 
              placeholder="Passwort bestätigen" 
              required>
            <i class="bx bxs-lock-alt position-absolute top-50 end-0 translate-middle-y pe-3 eye-icon"></i>
          </div>

          <input type="hidden" name="recaptcha_token" id="register-recaptcha-token">

          <button type="submit" class="btn btn-primary w-100 position-relative">
            <span
              id="register-spinner"
              class="spinner-border spinner-border-sm me-2 d-none"
              role="status"
              aria-hidden="true"
            ></span>
            Registrieren
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
