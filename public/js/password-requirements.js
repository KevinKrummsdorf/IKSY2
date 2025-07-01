// password-requirements.js
// Handles password requirement checks for forms with data-pw-validate

document.addEventListener('DOMContentLoaded', () => {
  const requirements = {
    minlength: { regex: /.{8,}/,       message: 'Mindestens 8 Zeichen' },
    maxlength: { regex: /^.{0,128}$/,  message: 'Maximal 128 Zeichen' },
    number:    { regex: /[0-9]/,       message: 'Mindestens eine Zahl' },
    lowercase: { regex: /[a-z]/,       message: 'Mindestens einen Kleinbuchstaben' },
    uppercase: { regex: /[A-Z]/,       message: 'Mindestens einen Großbuchstaben' },
    special:   { regex: /[^A-Za-z0-9]/,message: 'Mindestens ein Sonderzeichen' },
  };

  document.querySelectorAll('form[data-pw-validate]').forEach(form => {
    const pwdInput = form.querySelector('.pw-new');
    const confirmInput = form.querySelector('.pw-confirm');
    const reqList = form.querySelector('.requirement-list');
    if (!pwdInput || !confirmInput || !reqList) return;

    pwdInput.addEventListener('focus', () => reqList.classList.add('visible'));
    pwdInput.addEventListener('blur', () => reqList.classList.remove('visible'));
    pwdInput.addEventListener('input', () => {
      const val = pwdInput.value;
      reqList.querySelectorAll('li').forEach(li => {
        const ok = requirements[li.dataset.requirement].regex.test(val);
        li.classList.toggle('valid', ok);
        li.classList.toggle('invalid', !ok);
      });
    });

    form.addEventListener('submit', e => {
      pwdInput.setCustomValidity('');
      confirmInput.setCustomValidity('');

      const pwErrors = [];
      Object.values(requirements).forEach(({ regex, message }) => {
        if (!regex.test(pwdInput.value)) pwErrors.push(message);
      });
      if (pwErrors.length) {
        pwdInput.setCustomValidity(pwErrors.join('\n'));
      }
      if (pwdInput.value !== confirmInput.value) {
        confirmInput.setCustomValidity('Passwörter stimmen nicht überein.');
      }
      if (!form.checkValidity()) {
        e.preventDefault();
        form.reportValidity();
      }
    });
  });
});
