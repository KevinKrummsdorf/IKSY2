{extends file="./layouts/layout.tpl"}

{if $errors}
  <div class="alert alert-danger">
    <ul>
    {foreach $errors as $err}
      <li>{$err}</li>
    {/foreach}
    </ul>
  </div>
{/if}

{if $success}
  <div class="alert alert-success">
    Deine Nachricht (ID: {$contactId}) wurde erfolgreich versendet!
  </div>
{/if}

{block name="head" append}
  <script src="https://www.google.com/recaptcha/api.js?render={$recaptcha_site_key}"></script>
  <style>
  </style>
{/block}

{block name="content"}

<div class="container my-5">
  <h1 id="main-heading" class="text-center mb-4">Kontakt</h1>

  {if $success}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      Ihre Nachricht wurde erfolgreich gesendet.<br>
      {if $contactId}
        <strong>Ihre Kontakt-ID:</strong> {$contactId}
      {/if}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
    </div>
  {/if}

  {if $errors}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        {foreach $errors as $err}
          <li>{$err}</li>
        {/foreach}
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
    </div>
  {/if}

  <div class="mx-auto" style="max-width:600px">
    <div class="alert alert-info text-center" role="alert">
      Das Kontaktformular ist in dieser Demo-Version deaktiviert.
    </div>
    <div class="text-center mt-4">
      <h3>Kontaktieren Sie uns direkt:</h3>
      <p>E-Mail: <a href="mailto:studyhub.iksy@gmail.com">studyhub.iksy@gmail.com</a></p>
      <p>Servicezeiten: Montags bis Freitags 9:00 – 17:00 Uhr</p>
    </div>
  </div>
</div>

{* Der Code sorgt dafür, dass das Formular nur dann wirklich abgeschickt wird, wenn Google reCAPTCHA bestätigt *}
<script>
document.addEventListener('DOMContentLoaded', function() {
  const form    = document.getElementById('contact-form');
  const btn     = form.querySelector('button[type="submit"]');
  const spinner = document.getElementById('btn-spinner');
  const token   = document.getElementById('recaptcha_token');

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    btn.disabled = true;
    spinner.classList.remove('d-none');

    grecaptcha.ready(function() {
      grecaptcha.execute('{$recaptcha_site_key}', {ldelim}action:'contact'{rdelim})
        .then(function(t) {
          token.value = t;
          form.submit();
        })
        .catch(function() {
          // reCAPTCHA-Fehler: Button wieder aktivieren
          btn.disabled = false;
          spinner.classList.add('d-none');
          alert('Fehler bei der reCAPTCHA-Verifizierung. Bitte Seite neu laden.');
        });
    });
  });
});
</script>

{/block}
