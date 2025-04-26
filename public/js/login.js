document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.querySelector("#loginModal form");
    if (!loginForm) return;
  
    const loginBtn = loginForm.querySelector("button[type='submit']");
    const usernameInput = loginForm.querySelector("input[type='text']");
    const passwordInput = loginForm.querySelector("input[type='password']");
    const loginModal = document.getElementById("loginModal");
  
    loginBtn.addEventListener("click", async (event) => {
      event.preventDefault();
      usernameInput.setCustomValidity("");
      passwordInput.setCustomValidity("");
  
      if (!loginForm.checkValidity()) {
        loginForm.reportValidity();
        return;
      }
  
      try {
        const formData = new FormData(loginForm);
        const response = await fetch('login.php', {
          method: 'POST',
          body: formData
        });
  
        const data = await response.json();
  
        if (data.success) {
          // ✅ 1. Modal schließen
          const modalInstance = bootstrap.Modal.getInstance(loginModal);
          modalInstance.hide();
  
          // ✅ 2. Begrüßung anzeigen oder zur Startseite weiterleiten
          // Variante A: Begrüßung mit Alert
          alert("Willkommen zurück!");
  
          // Variante B: Weiterleitung (z. B. ins Dashboard)
          // window.location.href = "dashboard.html";
        } else {
          if (data.errors?.username) {
            usernameInput.setCustomValidity(data.errors.username);
          }
          if (data.errors?.password) {
            passwordInput.setCustomValidity(data.errors.password);
          }
          loginForm.reportValidity();
        }
      } catch (err) {
        alert("Fehler beim Login. Bitte versuche es später erneut.");
        console.error(err);
      }
    });
  });
  