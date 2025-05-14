{extends file="./layouts/layout.tpl"}

{block name="title"}Kontaktanfragen{/block}

{block name="content"}
<div class="container mt-5">
  <h1>Kontaktanfragen</h1>

  {if $contact_requests|@count > 0}
    {assign var="perPage" value=25}
    {assign var="startIndex" value=($currentPage - 1) * $perPage}

    {*
      Bootstrap-Alert für Löschbestätigung
      und verstecktes Formular für den POST
    *}
    <div id="deleteAlert" class="alert alert-warning d-none align-items-center" role="alert">
      <div class="flex-grow-1">
        Möchten Sie diese Kontaktanfrage wirklich löschen?
      </div>
      <div>
        <button type="button" id="confirmDelete" class="btn btn-sm btn-danger me-1">Löschen</button>
        <button type="button" id="cancelDelete" class="btn btn-sm btn-secondary">Abbrechen</button>
      </div>
      <form id="deleteForm" method="post" class="d-none">
        <input type="hidden" name="delete_id" id="delete_id">
      </form>
    </div>

    <div class="table-responsive shadow-sm mt-4">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>#</th>
            <th>Kontakt-ID</th>
            <th>Name</th>
            <th>E-Mail</th>
            <th>Betreff</th>
            <th>Datum</th>
            <th>Löschen</th>
          </tr>
        </thead>
        <tbody>
          {foreach $contact_requests as $req name=reqLoop}
            <tr>
              <td class="text-center">{$startIndex + $smarty.foreach.reqLoop.index + 1}</td>
              <td><code>{$req.contact_id|escape}</code></td>
              <td>{$req.name|escape}</td>
              <td><a href="mailto:{$req.email|escape}">{$req.email|escape}</a></td>
              <td>{$req.subject|escape}</td>
              <td>{$req.created_at}</td>
              <td class="text-center">
                <button
                  type="button"
                  class="btn btn-sm btn-outline-danger p-1 delete-btn"
                  data-id="{$req.contact_id|escape}"
                  data-page="{$currentPage}"
                >
                  <span class="material-symbols-outlined" style="vertical-align: middle;">
                    delete
                  </span>
                </button>
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>

    {*
      Pagination
    *}
    <nav aria-label="Seitennavigation" class="mt-4">
      <ul class="pagination justify-content-center">
        {if $currentPage > 1}
          <li class="page-item">
            <a class="page-link" href="contact_requests.php?page={$currentPage - 1}">Zurück</a>
          </li>
        {else}
          <li class="page-item disabled"><span class="page-link">Zurück</span></li>
        {/if}

        {section name=pages loop=$totalPages}
          {assign var="pageNum" value=$smarty.section.pages.index+1}
          <li class="page-item {if $pageNum == $currentPage}active{/if}">
            <a class="page-link" href="contact_requests.php?page={$pageNum}">{$pageNum}</a>
          </li>
        {/section}

        {if $currentPage < $totalPages}
          <li class="page-item">
            <a class="page-link" href="contact_requests.php?page={$currentPage + 1}">Weiter</a>
          </li>
        {else}
          <li class="page-item disabled"><span class="page-link">Weiter</span></li>
        {/if}
      </ul>
    </nav>

  {else}
    <div class="alert alert-info mt-4">Keine Kontaktanfragen vorhanden.</div>
  {/if}
</div>

  <div class="mt-4">
    <a href="dashboard.php" class="btn btn-sm btn-primary">Zurück zum Dashboard</a>
  </div>

{literal}
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const alertBox = document.getElementById('deleteAlert');
    const confirmBtn = document.getElementById('confirmDelete');
    const cancelBtn = document.getElementById('cancelDelete');
    const deleteForm = document.getElementById('deleteForm');
    const deleteInput = document.getElementById('delete_id');

    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-id');
        const page = btn.getAttribute('data-page');

        deleteForm.action = window.location.pathname + '?page=' + encodeURIComponent(page);
        deleteInput.value = id;

        alertBox.classList.remove('d-none');
        alertBox.scrollIntoView({ behavior: 'smooth' });
      });
    });

    cancelBtn.addEventListener('click', () => {
      alertBox.classList.add('d-none');
    });

    confirmBtn.addEventListener('click', () => {
      deleteForm.submit();
    });
  });
</script>
{/literal}
{/block}
