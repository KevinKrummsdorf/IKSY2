{extends file="./layouts/layout.tpl"}

{block name="title"}Upload-Logs{/block}

{block name="content"}
<div class="container mt-5">
  <h1>Upload-Logs</h1>

  <!-- Filterformular -->
  <form class="row g-3 mt-3 mb-4" method="get" action="{$base_url}/upload_logs">
    <div class="col-md-2">
      <label for="user_id" class="form-label">User-ID</label>
      <input type="number" class="form-control" id="user_id" name="user_id" value="{$filters.user_id|escape}">
    </div>
    <div class="col-md-3">
      <label for="filename" class="form-label">Dateiname</label>
      <input type="text" class="form-control" id="filename" name="filename" value="{$filters.filename|escape}">
    </div>
    <div class="col-md-3">
      <label for="course_name" class="form-label">Kursname</label>
      <input type="text" class="form-control" id="course_name" name="course_name" value="{$filters.course_name|escape}">
    </div>
    <div class="col-md-2">
      <label for="from_date" class="form-label">Von</label>
      <input type="date" class="form-control" id="from_date" name="from_date" value="{$filters.from_date|escape}">
    </div>
    <div class="col-md-2">
      <label for="to_date" class="form-label">Bis</label>
      <input type="date" class="form-control" id="to_date" name="to_date" value="{$filters.to_date|escape}">
    </div>
    <div class="col-12 d-flex justify-content-end">
      <button type="submit" class="btn btn-primary me-2">Filtern</button>
      <a href="{$base_url}/upload_logs" class="btn btn-outline-secondary me-2">Zurücksetzen</a>
      <button type="submit" name="export" value="csv" class="btn btn-success">Exportieren als CSV</button>
    </div>
  </form>

  <!-- Ergebnisanzeige -->
  {if $upload_logs|@count > 0}
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark text-center">
              <tr>
                <th>#</th>
                <th>Log ID</th>
                <th>Benutzername</th>
                <th>Dateiname</th>
                <th>Kurs</th>
                <th>Zeitpunkt</th>
              </tr>
            </thead>
            <tbody>
              {foreach $upload_logs as $index => $log}
              <tr>
                <td>{$index + 1 + ($currentPage - 1) * 25}</td>
                <td>{$log.log_id}</td>
                <td>{$log.username|default:'Unbekannt'|escape}</td>
                <td>{$log.stored_name|escape}</td>
                <td>{$log.course_name|default:'–'|escape}</td>
                <td>{$log.action_time|date_format:"%d.%m.%Y %H:%M"}</td>
              </tr>
              {/foreach}
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        {if $totalPages > 1}
        <nav aria-label="Upload-Log-Seiten">
          <ul class="pagination justify-content-center mt-3">
            {section name=page loop=$totalPages start=1 step=1}
              {assign var=pageNum value=$smarty.section.page.index}
              <li class="page-item {if $pageNum+1 == $currentPage}active{/if}">
                <a class="page-link" href="?page={$pageNum+1}
                  {if $filters.user_id}&user_id={$filters.user_id|escape}{/if}
                  {if $filters.filename}&filename={$filters.filename|escape}{/if}
                  {if $filters.course_name}&course_name={$filters.course_name|escape}{/if}
                  {if $filters.from_date}&from_date={$filters.from_date|escape}{/if}
                  {if $filters.to_date}&to_date={$filters.to_date|escape}{/if}">
                  {$pageNum+1}
                </a>
              </li>
            {/section}
          </ul>
        </nav>
        {/if}
      </div>
    </div>
  {else}
    <div class="alert alert-info mt-4">Keine Upload-Logs gefunden.</div>
  {/if}
  <div class="mt-4">
    <a href="{url path='dashboard'}" class="btn btn-sm btn-primary">Zurück zum Dashboard</a>
  </div>
</div>
{/block}
