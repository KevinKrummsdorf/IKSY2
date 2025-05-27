{extends file="layouts/layout.tpl"}

{block name="title"}CAPTCHA Logs{/block}

{block name="content"}
<div class="container my-4">
    <h1 class="mb-4">CAPTCHA Logs</h1>

    <!-- Filterformular -->
    <form method="get" class="row gy-2 gx-3 align-items-center mb-4">
        <div class="col-md-2">
            <label class="form-label">Erfolg</label>
            <select name="success" class="form-select">
                <option value="">Alle</option>
                <option value="1" {if $filters.success == '1'}selected{/if}>✔ Erfolg</option>
                <option value="0" {if $filters.success == '0'}selected{/if}>✘ Fehler</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Aktion</label>
            <input type="text" name="action" class="form-control" value="{$filters.action|escape}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Hostname</label>
            <input type="text" name="hostname" class="form-control" value="{$filters.hostname|escape}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Score (min)</label>
            <input type="number" step="0.01" name="score_min" class="form-control" value="{$filters.score_min|escape}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Score (max)</label>
            <input type="number" step="0.01" name="score_max" class="form-control" value="{$filters.score_max|escape}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Von</label>
            <input type="date" name="from_date" class="form-control" value="{$filters.from_date|escape}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Bis</label>
            <input type="date" name="to_date" class="form-control" value="{$filters.to_date|escape}">
        </div>
     <div class="col-12 d-flex justify-content-end">
      <button type="submit" class="btn btn-primary me-2">Filtern</button>
      <a href="{$base_url}/pending_uploads.php" class="btn btn-outline-secondary me-2">Zurücksetzen</a>
        <a href="{$smarty.server.SCRIPT_NAME}?export=csv{foreach $filters as $key => $val}{if $val != ''}&{$key}={$val|escape}{/if}{/foreach}" class="btn btn-success">Als CSV exportieren</a>
    </div>
    </form>
    </div>

    <!-- Ergebnis-Tabelle -->
    {if $captcha_logs|@count > 0}
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Aktion</th>
                    <th>Hostname</th>
                    <th>Fehler</th>
                    <th>Zeitpunkt</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$captcha_logs item=log key=idx}
                    <tr>
                        <td>{$idx+1}</td>
                        <td>{if $log.success}✔{/if}{if !$log.success}✘{/if}</td>
                        <td>{$log.score}</td>
                        <td>{$log.action|escape}</td>
                        <td>{$log.hostname|escape}</td>
                        <td>{$log.error_reason|escape}</td>
                        <td>{$log.created_at|date_format:"%d.%m.%Y %H:%M"}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
    {else}
        <div class="alert alert-info">Keine passenden Einträge gefunden.</div>
    {/if}

    <!-- Pagination -->
    {if $totalPages > 1}
    <nav aria-label="Seiten">
        <ul class="pagination justify-content-center">
            {section name=page start=1 loop=$totalPages+1}
                <li class="page-item {if $currentPage == $smarty.section.page.index}active{/if}">
                    <a class="page-link" href="?page={$smarty.section.page.index}
                        {foreach $filters as $key => $val}{if $val != ''}&{$key}={$val|escape}{/if}{/foreach}">
                        {$smarty.section.page.index}
                    </a>
                </li>
            {/section}
        </ul>
    </nav>
    {/if}
    <div class="mt-4">
      <a href="dashboard.php" class="btn btn-sm btn-primary">Zurück zum Dashboard</a>
    </div>
</div>
{/block}
