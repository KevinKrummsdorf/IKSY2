{extends file="./layouts/layout.tpl"}

{block name="title"}Passwort vergessen{/block}

{block name="content"}
<h1>Passwort zurücksetzen</h1>
{if $success}
    <div class="alert alert-success">E-Mail wurde versendet.</div>
{elseif $message}
    <div class="alert alert-danger">{$message}</div>
{/if}
<form method="post" class="needs-validation" novalidate>
    <div class="mb-3">
        <label for="identifier" class="form-label">Benutzername oder E-Mail</label>
        <input type="text" class="form-control" id="identifier" name="identifier" required>
    </div>
    <button type="submit" class="btn btn-primary">E-Mail senden</button>
</form>
<script>
(() => {
    'use strict';
    const form = document.querySelector('.needs-validation');
    form?.addEventListener('submit', e => {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            alert('Bitte alle Felder ausfüllen.');
        }
        form.classList.add('was-validated');
    });
})();
</script>
{/block}
