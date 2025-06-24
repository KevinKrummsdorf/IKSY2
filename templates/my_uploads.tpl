{extends file="./layouts/layout.tpl"}
{block name="title"}Meine Uploads{/block}

{block name="content"}
<div class="container mt-5">
  <h1>Meine Uploads</h1>

  <form class="row g-3 mt-3 mb-4" method="get" action="{$base_url}/my_uploads.php">
    <div class="col-md-3">
      <label class="form-label" for="title">Titel</label>
      <input type="text" class="form-control" id="title" name="title" list="title_suggestions" value="{$filters.title|escape}">
    </div>
    <div class="col-md-3">
      <label class="form-label" for="filename">Dateiname</label>
      <input type="text" class="form-control" id="filename" name="filename" list="filename_suggestions" value="{$filters.filename|escape}">
    </div>
    <div class="col-md-3">
      <label class="form-label" for="course_name">Kurs</label>
      <input type="text" class="form-control" id="course_name" name="course_name" list="course_suggestions" value="{$filters.course_name|escape}">
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
      <a href="{$base_url}/my_uploads.php" class="btn btn-outline-secondary">Zurücksetzen</a>
    </div>
  </form>
  <datalist id="title_suggestions">
    {foreach $suggestions.titles as $t}
      <option value="{$t|escape}"></option>
    {/foreach}
  </datalist>
  <datalist id="filename_suggestions">
    {foreach $suggestions.filenames as $f}
      <option value="{$f|escape}"></option>
    {/foreach}
  </datalist>
  <datalist id="course_suggestions">
    {foreach $suggestions.course_names as $c}
      <option value="{$c|escape}"></option>
    {/foreach}
  </datalist>

  {if $uploads|@count > 0}
    <div class="table-responsive card shadow-sm">
      <table class="table table-striped table-bordered align-middle mb-0">
        <thead class="table-dark text-center">
          <tr>
            <th>#</th>
            <th>Dateiname</th>
            <th>Titel</th>
            <th>Kurs</th>
            <th>Hochgeladen am</th>
            <th>Status</th>
            <th>Aktionen</th>
          </tr>
        </thead>
        <tbody>
        {foreach $uploads as $index => $u}
          <tr>
            <td>{$index + 1 + ($currentPage - 1) * 25}</td>
            <td>{$u.stored_name|escape}</td>
            <td>{$u.title|escape}</td>
            <td>{$u.course_name|escape}</td>
            <td>{$u.uploaded_at|date_format:"%d.%m.%Y %H:%M"}</td>
            <td>
                {if $u.is_approved}
                  <span class="badge bg-success">Freigegeben</span>
                {elseif $u.is_rejected}
                  <span class="badge bg-danger">Abgelehnt</span>
                  <a href="#" class="text-info ms-1 info-note" data-note="{$u.rejection_note|escape}">
                    <span class="material-symbols-outlined" style="font-size:1rem">info</span>
                  </a>
                {else}
                  <span class="badge bg-warning text-dark">Wartet auf Freigabe</span>
                {/if}
              </td>
              <td class="text-center">
                <a href="{$base_url}/download.php?id={$u.id}" class="btn btn-sm btn-primary me-1">Download</a>
                <a href="{$base_url}/view_pdf.php?file={$u.stored_name|escape:'url'}" target="_blank" class="btn btn-sm btn-secondary me-1">Ansehen</a>
                <form method="post" action="{$base_url}/delete_upload.php" class="d-inline" onsubmit="return confirm('Upload wirklich löschen?');">
                  <input type="hidden" name="csrf_token" value="{$csrf_token}">
                  <input type="hidden" name="upload_id" value="{$u.id}">
                  <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                </form>
              </td>
          </tr>
        {/foreach}
        </tbody>
      </table>
    </div>

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
    <div class="alert alert-info">Keine Uploads gefunden.</div>
  {/if}
</div>
<script>
  document.querySelectorAll('.info-note').forEach(el => {
    el.addEventListener('click', e => {
      e.preventDefault();
      const note = el.dataset.note || 'Kein Grund angegeben';
      alert('Ablehnungsgrund: ' + note);
    });
  });
</script>
{/block}
