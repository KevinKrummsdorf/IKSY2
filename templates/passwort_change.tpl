{extends file="./layouts/layout.tpl"}

{block name="title"}Passwort ändern{/block}

{block name="content"}
<h1>Passwort ändern</h1>
{if $success}
    <div class="alert alert-success">Passwort wurde aktualisiert.</div>
{elseif $message}
    <div class="alert alert-danger">{$message|escape}</div>
{/if}
<div id="formAlert" class="alert alert-danger d-none">Bitte alle Felder ausfüllen.</div>
<form method="post" class="needs-validation" data-pw-validate novalidate>
    <div class="mb-3">
        <label for="old_password" class="form-label">Aktuelles Passwort</label>
        <input type="password" class="form-control" id="old_password" name="old_password" required>
    </div>
    <div class="mb-3 pass-field">
        <label for="new_password" class="form-label">Neues Passwort</label>
        <div class="input-group">
            <input type="password" class="form-control pw-new" id="new_password" name="new_password" required>
            <span class="input-group-text" id="toggleNewPassword" style="cursor: pointer;">
                <span class="material-symbols-outlined">visibility</span>
            </span>
        </div>
        <ul class="requirement-list mb-3">
          <li data-requirement="minlength"><i class="material-symbols-outlined">close</i>Mindestens 8 Zeichen</li>
          <li data-requirement="maxlength"><i class="material-symbols-outlined">close</i>Maximal 128 Zeichen</li>
          <li data-requirement="number"><i class="material-symbols-outlined">close</i>Mindestens eine Zahl</li>
          <li data-requirement="lowercase"><i class="material-symbols-outlined">close</i>Kleinbuchstabe</li>
          <li data-requirement="uppercase"><i class="material-symbols-outlined">close</i>Großbuchstabe</li>
          <li data-requirement="special"><i class="material-symbols-outlined">close</i>Sonderzeichen</li>
        </ul>
    </div>
    <div class="mb-3">
        <label for="new_password_confirm" class="form-label">Passwort bestätigen</label>
        <div class="input-group">
            <input type="password" class="form-control pw-confirm" id="new_password_confirm" name="new_password_confirm" required>
            <span class="input-group-text" id="toggleNewPasswordConfirm" style="cursor: pointer;">
                <span class="material-symbols-outlined">visibility</span>
            </span>
        </div>
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
