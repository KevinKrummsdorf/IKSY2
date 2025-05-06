{extends file="./layouts/layout.tpl"}

{block name="head" append}
  <script src="https://www.google.com/recaptcha/api.js?render={$recaptcha_site_key}"></script>
  <style>
    .grecaptcha-badge {
      z-index: 9999 !important;
      bottom: 80px !important;
      right: 20px !important;
    }
  </style>
{/block}

{block name="content"}
<div class="container my-5">
  <h1 class="text-center">Kontakt</h1>

  {if $success}
    <div class="alert alert-success show">
      Ihre Nachricht wurde erfolgreich gesendet.<br>
      {if $orderId}
        <strong>Ihre Auftrags-ID:</strong> {$orderId}
      {/if}
    </div>
  {/if}

  {if $errors}
    <div class="alert alert-danger show">
      <ul class="mb-0">
        {foreach $errors as $err}
          <li>{$err}</li>
        {/foreach}
      </ul>
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
      <textarea id="message" name="message" rows="6" class="form-control" required>
        {$input.message|escape}
      </textarea>
    </div>

    <input type="hidden" name="recaptcha_token" id="recaptcha_token">

    <button type="submit" class="btn btn-primary w-100 position-relative">
      <span class="spinner-border spinner-border-sm me-2 d-none"
            id="btn-spinner" role="status"></span>
      Nachricht senden
    </button>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var form    = document.getElementById('contact-form'),
      btn     = form.querySelector('button[type="submit"]'),
      spinner = document.getElementById('btn-spinner'),
      token   = document.getElementById('recaptcha_token');

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    btn.disabled = true;
    spinner.classList.remove('d-none');

    grecaptcha.ready(function() {
      grecaptcha.execute('{$recaptcha_site_key}', {ldelim}action:'contact'{rdelim})
      .then(function(t) {
        token.value = t;
        form.submit();
      });
    });
  });

  setTimeout(function() {
    document.querySelectorAll('.alert').forEach(function(a) {
      a.classList.add('fade');
      a.classList.remove('show');
      a.addEventListener('transitionend', function(){ a.remove(); });
    });
  }, 5000);
});
</script>
{/block}
