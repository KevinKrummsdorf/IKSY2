{extends file="./layouts/layout.tpl"}

{block name="title"}Startseite{/block}

{block name="content"}
  {* Fehlermeldungen (Errors) *}
  {if $smarty.get.error eq 'user_not_found'}
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
      <strong>Benutzer nicht gefunden.</strong><br>
      Bitte überprüfe Benutzername oder E-Mail.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
    </div>
  {/if}

  {if $smarty.get.error eq 'not_verified'}
    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
      <strong>Account noch nicht verifiziert.</strong><br>
      Bitte klicke auf den Link in deiner Bestätigungs-E-Mail.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
    </div>
  {/if}

  {if $smarty.get.error eq 'wrong_password'}
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
      <strong>Falsches Passwort.</strong><br>
      Bitte versuche es erneut.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
    </div>
  {/if}

  {* numerische Error-Codes aus alten Redirects *}
  {if $smarty.get.error eq '1'}
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
      Bitte alle Felder ausfüllen.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
    </div>
  {/if}

  {if $smarty.get.error eq '2'}
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
      Serverfehler. Bitte später erneut versuchen.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
    </div>
  {/if}

  {* Login-Success-Alert und JS-Redirect nach 3 Sekunden *}
  {if $smarty.get.login eq 'success'}
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
      Login erfolgreich! Du wirst zum Dashboard weitergeleitet.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
    </div>
    {* JavaScript-Weiterleitung nach 3000 ms *}
    <script>
      setTimeout(function(){
        window.location.href = 'dashboard.php';
      }, 3000);
    </script>
  {/if}

  <div class="container-fluid">
    <section class="central-content text-center p-3">
      <h1 class="mb-4">Willkommen auf StudyHub</h1>
      <p class="lead mb-5">Die zentrale Plattform für deine Uni-Skripte, Zusammenfassungen & Lerngruppen</p>

      <section class="mb-5">
        <h2 class="mb-3">Material teilen</h2>
        <p class="mb-3">Lade deine Unterlagen hoch und hilf anderen Studierenden weiter.</p>
        <a href="{$base_url}/upload.php" class="btn btn-primary btn-lg">Jetzt hochladen</a>
      </section>

      <section>
        <h2 class="mb-3">Material finden</h2>
        <p class="mb-3">Durchsuche Skripte, Zusammenfassungen und Mitschriften deiner Uni.</p>
        <a href="{$base_url}/browse.php" class="btn btn-primary btn-lg">Jetzt entdecken</a>
      </section>
    </section>
  </div>
{/block}
