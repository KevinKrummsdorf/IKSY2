{extends file="./layouts/layout.tpl"}

{block name="title"}Gesperrte Benutzerkonten{/block}

{block name="content"}
<div class="container mt-5">
  <h1>Gesperrte Benutzer</h1>
  <p>Angemeldet als: {$username} | Rolle: Administrator</p>

  {if $locked_users|@count > 0}
    <div class="table-responsive shadow-sm mt-4">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>#</th>
            <th>User ID</th>
            <th>Benutzername</th>
            <th>E-Mail</th>
            <th>Fehlversuche</th>
            <th>Aktion</th>
          </tr>
        </thead>
        <tbody>
          {foreach $locked_users as $index => $user}
            <tr>
              <td>{$index + 1 + (($currentPage - 1) * 25)}</td>
              <td>{$user.id}</td>
              <td>{$user.username|escape}</td>
              <td><a href="mailto:{$user.email|escape}">{$user.email|escape}</a></td>
              <td>{$user.failed_attempts}</td>
              <td class="text-center">
                <button type="button"
                        class="btn btn-success btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#confirmUnlockModal"
                        data-user-id="{$user.id}">
                  Entsperren
                </button>
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
  {else}
    <div class="alert alert-success mt-4">Es sind aktuell keine Benutzerkonten gesperrt.</div>
  {/if}

  {* Modal außerhalb des if-Blocks *}
  <div class="modal fade" id="confirmUnlockModal" tabindex="-1" aria-labelledby="confirmUnlockLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="post" action="locked_users.php?page={$currentPage}">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmUnlockLabel">Benutzerkonto entsperren</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
          </div>
          <div class="modal-body">
            <p>Möchtest du dieses Benutzerkonto wirklich entsperren?</p>
            <div class="alert alert-info">
              <strong>Benutzer-ID:</strong> <span id="debug-user-id">?</span>
            </div>
          </div>
          <input type="hidden" name="user_id" id="modal-user-id">
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
            <button type="submit" class="btn btn-success">Entsperren</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {* Pagination *}
  <nav aria-label="Seitennavigation">
    <ul class="pagination justify-content-center mt-4">
      {if $currentPage > 1}
        <li class="page-item"><a class="page-link" href="locked_users.php?page={$currentPage-1}">Zurück</a></li>
      {else}
        <li class="page-item disabled"><span class="page-link">Zurück</span></li>
      {/if}

      {section name=pages loop=$totalPages}
        {assign var="pageNum" value=$smarty.section.pages.index+1}
        <li class="page-item {if $pageNum == $currentPage}active{/if}">
          <a class="page-link" href="locked_users.php?page={$pageNum}">{$pageNum}</a>
        </li>
      {/section}

      {if $currentPage < $totalPages}
        <li class="page-item"><a class="page-link" href="locked_users.php?page={$currentPage+1}">Weiter</a></li>
      {else}
        <li class="page-item disabled"><span class="page-link">Weiter</span></li>
      {/if}
    </ul>
  </nav>

  <div class="mt-4">
    <a href="dashboard.php" class="btn btn-sm btn-primary">Zurück zum Dashboard</a>
  </div>
</div>
{/block}

{block name="scripts"}
<script>
document.addEventListener('DOMContentLoaded', function () {
  const input = document.getElementById('modal-user-id');
  const debug = document.getElementById('debug-user-id');

  // Fallback: Direkt auf Button klicken statt Modal-Ereignis
  document.querySelectorAll('button[data-user-id]').forEach(function (button) {
    button.addEventListener('click', function () {
      const userId = this.getAttribute('data-user-id');
      if (input) input.value = userId;
      if (debug) debug.textContent = userId;

      console.log('🟢 Direkt über Button gesetzt:', userId);
    });
  });
});
</script>
{/block}

