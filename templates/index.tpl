{extends file="./layouts/layout.tpl"}

{block name="title"}Startseite{/block}

{block name="content"}
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

{if $smarty.get.error == 1}
<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
  Falsche Zugangsdaten. Bitte erneut versuchen.
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
</div>
{/if}

{if $smarty.get.error == 2}
<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
  Serverfehler. Bitte später erneut versuchen.
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
</div>
{/if}

