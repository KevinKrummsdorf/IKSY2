// register.js

// Selektoren für das Registrierungsformular und seine Elemente (mit IDs)
const registerForm = document.getElementById('registerForm');
const registerSubmitBtn = registerForm?.querySelector('button[type="submit"]'); // Sicherstellen, dass registerForm existiert
const usernameInputRegister = registerForm?.querySelector("#username");
const emailInputRegister = registerForm?.querySelector("#email");
const passwordInput = registerForm?.querySelector("#password");
const passwordConfirmInput = registerForm?.querySelector("#password_confirm");
const eyeIcons = registerForm?.querySelectorAll(".input-box.pass-field i.eye-icon");
const requirementList = registerForm?.querySelector(".requirement-list");

// Passwort-Anforderungen (ohne maxlength)
const requirements = {
    minlength: { regex: /.{8,}/, message: 'muss mindestens 8 Zeichen lang sein' },
    number:    { regex: /[0-9]/, message: 'muss mindestens eine Zahl enthalten' },
    lowercase: { regex: /[a-z]/, message: 'muss mindestens einen Kleinbuchstaben enthalten' },
    special:   { regex: /[^A-Za-z0-9]/, message: 'muss mindestens ein Sonderzeichen enthalten' },
    uppercase: { regex: /[A-Z]/, message: 'muss mindestens einen Großbuchstaben enthalten' },
    // maxlength wird separat behandelt
};

if (passwordInput && requirementList && passwordConfirmInput) {
    // --- Event Listener für Fokus auf das erste Passwortfeld ---
    passwordInput.addEventListener("focus", () => {
        requirementList.style.display = "block"; // Anforderungsliste anzeigen
    });

    // --- Event Listener für Blur auf das erste Passwortfeld ---
    passwordInput.addEventListener("blur", () => {
        // Verzögerung, um sicherzustellen, dass Eye-Icon Klick funktioniert
        setTimeout(() => {
            // Versteckt die Liste nur, wenn das Passwort-Bestätigungsfeld oder ein Eye-Icon nicht fokussiert sind
            if (document.activeElement !== passwordConfirmInput && !Array.from(eyeIcons || []).includes(document.activeElement)) {
                requirementList.style.display = "none"; // Anforderungsliste ausblenden
            }
        }, 150);
    });

    // --- Event Listener für Fokus auf das "Passwort bestätigen"-Feld ---
    passwordConfirmInput.addEventListener("focus", () => {
        requirementList.style.display = "none"; // Versteckt die Liste, wenn das Bestätigungsfeld fokussiert ist
    });

    // --- Live-Validierung der Liste bei Eingabe im ersten Passwortfeld ---
    passwordInput.addEventListener("keyup", (e) => {
        const currentPassword = e.target.value;

        // Checkliste aktualisieren
        for (const requirementName in requirements) {
            const { regex } = requirements[requirementName];
            const listItem = requirementList.querySelector(`li[data-requirement="${requirementName}"]`);
            if (listItem) {
                const icon = listItem.querySelector("i");
                const textSpan = listItem.querySelector("span");
                const isValid = regex.test(currentPassword);
                listItem.classList.toggle("valid", isValid);
                icon.className = `fa-solid ${isValid ? 'fa-check' : 'fa-circle'}`;
                textSpan.style.color = isValid ? "green" : ""; // Rot nur bei Submit-Fehler
            }
        }

        // Maximallänge separat prüfen
        const maxlengthListItem = requirementList.querySelector('li[data-requirement="maxlength"]');
        if (maxlengthListItem) {
            const icon = maxlengthListItem.querySelector("i");
            const textSpan = maxlengthListItem.querySelector("span");
            const isNotTooLong = currentPassword.length <= 128;
            maxlengthListItem.classList.toggle("valid", isNotTooLong);
            icon.className = `fa-solid ${isNotTooLong ? 'fa-check' : 'fa-circle'}`;
            textSpan.style.color = isNotTooLong ? "green" : ""; // Rot nur bei Submit-Fehler

        }

        // Lösche benutzerdefinierte Validierungsfehler während des Tippens
        passwordInput.setCustomValidity("");
        passwordConfirmInput.setCustomValidity("");
    });
}

// --- Event Listener für Eye-Icons (Passwort Sichtbarkeit) ---
if (eyeIcons) {
    eyeIcons.forEach((icon) => {
        icon.addEventListener("click", () => {
            const inputField = icon.previousElementSibling;
            if (inputField && (inputField === passwordInput || inputField === passwordConfirmInput)) {
                inputField.type = inputField.type === "password" ? "text" : "password";
                icon.className = `bx ${inputField.type === "password" ? 'bxs-lock-alt' : 'bxs-show'} eye-icon`;
            }
        });
    });
}

