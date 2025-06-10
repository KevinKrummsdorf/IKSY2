{extends file="./layouts/layout.tpl"}

{block name="title"}Passwort zurücksetzen{/block}

{block name="content"}
<h1>Neues Passwort setzen</h1>

{if $success}
    <div class="alert alert-success">Passwort wurde geändert.</div>
    <a href="{$base_url}/index.php" class="btn btn-primary">Zur Startseite</a>
{/if}

{if $message}
    <div class="alert alert-danger">{$message}</div>
{/if}

{if !$success}
<form method="post" class="needs-validation" novalidate>
    {*
        Dieser Alert wird durch JavaScript sichtbar gemacht,
        wenn die Felder leer oder ungültig sind (Client-seitig).
    *}
    <div id="formAlert" class="alert alert-danger d-none">Bitte alle Felder ausfüllen.</div>

    <div class="mb-3">
        <label for="password" class="form-label">Neues Passwort</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <div class="mb-3">
        <label for="password_confirm" class="form-label">Passwort bestätigen</label>
        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
    </div>
    <button type="submit" class="btn btn-primary">Passwort speichern</button>
</form>
{/if}

<script>
(() => {
    'use strict';
    const form = document.querySelector('.needs-validation');
    const alertBox = document.getElementById('formAlert');

    form?.addEventListener('submit', e => {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            alertBox?.classList.remove('d-none');
        } else {
            alertBox?.classList.add('d-none');
        }
        form.classList.add('was-validated');
    });
})();
</script>
{/block}
