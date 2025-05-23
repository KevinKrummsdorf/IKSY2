{extends file="./layouts/layout.tpl"}

{block name="title"}Alle Upload-Logs{/block}

{block name="content"}
<div class="container mt-5">
  <h1>Alle Upload-Logs</h1>
  <p>Benutzer: {$username} | Rolle: {if $isAdmin}Administrator{elseif $isMod}Moderator{else}Benutzer{/if}</p>

  {if $upload_logs|@count > 0}
    <div class="table-responsive shadow-sm">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>#</th>
            <th>User ID</th>
            <th>Dateiname</th>
            <th>Zeitpunkt</th>
          </tr>
        </thead>
        <tbody>
          {foreach $upload_logs as $index => $log}
            <tr>
              <td>{$index + 1 + (($currentPage - 1) * 25)}</td>
              <td>{$log.user_id}</td>
              <td>{$log.stored_name|escape}</td>
              <td>{$log.uploaded_at}</td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>

    {* Pagination *}
    <nav aria-label="Seitennavigation">
      <ul class="pagination justify-content-center mt-4">
        {* Zurück-Button *}
        {if $currentPage > 1}
          <li class="page-item">
            <a class="page-link" href="upload_logs.php?page={$currentPage-1}">Zurück</a>
          </li>
        {else}
          <li class="page-item disabled">
            <span class="page-link">Zurück</span>
          </li>
        {/if}

        {* Seitenzahlen *}
        {section name=pages loop=$totalPages}
          {assign var="pageNum" value=$smarty.section.pages.index+1}
          <li class="page-item {if $pageNum == $currentPage}active{/if}">
            <a class="page-link" href="upload_logs.php?page={$pageNum}">{$pageNum}</a>
          </li>
        {/section}

        {* Weiter-Button *}
        {if $currentPage < $totalPages}
          <li class="page-item">
            <a class="page-link" href="upload_logs.php?page={$currentPage+1}">Weiter</a>
          </li>
        {else}
          <li class="page-item disabled">
            <span class="page-link">Weiter</span>
          </li>
        {/if}
      </ul>
    </nav>

  {else}
    <div class="alert alert-info">Keine Upload-Logs vorhanden.</div>
  {/if}

  <div class="mt-4">
    <a href="dashboard.php" class="btn btn-sm btn-primary">Zurück zum Dashboard</a>
  </div>
</div>
{/block}
