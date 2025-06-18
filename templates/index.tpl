{extends file="./layouts/layout.tpl"}

{block name="title"}Startseite{/block}

{block name="content"}
<div class="container-fluid my-5">
  <section class="central-content text-center p-3 mb-5">
    <h1 id="main-heading" class="mb-4">Willkommen auf StudyHub</h1>
    <p class="lead mb-0">StudyHub hilft dir, Lernmaterialien zu sammeln, zu organisieren und dich mit anderen Studierenden auszutauschen.</p>
  </section>

  <section class="mb-5 text-center">
    <p>Auf unserer Plattform kannst du Skripte und Mitschriften bequem hochladen, nach nützlichen Dokumenten suchen und Lerngruppen für deine Kurse finden. So behältst du alles im Blick und profitierst vom Wissen deiner Kommilitoninnen und Kommilitonen.</p>
  </section>

  <section aria-labelledby="features-heading" class="mb-5">
    <h2 id="features-heading" class="h4 text-center mb-4">Funktionen für Mitglieder</h2>
    <ul class="list-unstyled mx-auto" style="max-width: 600px;">
      <li class="d-flex align-items-start mb-2"><span class="material-symbols-outlined me-2" aria-hidden="true">arrow_circle_up</span> Eigene Mitschriften und Dateien hochladen</li>
      <li class="d-flex align-items-start mb-2"><span class="material-symbols-outlined me-2" aria-hidden="true">search</span> Freigegebene Materialien durchsuchen und herunterladen</li>
      <li class="d-flex align-items-start mb-2"><span class="material-symbols-outlined me-2" aria-hidden="true">groups</span> Lerngruppen beitreten oder erstellen</li>
      <li class="d-flex align-items-start mb-2"><span class="material-symbols-outlined me-2" aria-hidden="true">calendar_month</span> Eigene Aufgaben im Kalender verwalten</li>
      <li class="d-flex align-items-start mb-2"><span class="material-symbols-outlined me-2" aria-hidden="true">person</span> Persönliches Profil bearbeiten</li>
      <li class="d-flex align-items-start mb-2"><span class="material-symbols-outlined me-2" aria-hidden="true">checklist</span> Eigene To-dos anlegen</li>
      <li class="d-flex align-items-start mb-2"><span class="material-symbols-outlined me-2" aria-hidden="true">calendar_month</span> Stundenplan erstellen</li>
      <li class="d-flex align-items-start"><span class="material-symbols-outlined me-2" aria-hidden="true">timer</span> Lerntimer im Dashboard nutzen</li>
    </ul>
  </section>

  <div class="text-center">
    <a href="{$base_url}/browse.php" class="btn btn-primary btn-lg" rel="noopener">Jetzt Mitschriften durchsuchen</a>
  </div>
</div>
{if isset($show_modal)}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const targetModalId = {
            login: 'loginModal',
            register: 'registerModal'
        }['{$show_modal|escape}'];

        if (targetModalId) {
            const modalElement = document.getElementById(targetModalId);
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        }
    });
</script>
{/if}

{/block}
