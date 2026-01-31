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
          <input type="hidden" name="csrf_token" value="{$csrf_token}">
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
        <div class="alert alert-info" role="alert">
          Die Registrierung ist in dieser Demo-Version deaktiviert.
        </div>
        <p>Um die Funktionen von StudyHub zu testen, können Sie sich mit einem der Test-Accounts einloggen.</p>
        <div class="text-center mt-4">
          <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Schließen</button>
        </div>
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
  const loginToggle = document.getElementById('toggleLoginPassword');
  if (loginToggle) {
    loginToggle.addEventListener('click', () => {
      togglePasswordVisibility('loginPassword', 'toggleLoginPassword');
    });
  }

  // Event Listener für die Registrierungspasswörter
  const regToggle = document.getElementById('togglePassword');
  if (regToggle) {
    regToggle.addEventListener('click', () => {
      togglePasswordVisibility('password', 'togglePassword');
    });
  }

  const regConfirmToggle = document.getElementById('togglePasswordConfirm');
  if (regConfirmToggle) {
    regConfirmToggle.addEventListener('click', () => {
      togglePasswordVisibility('password_confirm', 'togglePasswordConfirm');
    });
  }

  // Event Listener für Passwortänderungen (Profil/Seite)
  const newPwToggle = document.getElementById('toggleNewPassword');
  if (newPwToggle) {
    newPwToggle.addEventListener('click', () => {
      togglePasswordVisibility('new_password', 'toggleNewPassword');
    });
  }

  const newPwConfirmToggle = document.getElementById('toggleNewPasswordConfirm');
  if (newPwConfirmToggle) {
    newPwConfirmToggle.addEventListener('click', () => {
      togglePasswordVisibility('new_password_confirm', 'toggleNewPasswordConfirm');
    });
  }

  // Event Listener für Passwort-Reset Felder
  const resetToggle = document.getElementById('toggleResetPassword');
  if (resetToggle) {
    resetToggle.addEventListener('click', () => {
      togglePasswordVisibility('password', 'toggleResetPassword');
    });
  }

  const resetConfirmToggle = document.getElementById('toggleResetPasswordConfirm');
  if (resetConfirmToggle) {
    resetConfirmToggle.addEventListener('click', () => {
      togglePasswordVisibility('password_confirm', 'toggleResetPasswordConfirm');
    });
  }
</script>