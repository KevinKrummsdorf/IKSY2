{extends file="./layouts/layout.tpl"}

{block name="title"}Passwort zurücksetzen{/block}

{block name="content"}
<h1>Neues Passwort setzen</h1>

{if $success}
    <div class="alert alert-success">Passwort wurde geändert.</div>
    <a href="{$base_url}/" class="btn btn-primary">Zur Startseite</a>
{/if}

{if $message}
    <div class="alert alert-danger">{$message|escape}</div>
{/if}

{if !$success}
<form method="post" class="needs-validation" data-pw-validate novalidate>
    <input type="hidden" name="csrf_token" value="{$csrf_token}">
    <div id="formAlert" class="alert alert-danger d-none">Bitte alle Felder ausfüllen.</div>

    <div class="mb-3 pass-field">
        <label for="password" class="form-label">Neues Passwort</label>
        <div class="input-group">
            <input type="password" class="form-control pw-new" id="password" name="password" required>
            <span class="input-group-text" id="toggleResetPassword" style="cursor: pointer;">
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
        <label for="password_confirm" class="form-label">Passwort bestätigen</label>
        <div class="input-group">
            <input type="password" class="form-control pw-confirm" id="password_confirm" name="password_confirm" required>
            <span class="input-group-text" id="toggleResetPasswordConfirm" style="cursor: pointer;">
                <span class="material-symbols-outlined">visibility</span>
            </span>
        </div>
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
