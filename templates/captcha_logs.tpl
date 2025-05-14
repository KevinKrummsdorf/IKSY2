{extends file="./layouts/layout.tpl"}

{block name="title"}reCAPTCHA-Protokolle{/block}

{block name="content"}
<div class="container mt-5">
  <h1>reCAPTCHA-Protokolle</h1>

  {if $captcha_logs|@count > 0}
    {assign var="startIndex" value=($currentPage-1)*25}
    <div class="table-responsive shadow-sm">
      <table class="table table-striped table-bordered table-sm align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>#</th>
            <th>ID</th>
            <th>Erfolg</th>
            <th>Score</th>
            <th>Aktion</th>
            <th>Hostname</th>
            <th>Fehlergrund</th>
            <th>Zeitpunkt</th>
          </tr>
        </thead>
        <tbody>
          {foreach $captcha_logs as $log name=logsLoop}
            <tr>
              <td class="text-center">
                {$startIndex + $smarty.foreach.logsLoop.index + 1}
              </td>
              <td>{$log.id|escape}</td>
              <td class="text-center">
                {if $log.success}
                  <span class="badge bg-success">Ja</span>
                {else}
                  <span class="badge bg-danger">Nein</span>
                {/if}
              </td>
              <td>{$log.score|default:'–'}</td>
              <td>{$log.action|escape}</td>
              <td>{$log.hostname|escape}</td>
              <td>{$log.error_reason|default:'–'|escape}</td>
              <td>{$log.created_at}</td>
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
            <a class="page-link" href="captcha_logs.php?page={$currentPage-1}">Zurück</a>
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
            <a class="page-link" href="captcha_logs.php?page={$pageNum}">{$pageNum}</a>
          </li>
        {/section}

        {* Weiter-Button *}
        {if $currentPage < $totalPages}
          <li class="page-item">
            <a class="page-link" href="captcha_logs.php?page={$currentPage+1}">Weiter</a>
          </li>
        {else}
          <li class="page-item disabled">
            <span class="page-link">Weiter</span>
          </li>
        {/if}
      </ul>
    </nav>

  {else}
    <div class="alert alert-info">Keine reCAPTCHA-Logs vorhanden.</div>
  {/if}
    <div class="mt-4">
    <a href="dashboard.php" class="btn btn-sm btn-primary">Zurück zum Dashboard</a>
  </div>
</div>
{/block}
