{extends file="./layouts/layout.tpl"}

{block name="title"}Passwort ändern{/block}

{block name="content"}
<h1>Passwort ändern</h1>
{if $success}
    <div class="alert alert-success">Passwort wurde aktualisiert.</div>
{elseif $message}
    <div class="alert alert-danger">{$message}</div>
{/if}
<div id="formAlert" class="alert alert-danger d-none">Bitte alle Felder ausfüllen.</div>
<form method="post" class="needs-validation" novalidate>
    <div class="mb-3">
        <label for="old_password" class="form-label">Aktuelles Passwort</label>
        <input type="password" class="form-control" id="old_password" name="old_password" required>
    </div>
    <div class="mb-3">
        <label for="new_password" class="form-label">Neues Passwort</label>
        <input type="password" class="form-control" id="new_password" name="new_password" required>
    </div>
    <div class="mb-3">
        <label for="new_password_confirm" class="form-label">Passwort bestätigen</label>
        <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm" required>
    </div>
    <button type="submit" class="btn btn-primary">Passwort speichern</button>
</form>
<script>
(() => {
    'use strict';
    const form = document.querySelector('.needs-validation');
    const alertBox = document.getElementById('formAlert');
    form?.addEventListener('submit', e => {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            alertBox.classList.remove('d-none');
        } else {
            alertBox.classList.add('d-none');
        }
        form.classList.add('was-validated');
    });
})();
</script>
{/block}
