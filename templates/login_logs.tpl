{extends file="./layouts/layout.tpl"}

{block name="title"}Login-Logs Übersicht{/block}

{block name="content"}
<div class="container mt-5">
  <h1>Login-Logs</h1>

  {if $login_logs|count > 0}
    <div class="table-responsive shadow-sm">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>User ID</th>
            <th>IP (anonymisiert)</th>
            <th>Erfolg</th>
            <th>Grund</th>
            <th>Zeitpunkt</th>
          </tr>
        </thead>
        <tbody>
          {foreach from=$login_logs item=log name=logs}
            <tr>
              <td>{$smarty.foreach.logs.index+1 + ($currentPage-1)*25}</td>
              <td>{$log.username|default:'–'}</td>
              <td>{$log.user_id|default:'–'}</td>
              <td>{$log.ip_address|escape}</td>
              <td class="text-center">
                {if $log.success}
                  <span class="badge bg-success">Ja</span>
                {else}
                  <span class="badge bg-danger">Nein</span>
                {/if}
              </td>
              <td>{$log.reason|default:'–'|escape}</td>
              <td>{$log.created_at}</td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>

    {* Pagination *}
    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center mt-4">
        {if $currentPage > 1}
          <li class="page-item">
            <a class="page-link" href="?page={$currentPage-1}" aria-label="Previous">
              &laquo; Zurück
            </a>
          </li>
        {/if}

        {section name=page start=1 loop=$totalPages+1}
          <li class="page-item {if $smarty.section.page.index == $currentPage}active{/if}">
            <a class="page-link" href="?page={$smarty.section.page.index}">
              {$smarty.section.page.index}
            </a>
          </li>
        {/section}

        {if $currentPage < $totalPages}
          <li class="page-item">
            <a class="page-link" href="?page={$currentPage+1}" aria-label="Next">
              Weiter &raquo;
            </a>
          </li>
        {/if}
      </ul>
    </nav>

  {else}
    <div class="alert alert-info">Keine Login-Logs gefunden.</div>
  {/if}
</div>
  <div class="mt-4">
    <a href="dashboard.php" class="btn btn-sm btn-primary">Zurück zum Dashboard</a>
  </div>
{/block}
