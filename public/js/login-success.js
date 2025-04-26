document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const loginSuccess = urlParams.get('login');

    if (loginSuccess === 'success') {
        const alertContainer = document.getElementById('globalAlert');
        if (alertContainer) {
            alertContainer.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                  Willkommen zurück! Sie haben sich erfolgreich eingeloggt.
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
                </div>
            `;
        }

        // KEIN Modal-Öffnen mehr!

        // Nach 2 Sekunden Weiterleitung
        setTimeout(function() {
            window.location.href = baseUrl + '/dashboard.php';
        }, 2000);
    }
});
