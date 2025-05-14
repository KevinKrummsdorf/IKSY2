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

  <form id="contact-form" action="" method="POST" class="mx-auto" style="max-width:600px">
    <div class="mb-3">
      <label for="name" class="form-label">Name</label>
      <input id="name" name="name" type="text" class="form-control"
             value="{$input.name|escape}" required>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">E-Mail</label>
      <input id="email" name="email" type="email" class="form-control"
             value="{$input.email|escape}" required>
    </div>

    <div class="mb-3">
      <label for="subject" class="form-label">Betreff</label>
      <input id="subject" name="subject" type="text" class="form-control"
             value="{$input.subject|escape}" required>
    </div>

    <div class="mb-3">
      <label for="message" class="form-label">Nachricht</label>
      <textarea id="message" name="message" rows="6" class="form-control" required>{$input.message|escape}</textarea>
    </div>

    <input type="hidden" name="recaptcha_token" id="recaptcha_token">

    <button type="submit" class="btn btn-primary w-100 position-relative">
      <span class="spinner-border spinner-border-sm me-2 d-none"
            id="btn-spinner" role="status"></span>
      Nachricht senden
    </button>
    <br></br>
    <div class="text-center mt-4">
      <h3>Oder kontaktieren Sie uns direkt:</h3>
      <p>E-Mail: <a href="mailto:studyhub.iksy@gmail.com">studyhub.iksy@gmail.com</a></p>
      <p>Servicezeiten: Montags bis Freitags 9:00 – 17:00 Uhr</p>
    </div>
  </form>
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
