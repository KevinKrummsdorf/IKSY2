<?php
/* Smarty version 5.5.0, created on 2025-05-13 14:31:54
  from 'file:partials/modals.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.0',
  'unifunc' => 'content_68233bbaa151e1_34388745',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'a97ff512809b0df5657fb556960db19c57eb00b0' => 
    array (
      0 => 'partials/modals.tpl',
      1 => 1746806660,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_68233bbaa151e1_34388745 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates\\partials';
?><div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loginModalLabel">Login</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
      <div class="modal-body">
        <div id="loginAlert" class="alert alert-danger d-none" role="alert" aria-live="assertive"></div>

        <form id="login-form" method="POST" action="login.php" novalidate>
          <div class="mb-3">
            <label for="username_or_email" class="form-label">Benutzername oder E-Mail</label>
            <input type="text" class="form-control" id="username_or_email" name="username_or_email"
                   required autocomplete="username" aria-describedby="login-help">
          </div>

          <div class="mb-3">
            <label for="loginPassword" class="form-label">Passwort</label>
            <input type="password" class="form-control" id="loginPassword" name="password"
                   required autocomplete="current-password">
          </div>

          <input type="hidden" name="recaptcha_token" id="login-recaptcha-token">

          <button type="submit" class="btn btn-primary w-100 position-relative">
            <span id="login-spinner" class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
            Login
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registerModalLabel">Registrierung</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
      <div class="modal-body">
        <form id="registerForm" method="POST" action="register.php" novalidate>
          <div class="mb-3">
            <label for="username" class="form-label">Benutzername</label>
            <input type="text" class="form-control" id="username" name="username" required>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">E-Mail-Adresse</label>
            <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
          </div>

<div class="mb-3 pass-field">
  <label for="password" class="form-label">Passwort</label>
  <input type="password" class="form-control" id="password" name="password" required>
  <ul class="requirement-list mb-3">
    <li data-requirement="minlength">Mindestens 8 Zeichen</li>
    <li data-requirement="maxlength">Maximal 128 Zeichen</li>
    <li data-requirement="number">Mindestens eine Zahl</li>
    <li data-requirement="lowercase">Kleinbuchstabe</li>
    <li data-requirement="uppercase">Großbuchstabe</li>
    <li data-requirement="special">Sonderzeichen</li>
  </ul>
</div>

          <div class="mb-3 pass-field">
            <label for="password_confirm" class="form-label">Passwort bestätigen</label>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
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
<?php }
}
