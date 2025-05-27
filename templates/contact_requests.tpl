{extends file="./layouts/layout.tpl"}
{block name="title"}Kontaktanfragen{/block}

{block name="content"}
<div class="container mt-5">
  <h1>Kontaktanfragen</h1>

  {if isset($flash)}
    <div class="alert alert-{$flash.type} alert-dismissible fade show mt-3" role="alert">
      {$flash.message|escape}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  {/if}

  <!-- Filterformular -->
  <form class="row g-3 mt-3 mb-4" method="get" action="{$base_url}/contact_request.php">
    <div class="col-md-3">
      <label for="name" class="form-label">Name</label>
      <input type="text" class="form-control" id="name" name="name" value="{$filters.name|escape}">
    </div>
    <div class="col-md-3">
      <label for="email" class="form-label">E-Mail</label>
      <input type="email" class="form-control" id="email" name="email" value="{$filters.email|escape}">
    </div>
    <div class="col-md-3">
      <label for="subject" class="form-label">Betreff</label>
      <input type="text" class="form-control" id="subject" name="subject" value="{$filters.subject|escape}">
    </div>
    <div class="col-md-3">
      <label for="from" class="form-label">Von</label>
      <input type="date" class="form-control" id="from" name="from" value="{$filters.from|escape}">
    </div>
    <div class="col-md-3">
      <label for="to" class="form-label">Bis</label>
      <input type="date" class="form-control" id="to" name="to" value="{$filters.to|escape}">
    </div>
    <div class="col-12 d-flex justify-content-end">
      <button type="submit" class="btn btn-primary me-2">Filtern</button>
      <a href="{$base_url}/contact_request.php" class="btn btn-outline-secondary me-2">Zurücksetzen</a>
      <button type="submit" name="export" value="csv" class="btn btn-success">Exportieren als CSV</button>
    </div>
  </form>

  <!-- Tabelle -->
  {if $contact_requests|@count > 0}
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered align-middle table-striped">
            <thead class="table-dark text-center">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>E-Mail</th>
                <th>Betreff</th>
                <th>Nachricht</th>
                <th>Status</th>
                <th>Antworten</th>
                <th>Aktionen</th>
              </tr>
            </thead>
            <tbody>
              {foreach $contact_requests as $index => $r}
              <tr>
                <td>{$index+1}</td>
                <td>{$r.name|escape}</td>
                <td>{$r.email|escape}</td>
                <td>{$r.subject|escape}</td>
                <td style="max-width: 250px;">{$r.message|truncate:200:"..."|escape}</td>

                <!-- Status-Wechsel -->
                <td>
                  <form method="post" action="{$base_url}/contact_request.php" class="d-flex">
                    <input type="hidden" name="status_contact_id" value="{$r.contact_id}">
                    <select name="new_status" class="form-select form-select-sm me-2">
                      <option value="offen" {if $r.status == 'offen'}selected{/if}>Offen</option>
                      <option value="in_bearbeitung" {if $r.status == 'in_bearbeitung'}selected{/if}>In Bearbeitung</option>
                      <option value="geschlossen" {if $r.status == 'geschlossen'}selected{/if}>Geschlossen</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-primary">Speichern</button>
                  </form>
                </td>

                <!-- Antwortformular -->
                <td>
                  <form method="post" action="{$base_url}/contact_request.php">
                    <input type="hidden" name="reply_contact_id" value="{$r.contact_id}">
                    <textarea name="reply_text" rows="2" class="form-control mb-2" placeholder="Antwort eingeben..." required></textarea>
                    <button type="submit" class="btn btn-sm btn-outline-success w-100">Antwort senden</button>
                  </form>
                </td>

                <!-- Weitere Aktionen -->
                <td class="text-center">
                  <span class="badge bg-info">{$r.status|capitalize}</span><br>
                  <small>{$r.created_at|date_format:"%d.%m.%Y %H:%M"}</small>
                </td>
              </tr>
              {/foreach}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  {else}
    <div class="alert alert-info mt-4">Keine Kontaktanfragen gefunden.</div>
  {/if}
  <div class="mt-4">
    <a href="dashboard.php" class="btn btn-sm btn-primary">Zurück zum Dashboard</a>
  </div>

</div>
{/block}
