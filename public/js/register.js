document.addEventListener('DOMContentLoaded', () => {
    // --- Selektoren ---
    const registerForm           = document.getElementById('registerForm');
    const registerSubmitBtn      = registerForm?.querySelector('button[type="submit"]');
    const usernameInputRegister  = registerForm?.querySelector('#username');
    const emailInputRegister     = registerForm?.querySelector('#email');
    const passwordInput          = registerForm?.querySelector('#password');
    const passwordConfirmInput   = registerForm?.querySelector('#password_confirm');
    const eyeIcons               = registerForm?.querySelectorAll('.input-box.pass-field i.eye-icon');
    const requirementList        = registerForm?.querySelector('.requirement-list');
    const globalAlert            = document.getElementById('globalAlert');

    if (!registerForm || !registerSubmitBtn || !globalAlert || !passwordInput || !passwordConfirmInput || !requirementList) {
        console.error('Elemente für Registrierung fehlen!');
        return;
    }

    // --- Passwort-Anforderungen ---
    const requirements = {
        minlength: { regex: /.{8,}/,            message: 'muss mindestens 8 Zeichen lang sein' },
        maxlength: { regex: /^.{0,128}$/,       message: 'darf maximal 128 Zeichen lang sein' },
        number:    { regex: /[0-9]/,            message: 'muss mindestens eine Zahl enthalten' },
        lowercase: { regex: /[a-z]/,            message: 'muss mindestens einen Kleinbuchstaben enthalten' },
        special:   { regex: /[^A-Za-z0-9]/,     message: 'muss mindestens ein Sonderzeichen enthalten' },
        uppercase: { regex: /[A-Z]/,            message: 'muss mindestens einen Großbuchstaben enthalten' },
    };

    // --- Show/Hide Requirement-Liste ---
    passwordInput.addEventListener('focus', () => {
        requirementList.style.display = 'block';
        setTimeout(() => requirementList.classList.add('visible'), 10);
    });

    passwordInput.addEventListener('blur', () => {
        setTimeout(() => {
            if (document.activeElement !== passwordConfirmInput) {
                requirementList.classList.remove('visible');
                setTimeout(() => requirementList.style.display = 'none', 300);
            }
        }, 100);
    });

    passwordConfirmInput.addEventListener('focus', () => {
        requirementList.classList.remove('visible');
        setTimeout(() => requirementList.style.display = 'none', 300);
    });

    // --- Live-Validierung bei Eingabe ---
    passwordInput.addEventListener('keyup', (e) => {
        const current = e.target.value;

        // Anforderungen prüfen
        for (const [name, { regex }] of Object.entries(requirements)) {
            const li = requirementList.querySelector(`li[data-requirement="${name}"]`);
            if (!li) continue;
            const icon = li.querySelector('i');
            const span = li.querySelector('span');
            const passed = regex.test(current);

            li.classList.toggle('valid', passed);
            if (icon) icon.className = `fa-solid ${passed ? 'fa-check' : 'fa-circle'}`;
            if (span) span.style.color = passed ? 'green' : '';
        }

        // Alte Validierungsfehler zurücksetzen
        passwordInput.setCustomValidity('');
        passwordConfirmInput.setCustomValidity('');
    });

    // --- Eye-Icon Klick (Passwort ein-/ausblenden) ---
    eyeIcons?.forEach(icon => {
        icon.addEventListener('click', () => {
            const inputField = icon.previousElementSibling;
            if (inputField === passwordInput || inputField === passwordConfirmInput) {
                inputField.type = inputField.type === 'password' ? 'text' : 'password';
                icon.className = `bx ${inputField.type === 'password' ? 'bxs-lock-alt' : 'bxs-show'} eye-icon`;
            }
        });
    });

    // --- Alert-Funktion ---
    const showAlert = (message, type = 'danger', includeLoginButton = false) => {
        let buttonHtml = '';
        if (includeLoginButton) {
            buttonHtml = `
                <button id="loginAfterRegisterBtn" class="btn btn-primary btn-sm mt-2">
                    Jetzt einloggen
                </button>
            `;
        }
        globalAlert.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show mt-3" role="alert">
                ${message}
                ${buttonHtml}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
            </div>
        `;
        if (includeLoginButton) {
            const loginBtn = document.getElementById('loginAfterRegisterBtn');
            loginBtn?.addEventListener('click', async () => {
                loginBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Bitte warten...
                `;
                loginBtn.disabled = true;
                await new Promise(r => setTimeout(r, 3000));
                const loginModalElement = document.getElementById('loginModal');
                if (loginModalElement) {
                    bootstrap.Modal.getOrCreateInstance(loginModalElement).show();
                }
                globalAlert.innerHTML = '';
            });
        }
    };

    // --- Submit-Handler ---
    registerSubmitBtn.addEventListener('click', async (event) => {
        event.preventDefault();

        // Alte Custom-Validity entfernen
        usernameInputRegister.setCustomValidity('');
        emailInputRegister.setCustomValidity('');
        passwordInput.setCustomValidity('');
        passwordConfirmInput.setCustomValidity('');

        // HTML5-Standard-Validierung
        if (!registerForm.checkValidity()) {
            registerForm.reportValidity();
            return;
        }

        // Zusätzliche Passwort-Validierung
        const pwd = passwordInput.value;
        const errors = [];
        for (const { regex, message } of Object.values(requirements)) {
            if (!regex.test(pwd)) {
                errors.push(`Passwort ${message}.`);
            }
        }
        if (errors.length) {
            passwordInput.setCustomValidity(errors.join('\n'));
            registerForm.reportValidity();
            return;
        }
        if (pwd !== passwordConfirmInput.value) {
            passwordConfirmInput.setCustomValidity('Passwörter stimmen nicht überein.');
            registerForm.reportValidity();
            return;
        }

        // AJAX-Anfrage
        const formData = new FormData(registerForm);
        try {
            const response = await fetch('register.php', {
                method: 'POST',
                body: formData,
            });
            const ct = response.headers.get('content-type') || '';
            if (!ct.includes('application/json')) {
                const text = await response.text();
                console.error('Unerwartete Antwort:', text);
                showAlert('Server hat eine ungültige Antwort geschickt.');
                return;
            }
            const data = await response.json();
            if (data.success) {
                showAlert('Registrierung erfolgreich! Willkommen bei StudyHub. Bitte bestätige deine E-Mail-Adresse.', 'success'); 
                // nach 5 Sek automatisch schließen
                setTimeout(() => {
                    const alertEl = globalAlert.querySelector('.alert');
                    if (alertEl) {
                        bootstrap.Alert.getOrCreateInstance(alertEl).close();
                    }
                }, 5000);     
                registerForm.reset();
                requirementList.classList.remove('visible');
                requirementList.style.display = 'none';
                requirementList.querySelectorAll('li').forEach(li => {
                    li.classList.remove('valid');
                    const icon = li.querySelector('i');
                    const span = li.querySelector('span');
                    if (icon) icon.className = 'fa-solid fa-circle';
                    if (span) span.style.color = '';
                });
                const registerModal = document.getElementById('registerModal');
                registerModal && bootstrap.Modal.getOrCreateInstance(registerModal).hide();
            } else if (data.errors) {
                let msgs = '';
                for (const v of Object.values(data.errors)) {
                    msgs += Array.isArray(v) ? v.map(m => `<div>${m}</div>`).join('') : `<div>${v}</div>`;
                }
                showAlert(msgs, 'danger');
                // setCustomValidity nach Bedarf...
                registerForm.reportValidity();
            } else {
                showAlert(data.message ? 'Registrierung fehlgeschlagen: ' + data.message : 'Unbekannter Fehler.', 'danger');
            }
        } catch (err) {
            console.error('Registrierungsfehler:', err);
            showAlert('Verbindungsfehler. Bitte später erneut versuchen.', 'danger');
        }
    });
});
