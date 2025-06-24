{extends file="./layouts/layout.tpl"}
{block name="title"}Kursvorschläge{/block}

{block name="content"}
<div class="container mt-5">
  <h1 class="mb-4">Offene Kursvorschläge</h1>

  {if $flash}
    <div class="alert alert-{$flash.type}">{$flash.message|escape}</div>
  {/if}

  <form class="row g-3 mb-4" method="get" action="{url path='pending_courses'}">
    <div class="col-md-3">
      <label for="username" class="form-label">Benutzername</label>
      <input type="text" class="form-control" id="username" name="username" value="{$filters.username|escape}">
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
      <a href="{url path='pending_courses'}" class="btn btn-outline-secondary me-2">Zurücksetzen</a>
      <button type="submit" name="export" value="csv" class="btn btn-success">Exportieren als CSV</button>
    </div>
  </form>

  {if $pending_courses|@count > 0}
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-dark text-center">
              <tr>
                <th>#</th>
                <th>Kursname</th>
                <th>Benutzer</th>
                <th>E-Mail</th>
                <th>Vorgeschlagen am</th>
                <th>Aktion</th>
              </tr>
            </thead>
            <tbody>
              {foreach $pending_courses as $i => $c}
              <tr>
                <td>{$i+1}</td>
                <td>{$c.course_name|escape}</td>
                <td>{$c.username|escape}</td>
                <td>{$c.email|escape}</td>
                <td>{$c.suggested_at|date_format:"%d.%m.%Y %H:%M"}</td>
                <td>
                  <form method="post" action="{url path='pending_courses'}" class="d-flex flex-column align-items-start">
                    <input type="hidden" name="suggestion_id" value="{$c.id}">
                    
                    <div class="mb-2 w-100">
                      <textarea name="rejection_reason" class="form-control" rows="2" placeholder="Ablehnungsgrund... (Pflicht bei Ablehnung)"></textarea>
                    </div>

                    <div class="btn-group">
                      <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Annehmen</button>
                      <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm reject-btn">Ablehnen</button>
                    </div>
                  </form>
                </td>
              </tr>
              {/foreach}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  {else}
    <div class="alert alert-info">Keine offenen Kursvorschläge gefunden.</div>
  {/if}
</div>

<script>
  // Pflichtfeldprüfung für Ablehnung
  document.querySelectorAll('.reject-btn').forEach(button => {
    button.addEventListener('click', function (e) {
      const form = this.closest('form');
      const reason = form.querySelector('textarea[name="rejection_reason"]').value.trim();
      if (!reason) {
        e.preventDefault();
        alert('Bitte gib einen Ablehnungsgrund an.');
      }
    });
  });
</script>
{/block}
