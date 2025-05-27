{extends file="./layouts/layout.tpl"}

{block name="title"}Login-Logs{/block}

{block name="content"}
<div class="container mt-5">
  <h1>Login-Logs</h1>

  <!-- Filterformular -->
  <form class="row g-3 mt-3 mb-4" method="get" action="{$base_url}/login_logs.php">
    <div class="col-md-2">
      <label for="user_id" class="form-label">User-ID</label>
      <input type="number" class="form-control" id="user_id" name="user_id" value="{$filters.user_id|escape}">
    </div>
    <div class="col-md-3">
      <label for="ip_address" class="form-label">IP-Adresse</label>
      <input type="text" class="form-control" id="ip_address" name="ip_address" value="{$filters.ip_address|escape}">
    </div>
    <div class="col-md-2">
      <label for="success" class="form-label">Status</label>
      <select id="success" name="success" class="form-select">
        <option value="" {if $filters.success === ''}selected{/if}>Alle</option>
        <option value="1" {if $filters.success == '1'}selected{/if}>Erfolg</option>
        <option value="0" {if $filters.success == '0'}selected{/if}>Fehlgeschlagen</option>
      </select>
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
      <a href="{$base_url}/login_logs.php" class="btn btn-outline-secondary me-2">Zurücksetzen</a>
      <button type="submit" name="export" value="csv" class="btn btn-success">Exportieren als CSV</button>
    </div>
  </form>

  <!-- Ergebnisanzeige -->
  {if $login_logs|@count > 0}
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark text-center">
              <tr>
                <th>#</th>
                <th>User-ID</th>
                <th>Benutzername</th>
                <th>IP-Adresse</th>
                <th>Status</th>
                <th>Zeitpunkt</th>
                <th>Grund</th>
              </tr>
            </thead>
            <tbody>
              {foreach $login_logs as $index => $log}
              <tr>
                <td>{$index + 1 + ($currentPage - 1) * 25}</td>
                <td>{$log.user_id|default:'–'}</td>
                <td>{$log.username|default:'Unbekannt'|escape}</td>
                <td>{$log.ip_address|escape}</td>
                <td class="text-center">
                  {if $log.success}
                    <span class="badge bg-success">Erfolg</span>
                  {else}
                    <span class="badge bg-danger">Fehlgeschlagen</span>
                  {/if}
                </td>
                <td>{$log.created_at|date_format:"%d.%m.%Y %H:%M"}</td>
                <td>{$log.reason|default:'–'|escape}</td>
              </tr>
              {/foreach}
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        {if $totalPages > 1}
        <nav aria-label="Login-Log-Seiten">
          <ul class="pagination justify-content-center mt-3">
            {section name=page loop=$totalPages start=1 step=1}
              {assign var=pageNum value=$smarty.section.page.index}
              <li class="page-item {if $pageNum+1 == $currentPage}active{/if}">
                <a class="page-link" href="?page={$pageNum+1}
                  {if $filters.user_id}&user_id={$filters.user_id|escape}{/if}
                  {if $filters.ip_address}&ip_address={$filters.ip_address|escape}{/if}
                  {if $filters.success}&success={$filters.success|escape}{/if}
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
    <div class="alert alert-info mt-4">Keine Login-Logs gefunden.</div>
  {/if}
  <div class="mt-4">
    <a href="dashboard.php" class="btn btn-sm btn-primary">Zurück zum Dashboard</a>
  </div>
</div>
{/block}
