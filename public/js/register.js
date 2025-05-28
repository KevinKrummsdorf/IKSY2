// register.js
document.addEventListener('DOMContentLoaded', () => {
  const registerModal        = document.getElementById('registerModal');
  const registerForm         = document.getElementById('registerForm');
  const registerSubmitBtn    = registerForm.querySelector('button[type="submit"]');
  const passwordInput        = registerForm.querySelector('#password');
  const passwordConfirmInput = registerForm.querySelector('#password_confirm');
  const eyeIcons             = registerForm.querySelectorAll('.pass-field i.eye-icon');
  const requirementList      = registerForm.querySelector('.requirement-list');
  const alertContainer       = document.getElementById('AlertContainer');
  const tokenField           = document.getElementById('register-recaptcha-token');
  const spinner              = document.getElementById('register-spinner');
  const siteKey              = window.recaptchaSiteKey;

  if (!registerForm || !registerSubmitBtn || !passwordInput || !passwordConfirmInput
      || !requirementList || !alertContainer || !tokenField || !spinner) {
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

  eyeIcons.forEach(icon => {
    icon.addEventListener('click', () => {
      const input = icon.previousElementSibling;
      if ([passwordInput, passwordConfirmInput].includes(input)) {
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.className = `bx ${input.type === 'password' ? 'bxs-lock-alt' : 'bxs-show'} eye-icon`;
      }
    });
  });

  passwordInput.addEventListener('focus', () => {
    requirementList.classList.add('visible');
  });
  passwordInput.addEventListener('blur', () => {
    requirementList.classList.remove('visible');
  });

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
    requirementList.querySelectorAll('li').forEach(li => {
      li.classList.remove('valid', 'invalid');
    });
  }

  function showAlert(htmlMessage, type = 'danger', autoCloseMs = 5000) {
    alertContainer.innerHTML = `
      <div class="container mt-3">
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
          ${htmlMessage}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
        </div>
      </div>`;
    const alertEl = alertContainer.querySelector('.alert');
    const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
    if (autoCloseMs > 0) {
      setTimeout(() => bsAlert.close(), autoCloseMs);
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

    grecaptcha.ready(() => {
      grecaptcha.execute(siteKey, { action: 'register' }).then(async token => {
        tokenField.value = token;
        try {
          const response = await fetch('register.php', {
            method: 'POST',
            body: new FormData(registerForm)
          });

          if (!response.ok) throw new Error(`Server-Error: ${response.status}`);
          const ct = response.headers.get('content-type') || '';
          if (!ct.includes('application/json')) {
            showAlert('Ungültige Server-Antwort.', 'danger');
            return;
          }

          const data = await response.json();

          if (data.success) {
            bootstrap.Modal.getInstance(registerModal)?.hide();
            setTimeout(() => {
              resetForm();
              showAlert(
                'Registrierung erfolgreich! Bitte bestätige deine E-Mail-Adresse.',
                'success',
                7000
              );
            }, 300);
          } else {
            let msgs = '';
            if (data.errors) {
              Object.values(data.errors).forEach(v => {
                if (Array.isArray(v)) v.forEach(m => msgs += `<div>${m}</div>`);
                else msgs += `<div>${v}</div>`;
              });
            } else {
              msgs = data.message || 'Unbekannter Fehler.';
            }
            showAlert(msgs, 'danger');
          }
        } catch (err) {
          console.error(err);
          showAlert('Verbindungsfehler. Bitte später erneut versuchen.', 'danger');
        } finally {
          registerSubmitBtn.disabled = false;
          spinner.classList.add('d-none');
        }
      });
    });
  });
});
