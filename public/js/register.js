// register.js
document.addEventListener('DOMContentLoaded', () => {
  const registerModal        = document.getElementById('registerModal');
  const registerForm         = document.getElementById('registerForm');
  const registerSubmitBtn    = registerForm.querySelector('button[type="submit"]');
  const passwordInput        = registerForm.querySelector('#password');
  const passwordConfirmInput = registerForm.querySelector('#password_confirm');
  const eyeIcons             = registerForm.querySelectorAll('.pass-field i.eye-icon');
  const requirementList      = registerForm.querySelector('.requirement-list');
  const globalAlertContainer = document.getElementById('AlertContainer');
  const formAlertContainer   = document.getElementById('registerAlert');
  const spinner              = document.getElementById('register-spinner');

  if (!registerForm || !registerSubmitBtn || !passwordInput || !passwordConfirmInput
      || !requirementList || !spinner) {
    console.error('Einige Elemente fehlen im Formular!');
    return;
  }

  const requirements = {
    minlength: { regex: /.{8,}/,       message: 'Mindestens 8 Zeichen' },
    maxlength: { regex: /^.{0,128}$/,  message: 'Maximal 128 Zeichen' },
    number:    { regex: /[0-9]/,       message: 'Mindestens eine Zahl' },
    lowercase: { regex: /[a-z]/,       message: 'Mindestens einen Kleinbuchstaben' },
    uppercase: { regex: /[A-Z]/,       message: 'Mindestens einen Großbuchstaben' },
    special:   { regex: /[^A-Za-z0-9]/,message: 'Mindestens ein Sonderzeichen' },
  };

  // Passwort anzeigen/verstecken
  eyeIcons.forEach(icon => {
    icon.addEventListener('click', () => {
      const input = icon.previousElementSibling;
      if ([passwordInput, passwordConfirmInput].includes(input)) {
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.className = `bx ${input.type === 'password' ? 'bxs-lock-alt' : 'bxs-show'} eye-icon`;
      }
    });
  });

  passwordInput.addEventListener('focus', () => requirementList.classList.add('visible'));
  passwordInput.addEventListener('blur', () => requirementList.classList.remove('visible'));

  passwordInput.addEventListener('input', () => {
    const val = passwordInput.value;
    requirementList.querySelectorAll('li').forEach(li => {
      const ok = requirements[li.dataset.requirement].regex.test(val);
      li.classList.toggle('valid', ok);
      li.classList.toggle('invalid', !ok);
    });
  });

  function resetForm() {
    registerForm.reset();
    requirementList.classList.remove('visible');
    requirementList.querySelectorAll('li').forEach(li => li.classList.remove('valid', 'invalid'));
  }

  function getRegistrationErrorMessage(err) {
    if (err.status === 400 && typeof err.data === 'object' && err.data.errors) {
        const errorMessages = Object.values(err.data.errors);
        if (errorMessages.length > 0) {
            return errorMessages.join('<br>');
        }
    }
    const code = typeof err.data === 'object' ? err.data?.error?.code : null;
    if (err.status === 409 && code === 'USERNAME_EXISTS') return 'Username bereits vergeben';
    if (err.status === 409 && code === 'EMAIL_EXISTS')    return 'E-Mail bereits vergeben';
    if (err.status === 400 && code === 'PASSWORD_WEAK')   return 'Passwort erfüllt nicht die Bedingungen';

    if (err.status === 409 && typeof err.data === 'string') {
      if (err.data.includes('username')) return 'Username bereits vergeben';
      if (err.data.includes('E-Mail'))   return 'E-Mail bereits vergeben';
    }
    return 'Verbindungsfehler. Bitte später erneut versuchen.';
  }

  function showAlert(htmlMessage, type = 'danger', autoCloseMs = 5000, container = formAlertContainer) {
    if (!container) container = globalAlertContainer;
    if (!container) { alert(htmlMessage); return; }

    if (container === globalAlertContainer) {
      container.innerHTML = `
        <div class="container mt-3">
          <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${htmlMessage}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
          </div>
        </div>`;
      const alertEl = container.querySelector('.alert');
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
      if (autoCloseMs > 0) setTimeout(() => bsAlert.close(), autoCloseMs);
    } else {
      container.innerHTML = htmlMessage;
      container.className = `alert alert-${type}`;
      container.classList.remove('d-none');
      if (autoCloseMs > 0) setTimeout(() => container.classList.add('d-none'), autoCloseMs);
    }
  }

  async function register(formData) {
    try {
      const res = await fetch(registerForm.action, { method: 'POST', body: formData });
      let data = null;
      try { data = await res.clone().json(); } catch { data = await res.text(); }

      if (!res.ok) throw { status: res.status, data };
      return data;
    } catch (err) {
      const msg = getRegistrationErrorMessage(err);
      showAlert(msg, 'danger');
      console.log('Registration error', err);
      return null;
    }
  }

  registerSubmitBtn.addEventListener('click', async event => {
    event.preventDefault();

    passwordInput.setCustomValidity('');
    passwordConfirmInput.setCustomValidity('');

    if (!registerForm.checkValidity()) {
      registerForm.reportValidity();
      return;
    }

    const pwd = passwordInput.value;
    const confirmPwd = passwordConfirmInput.value;

    const pwErrors = [];
    Object.values(requirements).forEach(({ regex, message }) => {
      if (!regex.test(pwd)) pwErrors.push(message);
    });

    if (pwErrors.length) {
      passwordInput.setCustomValidity(pwErrors.join('\n'));
      registerForm.reportValidity();
      return;
    }

    if (pwd !== confirmPwd) {
      passwordConfirmInput.setCustomValidity('Passwörter stimmen nicht überein.');
      registerForm.reportValidity();
      return;
    }

    registerSubmitBtn.disabled = true;
    spinner.classList.remove('d-none');

    const formData = new FormData(registerForm);
    const data = await register(formData);
    if (data && data.success) {
      bootstrap.Modal.getInstance(registerModal)?.hide();
      setTimeout(() => {
        resetForm();
        showAlert(
          'Registrierung erfolgreich! Bitte bestätige deine E-Mail-Adresse.',
          'success',
          7000,
          globalAlertContainer
        );
      }, 300);
    }
    registerSubmitBtn.disabled = false;
    spinner.classList.add('d-none');
  });
});
