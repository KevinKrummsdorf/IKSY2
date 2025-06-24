// login.js

document.addEventListener("DOMContentLoaded", () => {
    // ---------------------------------------
    // 1) Handle "login=success" alert & redirect
    // ---------------------------------------
    const urlParams = new URLSearchParams(window.location.search);
    const loginSuccess = urlParams.get('login');

    if (loginSuccess === 'success') {
        const alertContainer = document.getElementById('globalAlert');

        // KEIN Modal-Öffnen mehr!

        // Nach 2 Sekunden Weiterleitung
        setTimeout(function() {
            const target = usePrettyUrls ? '/dashboard' : '/dashboard.php';
            window.location.href = baseUrl + target;
        }, 2000);
    }
  
    // ---------------------------------------
    // 2) reCAPTCHA v3 protection on login form
    // ---------------------------------------
    const form     = document.querySelector("#loginModal form");
    const tokenEl  = document.getElementById("login-recaptcha-token");
    const btn      = form?.querySelector("button[type='submit']");
    const spinner  = document.getElementById("login-spinner");
    const siteKey  = window.recaptchaSiteKey;
  
    if (form && btn && tokenEl && spinner && siteKey) {
      form.addEventListener("submit", event => {
        event.preventDefault();
  
        // HTML5 validation
        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }
  
        // Show spinner and disable button
        btn.disabled = true;
        spinner.classList.remove("d-none");
  
        // Fetch reCAPTCHA token
        grecaptcha.ready(() => {
          grecaptcha.execute(siteKey, { action: "login" })
            .then(token => {
              console.log("Login reCAPTCHA Token:", token);
              tokenEl.value = token;
              // Submit the form normally
              form.submit();
            })
            .catch(err => {
              console.error("reCAPTCHA error:", err);
              // Allow retry
              btn.disabled = false;
              spinner.classList.add("d-none");
            });
        });
      });
    }
  });
  