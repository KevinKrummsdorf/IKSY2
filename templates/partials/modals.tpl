{* Login Modal *}
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loginModalLabel">Login</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
      <div class="modal-body">
        <div id="loginAlert" class="alert alert-danger d-none" role="alert" aria-live="assertive"></div>

        <form id="login-form" method="POST" action="{url path='login'}" novalidate>
          <div class="mb-3">
            <label for="username_or_email" class="form-label">Username oder E-Mail</label>
            <input type="text" class="form-control" id="username_or_email" name="username_or_email"
                   required autocomplete="username" aria-describedby="login-help">
          </div>

          <div class="mb-3 pass-field">
            <label for="loginPassword" class="form-label">Passwort</label>
            <div class="input-group">
              <input type="password" class="form-control" id="loginPassword" name="password"
                     required autocomplete="current-password">
              <span class="input-group-text" id="toggleLoginPassword" style="cursor: pointer;">
                <span class="material-symbols-outlined">visibility</span>
              </span>
            </div>
          </div>

          <input type="hidden" name="recaptcha_token" id="login-recaptcha-token">

          <button type="submit" class="btn btn-primary w-100 position-relative">
            <span id="login-spinner" class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
            Login
          </button>
          <div class="mt-2 text-end">
            <a href="{url path='request_password_reset'}">Passwort vergessen?</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{* Registration Modal *}
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registerModalLabel">Registrierung</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
      <div class="modal-body">
        <div id="registerAlert" class="alert alert-danger d-none" role="alert" aria-live="assertive"></div>

        <form id="registerForm" method="POST" action="{url path='register'}" data-pw-validate novalidate>
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">E-Mail-Adresse</label>
            <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
          </div>

          <div class="mb-3 pass-field">
            <label for="password" class="form-label">Passwort</label>
            <div class="input-group">
              <input type="password" class="form-control pw-new" id="password" name="password" required>
              <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                <span class="material-symbols-outlined">visibility</span>
              </span>
            </div>
            <ul class="requirement-list mb-3">
              <li data-requirement="minlength"><i class="material-symbols-outlined">close</i>Mindestens 8 Zeichen</li>
              <li data-requirement="maxlength"><i class="material-symbols-outlined">close</i>Maximal 128 Zeichen</li>
              <li data-requirement="number"><i class="material-symbols-outlined">close</i>Mindestens eine Zahl</li>
              <li data-requirement="lowercase"><i class="material-symbols-outlined">close</i>Kleinbuchstabe</li>
              <li data-requirement="uppercase"><i class="material-symbols-outlined">close</i>Großbuchstabe</li>
              <li data-requirement="special"><i class="material-symbols-outlined">close</i>Sonderzeichen</li>
            </ul>
          </div>

          <div class="mb-3 pass-field">
            <label for="password_confirm" class="form-label">Passwort bestätigen</label>
            <div class="input-group">
                <input type="password" class="form-control pw-confirm" id="password_confirm" name="password_confirm" required>
              <span class="input-group-text" id="togglePasswordConfirm" style="cursor: pointer;">
                <span class="material-symbols-outlined">visibility</span>
              </span>
            </div>
          </div>

          <input type="hidden" name="recaptcha_token" id="register-recaptcha-token">

          <button type="submit" class="btn btn-primary w-100 position-relative">
            <span id="register-spinner" class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
            Registrieren
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  // Funktion zum Umschalten der Passwortsichtbarkeit
  function togglePasswordVisibility(passwordFieldId, toggleIconId) {
    const passwordField = document.getElementById(passwordFieldId);
    const toggleIcon = document.getElementById(toggleIconId).querySelector('span');

    if (passwordField.type === 'password') {
      passwordField.type = 'text';
      toggleIcon.textContent = 'visibility_off'; // Ändert das Icon zu "visibility_off"
    } else {
      passwordField.type = 'password';
      toggleIcon.textContent = 'visibility'; // Setzt das Icon auf "visibility"
    }
  }

  // Event Listener für das Umschalten der Passwortsichtbarkeit im Login Modal
  document.getElementById('toggleLoginPassword').addEventListener('click', function() {
    togglePasswordVisibility('loginPassword', 'toggleLoginPassword');
  });

  // Event Listener für das Umschalten der Passwortsichtbarkeit im Registration Modal
  document.getElementById('togglePassword').addEventListener('click', function() {
    togglePasswordVisibility('password', 'togglePassword');
  });

  // Event Listener für das Umschalten der Passwortsichtbarkeit im Confirm Password Feld
  document.getElementById('togglePasswordConfirm').addEventListener('click', function() {
    togglePasswordVisibility('password_confirm', 'togglePasswordConfirm');
  });
</script>