{extends file='layout.tpl'}

{block name="content"}
<div class="container mt-5">
    <h2 class="mb-3">Zwei-Faktor-Authentifizierung einrichten</h2>

    {if isset($message)}
        <div class="alert alert-danger">{$message}</div>
    {/if}

    <p>Scanne den folgenden QR-Code mit einer Authenticator-App wie z. B. <strong>Google Authenticator</strong> oder <strong>Authy</strong>:</p>

    <div class="text-center my-4">
        <img src="{$qrCodeUrl}" alt="QR-Code" class="img-fluid" style="max-width: 200px;">
    </div>

    <form method="post" action="verify_2fa.php" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="code" class="form-label">Einmal-Code aus der App eingeben:</label>
            <input type="text" name="code" id="code" class="form-control" required pattern="^\d{6}$" autocomplete="off">
            <div class="invalid-feedback">Bitte einen gültigen 6-stelligen Code eingeben.</div>
        </div>

        <button type="submit" class="btn btn-primary">2FA aktivieren</button>
    </form>
</div>

<script>
    // Bootstrap Client-Side Validation
    (() => {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>
{/block}