// --- Event Listener für Formular-Submit (Registrierung) ---
if (registerSubmitBtn && registerForm && passwordInput && passwordConfirmInput && usernameInputRegister && emailInputRegister) {
    registerSubmitBtn.addEventListener('click', async (event) => {
        event.preventDefault(); // Standard-Submit verhindern

        // 1. Alte benutzerdefinierte Fehler löschen (wichtig für Neuvalidierung)
        usernameInputRegister.setCustomValidity("");
        emailInputRegister.setCustomValidity("");
        passwordInput.setCustomValidity("");
        passwordConfirmInput.setCustomValidity("");

        let isClientValid = true; // Flag für Client-seitige Validität
        let formShouldSubmit = false; // Wird true, wenn Client-Validierung OK ist

        // 2. HTML5 Standard-Validierung prüfen
        if (!registerForm.checkValidity()) {
            isClientValid = false;
            // Browser zeigt native Fehler an (z.B. für required, type="email")
            // reportValidity() wird später global aufgerufen
        }

        // 3. Benutzerdefinierte Passwort-Validierung (Client-seitig)
        const currentPassword = passwordInput.value;
        const passwordErrorMessages = [];

        for (const requirementName in requirements) {
            if (!requirements[requirementName].regex.test(currentPassword)) {
                passwordErrorMessages.push(`Passwort ${requirements[requirementName].message}.`);
            }
        }
        // Maximallänge separat prüfen
        if (currentPassword.length > 128) {
            passwordErrorMessages.push("Passwort darf maximal 128 Zeichen lang sein.");
        }

        if (passwordErrorMessages.length > 0) {
            passwordInput.setCustomValidity(passwordErrorMessages.join('\n'));
            isClientValid = false;
        }

        // 4. Passwort-Übereinstimmung prüfen (Client-seitig)
        if (currentPassword !== passwordConfirmInput.value) {
            passwordConfirmInput.setCustomValidity("Die Passwörter müssen übereinstimmen.");
            isClientValid = false;
        }

        // 5. Entscheidung: AJAX senden oder Client-Fehler anzeigen?
        if (!isClientValid) {
            registerForm.reportValidity(); // Zeige ALLE gesetzten Client-Fehler an
            return; // Stoppt hier, kein AJAX-Call
        }

        // --- Ab hier: Client-seitige Validierung war erfolgreich ---
        formShouldSubmit = true;

        if (formShouldSubmit) {
            const formData = new FormData(registerForm); // Formulardaten sammeln

            try {
                console.log("Sende Daten an den Server...");
                const response = await fetch('register.php', { // Sicherstellen, dass der Pfad stimmt
                    method: 'POST',
                    body: formData,
                });

                console.log("Antwort vom Server erhalten:", response.status);

                const contentType = response.headers.get("content-type");
                let data = null;

                if (contentType && contentType.includes("application/json")) {
                    data = await response.json();
                    console.log("Daten vom Server:", data);
                } else {
                    // Wenn keine JSON-Antwort kommt (z.B. bei PHP-Fehler vor json_encode)
                    const textResponse = await response.text();
                    console.error("Server hat keine gültige JSON-Antwort gesendet. Antwort:", textResponse);
                    alert("Ein Fehler ist aufgetreten. Die Serverantwort war unerwartet.");
                    return;
                }

                // Server-Antwort verarbeiten
                if (data.success) {
                    alert('Registrierung erfolgreich!');
                    registerForm.reset(); // Formular zurücksetzen
                    requirementList.style.display = 'none'; // Anforderungsliste ausblenden
                    // Ggf. Weiterleitung: window.location.href = 'login.html';
                } else {
                    // Fehler vom Server anzeigen
                    let serverErrorsFound = false;
                    if (data.errors) {
                        if (data.errors.username) {
                            usernameInputRegister.setCustomValidity(data.errors.username);
                            serverErrorsFound = true;
                        }
                        if (data.errors.email) {
                            emailInputRegister.setCustomValidity(data.errors.email);
                            serverErrorsFound = true;
                        }
                        if (data.errors.password) { // Ist ein Array von Fehlern
                            passwordInput.setCustomValidity(data.errors.password.join('\n'));
                            serverErrorsFound = true;
                        }
                        if (data.errors.password_confirm) {
                            passwordConfirmInput.setCustomValidity(data.errors.password_confirm);
                            serverErrorsFound = true;
                        }
                    }

                    if (serverErrorsFound) {
                        registerForm.reportValidity(); // Zeige die vom Server gemeldeten Fehler an
                    } else if (data.message) {
                        // Allgemeiner Fehler vom Server (z.B. DB-Fehler)
                        alert('Registrierung fehlgeschlagen: ' + data.message);
                    } else {
                        // Unbekannter Fehler ohne spezifische Meldung
                        alert('Registrierung fehlgeschlagen. Unbekannter Fehler.');
                    }
                }

            } catch (error) {
                console.error('Fehler bei der AJAX-Anfrage:', error);
                alert('Ein Netzwerkfehler oder ein anderes Problem ist aufgetreten. Bitte versuchen Sie es später erneut.');
            }
        }
    });
} else {
    console.error("Registrierungsformular oder wichtige Elemente davon wurden nicht gefunden.");
}
