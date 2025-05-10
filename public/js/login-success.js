document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const loginSuccess = urlParams.get('login');

    if (loginSuccess === 'success') {
        //const alertContainer = document.getElementById('globalAlert');

        // KEIN Modal-Öffnen mehr!

        // Nach 2 Sekunden Weiterleitung
        setTimeout(function() {
            window.location.href = baseUrl + '/dashboard.php';
        }, 2000);
    }
});
