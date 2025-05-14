<script>
    window.recaptchaSiteKey = '{$recaptcha_site_key}';
    const baseUrl = '{$base_url}';
</script>
<script src="https://www.google.com/recaptcha/api.js?render={$recaptcha_site_key}" async defer></script>

<link href="{$base_url}/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="{$base_url}/css/style.css" rel="stylesheet">

{block name="content"}
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center mb-4">2-Faktor-Authentifizierung</h2>

            {if isset($flash.message) || isset($message)}
                <div class="alert alert-{if isset($flash.type)}{$flash.type|default:'info'}{else}danger{/if}">
                    {if isset($flash.message)}
                        {$flash.message}
                    {else}
                        {$message}
                    {/if}
                </div>
            {/if}

            <form method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="code" class="form-label">Bestätigungscode</label>
                    <input type="text"
                           class="form-control"
                           id="code"
                           name="code"
                           inputmode="numeric"
                           maxlength="6"
                           required
                           autocomplete="one-time-code"
                           placeholder="123456"
                           title="Bitte genau 6 Ziffern eingeben">
                    <div class="invalid-feedback">
                        Bitte gib einen gültigen 6-stelligen Code ein.
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Bestätigen</button>
            </form>
        </div>
    </div>
</div>

<script>
(() => {
    'use strict';
    const form = document.querySelector('.needs-validation');
    form?.addEventListener('submit', e => {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Eingabe auf 6 Ziffern begrenzen (nur Zahlen)
    const input = document.getElementById('code');
    input?.addEventListener('input', e => {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 6);
    });
})();
</script>
{/block}
