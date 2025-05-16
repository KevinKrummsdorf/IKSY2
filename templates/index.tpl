{extends file="./layouts/layout.tpl"}

{block name="title"}Startseite{/block}

{block name="content"}
<div class="container-fluid my-5">
  <section class="central-content text-center p-3">
    <h1 id="main-heading" class="mb-4">Willkommen auf StudyHub</h1>
    <p class="lead mb-5">Die zentrale Plattform für deine Uni-Skripte, Zusammenfassungen & Lerngruppen</p>

    <section class="mb-5">
      <h2 class="mb-3">Material teilen</h2>
      <p class="mb-3">Lade deine Unterlagen hoch und hilf anderen Studierenden weiter.</p>
      <a href="{$base_url}/upload.php" class="btn btn-primary btn-lg" rel="noopener">Jetzt hochladen</a>
    </section>

    <section>
      <h2 class="mb-3">Material finden</h2>
      <p class="mb-3">Durchsuche Skripte, Zusammenfassungen und Mitschriften deiner Uni.</p>
      <a href="{$base_url}/browse.php" class="btn btn-primary btn-lg" rel="noopener">Jetzt entdecken</a>
    </section>
  </section>
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
