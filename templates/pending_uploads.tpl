{extends file="./layouts/layout.tpl"}
{block name="title"}Ungeprüfte Uploads{/block}

{if $flash}
  <div class="alert alert-{$flash.type} alert-dismissible fade show" role="alert">
    {$flash.message}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
  </div>
{/if}

{block name="content"}
<div class="container mt-5">
  <h1>Ungeprüfte Uploads</h1>

  <form class="row g-3 mt-3 mb-4" method="get" action="{$base_url}/pending_uploads.php">
    <div class="col-md-3">
      <label class="form-label" for="title">Titel</label>
      <input type="text" class="form-control" id="title" name="title" value="{$filters.title|escape}">
    </div>
    <div class="col-md-3">
      <label class="form-label" for="filename">Dateiname</label>
      <input type="text" class="form-control" id="filename" name="filename" value="{$filters.filename|escape}">
    </div>
    <div class="col-md-3">
      <label class="form-label" for="course_name">Kurs</label>
      <input type="text" class="form-control" id="course_name" name="course_name" value="{$filters.course_name|escape}">
    </div>
    <div class="col-md-3">
      <label class="form-label" for="username">Uploader</label>
      <input type="text" class="form-control" id="username" name="username" value="{$filters.username|escape}">
    </div>
    <div class="col-md-3">
      <label class="form-label" for="from_date">Von</label>
      <input type="date" class="form-control" id="from_date" name="from_date" value="{$filters.from_date|escape}">
    </div>
    <div class="col-md-3">
      <label class="form-label" for="to_date">Bis</label>
      <input type="date" class="form-control" id="to_date" name="to_date" value="{$filters.to_date|escape}">
    </div>
    <div class="col-12 d-flex justify-content-end">
      <button type="submit" class="btn btn-primary me-2">Filtern</button>
      <a href="{$base_url}/pending_uploads.php" class="btn btn-outline-secondary me-2">Zurücksetzen</a>
      <button type="submit" name="export" value="csv" class="btn btn-success">Exportieren als CSV</button>
    </div>
  </form>

  {if $pending_uploads|@count > 0}
    <form method="post" action="{$base_url}/pending_uploads.php">
      <div class="table-responsive card shadow-sm">
        <table class="table table-striped table-bordered align-middle mb-0">
          <thead class="table-dark text-center">
            <tr>
              <th>#</th>
              <th>Dateiname</th>
              <th>Titel</th>
              <th>Kurs</th>
              <th>Uploader</th>
              <th>Hochgeladen am</th>
              <th>Grund (bei Ablehnung)</th>
              <th>Aktionen</th>
            </tr>
          </thead>
          <tbody>
            {foreach $pending_uploads as $index => $upload}
            <tr>
              <td>{$index + 1 + ($currentPage - 1) * 25}</td>
              <td>{$upload.stored_name|escape}</td>
              <td>{$upload.title|escape}</td>
              <td>{$upload.course_name|escape}</td>
              <td>{$upload.username|escape}</td>
              <td>{$upload.uploaded_at|date_format:"%d.%m.%Y %H:%M"}</td>
              <td>
                <input type="text" name="note" class="form-control form-control-sm" placeholder="Optionaler Ablehnungsgrund">
              </td>
              <td class="text-center">
                <input type="hidden" name="upload_id" value="{$upload.id}">
                <div class="btn-group" role="group">
                  <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Freigeben</button>
                  <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Ablehnen</button>
                  <a href="{$base_url}/uploads/{$upload.stored_name|escape}" target="_blank" class="btn btn-outline-secondary btn-sm">Ansehen</a>
                </div>
              </td>
            </tr>
            {/foreach}
          </tbody>
        </table>
      </div>
    </form>

    {if $totalPages > 1}
    <nav aria-label="Seiten-Navigation">
      <ul class="pagination justify-content-center mt-4">
        {section name=page loop=$totalPages start=1 step=1}
          {assign var=pageNum value=$smarty.section.page.index}
          <li class="page-item {if $pageNum+1 == $currentPage}active{/if}">
            <a class="page-link" href="?page={$pageNum+1}
              {if $filters.title}&title={$filters.title|escape}{/if}
              {if $filters.filename}&filename={$filters.filename|escape}{/if}
              {if $filters.course_name}&course_name={$filters.course_name|escape}{/if}
              {if $filters.username}&username={$filters.username|escape}{/if}
              {if $filters.from_date}&from_date={$filters.from_date|escape}{/if}
              {if $filters.to_date}&to_date={$filters.to_date|escape}{/if}">
              {$pageNum+1}
            </a>
          </li>
        {/section}
      </ul>
    </nav>
    {/if}
  {else}
    <div class="alert alert-info">Keine ungeprüften Uploads gefunden.</div>
  {/if}
</div>
{/block}
