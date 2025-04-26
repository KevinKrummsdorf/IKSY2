document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  if (!loginForm) {
      console.error('Login-Formular nicht gefunden!');
      return;
  }

  const loginBtn = document.getElementById("loginButton");
  const spinner = document.getElementById("loginSpinner");
  const loginAlert = document.getElementById("loginAlert");

  const showAlert = (message, type = 'danger') => {
      if (!loginAlert) return;
      loginAlert.innerHTML = `
          <div class="alert alert-${type} alert-dismissible fade show" role="alert">
              ${message}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
          </div>
      `;
  };

  const setLoading = (loading) => {
      if (loading) {
          spinner?.classList.remove('d-none');
          loginBtn.disabled = true;
      } else {
          spinner?.classList.add('d-none');
          loginBtn.disabled = false;
      }
  };

  loginForm.addEventListener("submit", async (event) => {
      event.preventDefault();

      if (!loginForm.checkValidity()) {
          loginForm.reportValidity();
          return;
      }

      setLoading(true);

      try {
          const formData = new FormData(loginForm);
          const response = await fetch('login.php', {
              method: 'POST',
              body: formData
          });

          if (!response.ok) {
              throw new Error("Serverfehler: " + response.status);
          }

          const data = await response.json();

          if (data.success) {
            showAlert("Willkommen zurück!", "success");
            console.log("Login erfolgreich, starte Weiterleitung in 1.5 Sekunden...");
        
            setTimeout(() => {
                const targetUrl = data.redirect || "dashboard.php";
        
                console.log("Starte Redirect auf: " + targetUrl);
        
                // Harte Weiterleitung
                window.location.assign(targetUrl);
            }, 1500);
        }
        
         else {
              showAlert(data.message || "Login fehlgeschlagen.");
          }
      } catch (err) {
          console.error(err);
          showAlert("Verbindungsfehler. Bitte später erneut versuchen.");
      } finally {
          setLoading(false);
      }
  });
});
