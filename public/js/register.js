document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    const registerSubmitBtn = registerForm?.querySelector('button[type="submit"]');
    const usernameInputRegister = registerForm?.querySelector("#username");
    const emailInputRegister = registerForm?.querySelector("#email");
    const passwordInput = registerForm?.querySelector("#password");
    const passwordConfirmInput = registerForm?.querySelector("#password_confirm");
    const requirementList = registerForm?.querySelector(".requirement-list");
    const globalAlert = document.getElementById('globalAlert');

    if (!registerForm || !registerSubmitBtn || !globalAlert || !passwordInput || !passwordConfirmInput || !requirementList) {
        console.error('Elemente für Registrierung fehlen!');
        return;
    }

    // 🛠️ Requirement List Handling
    passwordInput.addEventListener('focus', () => {
        requirementList.style.display = 'block';
        setTimeout(() => {
            requirementList.classList.add('visible');
        }, 10); // kleiner Delay für sanften Transition-Start
    });

    passwordInput.addEventListener('blur', () => {
        setTimeout(() => {
            if (document.activeElement !== passwordConfirmInput) {
                requirementList.classList.remove('visible');
                setTimeout(() => {
                    requirementList.style.display = 'none';
                }, 300); // Zeit passend zu deinem CSS-Transition
            }
        }, 100); // kleinen Delay für Eye-Icon-Click
    });

    passwordConfirmInput.addEventListener('focus', () => {
        requirementList.classList.remove('visible');
        setTimeout(() => {
            requirementList.style.display = 'none';
        }, 300);
    });

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
            if (loginBtn) {
                loginBtn.addEventListener('click', async () => {
                    loginBtn.innerHTML = `
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Bitte warten...
                    `;
                    loginBtn.disabled = true;
                    await new Promise(resolve => setTimeout(resolve, 3000)); // Spinner 3 Sekunden
                    const loginModalElement = document.getElementById('loginModal');
                    if (loginModalElement) {
                        const loginModal = bootstrap.Modal.getOrCreateInstance(loginModalElement);
                        loginModal.show();
                    }
                    globalAlert.innerHTML = '';
                });
            }
        }
    };

    registerSubmitBtn.addEventListener('click', async (event) => {
        event.preventDefault();

        usernameInputRegister.setCustomValidity('');
        emailInputRegister.setCustomValidity('');
        passwordInput.setCustomValidity('');
        passwordConfirmInput.setCustomValidity('');

        const requirements = {
            minlength: /.{8,}/,
            number: /[0-9]/,
            lowercase: /[a-z]/,
            special: /[^A-Za-z0-9]/,
            uppercase: /[A-Z]/,
        };

        let isClientValid = true;
        const currentPassword = passwordInput.value;
        const passwordErrors = [];

        for (const [rule, regex] of Object.entries(requirements)) {
            if (!regex.test(currentPassword)) {
                passwordErrors.push(`Passwort verletzt Anforderung: ${rule}`);
            }
        }
        if (currentPassword.length > 128) {
            passwordErrors.push("Passwort darf maximal 128 Zeichen haben.");
        }

        if (passwordErrors.length > 0) {
            passwordInput.setCustomValidity(passwordErrors.join('\n'));
            isClientValid = false;
        }

        if (currentPassword !== passwordConfirmInput.value) {
            passwordConfirmInput.setCustomValidity("Passwörter stimmen nicht überein.");
            isClientValid = false;
        }

        if (!registerForm.checkValidity() || !isClientValid) {
            registerForm.reportValidity();
            return;
        }

        const formData = new FormData(registerForm);

        try {
            const response = await fetch('register.php', {
                method: 'POST',
                body: formData,
            });

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Unerwartete Antwort:', text);
                showAlert('Server hat eine ungültige Antwort geschickt.');
                return;
            }

            const data = await response.json();

            if (data.success) {
                showAlert('Registrierung erfolgreich! Willkommen bei StudyHub.', 'success', true);

                registerForm.reset();
                requirementList.classList.remove('visible');
                requirementList.style.display = 'none';

                // Reset icons in der Liste
                requirementList.querySelectorAll('li').forEach(li => {
                    li.classList.remove('valid');
                    const icon = li.querySelector('i');
                    const span = li.querySelector('span');
                    if (icon) icon.className = 'fa-solid fa-circle';
                    if (span) span.style.color = '';
                });

                const registerModalElement = document.getElementById('registerModal');
                if (registerModalElement) {
                    const registerModal = bootstrap.Modal.getOrCreateInstance(registerModalElement);
                    registerModal.hide();
                }
            } else if (data.errors) {
                let errorMessages = '';
                for (const [key, value] of Object.entries(data.errors)) {
                    if (Array.isArray(value)) {
                        errorMessages += value.map(e => `<div>${e}</div>`).join('');
                    } else {
                        errorMessages += `<div>${value}</div>`;
                    }
                }
                showAlert(errorMessages, 'danger');

                if (data.errors.username) usernameInputRegister.setCustomValidity(data.errors.username);
                if (data.errors.email) emailInputRegister.setCustomValidity(data.errors.email);
                if (data.errors.password) passwordInput.setCustomValidity(data.errors.password.join('\n'));
                if (data.errors.password_confirm) passwordConfirmInput.setCustomValidity(data.errors.password_confirm);

                registerForm.reportValidity();
            } else if (data.message) {
                showAlert('Registrierung fehlgeschlagen: ' + data.message, 'danger');
            } else {
                showAlert('Unbekannter Fehler bei der Registrierung.', 'danger');
            }

        } catch (err) {
            console.error('Registrierungsfehler:', err);
            showAlert('Verbindungsfehler. Bitte später erneut versuchen.', 'danger');
        }
    });
});
