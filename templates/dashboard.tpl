{extends file="./layouts/layout.tpl"}

{block name="title"}Dashboard{/block}

{block name="content"}
<div class="container mt-5">
  <h1>
    Hallo, {$username}!<br>
    <small class="text-muted">
        Rolle:
        {if $isAdmin}
            Administrator
        {elseif $isMod}
            Moderator
        {else}
            Benutzer
        {/if}
    </small>
  </h1>

  {if $isAdmin}
    {* Abschnitt 1 : locked User *}
    <section class="my-5">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="mb-0">Gesperrte Benutzer</h2>
        <button class="btn btn-outline-secondary btn-sm toggle-collapse-icon"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapseLockedUsers"
                aria-expanded="false"
                aria-controls="collapseLockedUsers">
          <i class="bi bi-chevron-down"></i>
        </button>
      </div>

      <div class="collapse" id="collapseLockedUsers">
        {if $locked_users|@count > 0}
          <div class="card card-body">
            <table class="table table-striped table-bordered align-middle">
              <thead class="table-dark text-center">
                <tr>
                  <th>#</th>
                  <th>User ID</th>
                  <th>Benutzername</th>
                  <th>E-Mail</th>
                  <th>Fehlversuche</th>
                </tr>
              </thead>
              <tbody>
                {foreach $locked_users as $i => $user}
                  <tr>
                    <td>{$i+1}</td>
                    <td>{$user.id}</td>
                    <td>{$user.username|escape}</td>
                    <td><a href="mailto:{$user.email|escape}">{$user.email|escape}</a></td>
                    <td>{$user.failed_attempts}</td>
                  </tr>
                {/foreach}
              </tbody>
            </table>
            <div class="mt-3 text-end">
              <a href="locked_users.php" class="btn btn-sm btn-primary">Zur Benutzerverwaltung</a>
            </div>
          </div>
        {else}
          <div class="alert alert-success">Es sind aktuell keine Benutzerkonten gesperrt.</div>
        {/if}
      </div>
    </section>
  {/if}

  {if $isAdmin || $isMod}
    {* Abschnitt 2 : Kontaktanfragen *}
    <section class="my-5">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="mb-0">Kontaktanfragen</h2>
        <button class="btn btn-outline-secondary btn-sm toggle-collapse-icon"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapseContactRequests"
                aria-expanded="false"
                aria-controls="collapseContactRequests">
          <i class="bi bi-chevron-down"></i>
        </button>
      </div>

      <div class="collapse" id="collapseContactRequests">
        {if $contact_requests|count > 0}
          <div class="card card-body">
            <table class="table table-striped table-bordered align-middle">
              <thead class="table-dark text-center">
                <tr>
                  <th>Kontakt-ID</th>
                  <th>Name</th>
                  <th>E-Mail</th>
                  <th>Betreff</th>
                  <th>Datum</th>
                </tr>
              </thead>
              <tbody>
                {foreach $contact_requests as $req}
                  <tr>
                    <td><code>{$req.contact_id|escape}</code></td>
                    <td>{$req.name|escape}</td>
                    <td><a href="mailto:{$req.email|escape}">{$req.email|escape}</a></td>
                    <td>{$req.subject|escape}</td>
                    <td>{$req.created_at}</td>
                  </tr>
                {/foreach}
              </tbody>
            </table>
            <div class="mt-3 text-end">
              <a href="contact_request.php" class="btn btn-sm btn-primary">Alle Kontaktanfragen anzeigen</a>
            </div>
          </div>
        {else}
          <div class="alert alert-info">Keine Kontaktanfragen vorhanden.</div>
        {/if}
      </div>
    </section>

    {* Abschnitt 3 : Upload-Logs *}
    <section class="my-5">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="mb-0">Upload-Logs</h2>
        <button class="btn btn-outline-secondary btn-sm toggle-collapse-icon"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapseUploadLogs"
                aria-expanded="false"
                aria-controls="collapseUploadLogs">
          <i class="bi bi-chevron-down"></i>
        </button>
      </div>

      <div class="collapse" id="collapseUploadLogs">
        {if $upload_logs|count > 0}
          <div class="card card-body">
            <table class="table table-striped table-bordered align-middle">
              <thead class="table-dark text-center">
                <tr>
                  <th>#</th>
                  <th>Username</th>
                  <th>Dateiname</th>
                  <th>Action</th>
                  <th>Grund</th>
                  <th>Zeitpunkt</th>
                </tr>
              </thead>
              <tbody>
                {foreach $upload_logs as $i => $log}
                  <tr>
                    <td>{$index + 1}</td>
                    <td>{$log.acted_by_user|default:'–'|escape}</td>
                    <td>{$log.stored_name|escape}</td>
                    <td>{$log.action|capitalize}</td>
                    <td>{$log.note|default:'–'|escape}</td>
                    <td>{$log.action_time|date_format:"%d.%m.%Y %H:%M"}</td>
                  </tr>
                {/foreach}
              </tbody>
            </table>
            <div class="mt-3 text-end">
              <a href="upload_logs.php" class="btn btn-sm btn-primary">Alle Upload-Logs anzeigen</a>
            </div>
          </div>
        {else}
          <div class="alert alert-info">Keine Upload-Logs vorhanden.</div>
        {/if}
      </div>
    </section>

  {* Abschnitt 4 : Ungeprüfte Uploads *}
<section class="my-5">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h2 class="mb-0">Ungeprüfte Uploads</h2>
    <button class="btn btn-outline-secondary btn-sm"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#collapsePendingUploads"
            aria-expanded="false"
            aria-controls="collapsePendingUploads">
      <i class="bi bi-chevron-down"></i>
    </button>
  </div>

  <div class="collapse" id="collapsePendingUploads">
    {if $pending_uploads|@count > 0}
      <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
          <thead class="table-dark text-center">
            <tr>
              <th>#</th>
              <th>Dateiname</th>
              <th>Titel</th>
              <th>Kurs</th>
              <th>Uploader</th>
              <th>Hochgeladen am</th>
            </tr>
          </thead>
          <tbody>
            {foreach $pending_uploads as $index => $u}
            <tr>
              <td>{$index+1}</td>
              <td>{$u.stored_name|escape}</td>
              <td>{$u.title|escape}</td>
              <td>{$u.course_name|escape}</td>
              <td>{$u.username|default:'Unbekannt'|escape}</td>
              <td>{$u.uploaded_at|date_format:"%d.%m.%Y %H:%M"}</td>
            </tr>
            {/foreach}
          </tbody>
        </table>
      </div>
      <div class="mt-3 text-end">
        <a href="{$base_url}/pending_uploads.php" class="btn btn-sm btn-primary">Alle ungeprüften Uploads anzeigen</a>
      </div>
    {else}
      <div class="alert alert-info">Keine neuen Uploads vorhanden.</div>
    {/if}
  </div>
</section>

{* Abschnitt 5 : Offene Kursvorschläge *}
<section class="my-5">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h2 class="mb-0">Offene Kursvorschläge</h2>
    <button class="btn btn-outline-secondary btn-sm"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#collapsePendingCourses"
            aria-expanded="false"
            aria-controls="collapsePendingCourses">
      <i class="bi bi-chevron-down"></i>
    </button>
  </div>

  <div class="collapse" id="collapsePendingCourses">
    {if $pending_courses|@count > 0}
      <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
          <thead class="table-dark text-center">
            <tr>
              <th>#</th>
              <th>Kursname</th>
              <th>Vorgeschlagen von</th>
              <th>Zeitpunkt</th>
            </tr>
          </thead>
          <tbody>
            {foreach $pending_courses as $index => $pc}
            <tr>
              <td>{$index+1}</td>
              <td>{$pc.course_name|escape}</td>
              <td>{$pc.username|escape}</td>
              <td>{$pc.suggested_at|date_format:"%d.%m.%Y %H:%M"}</td>
            </tr>
            {/foreach}
          </tbody>
        </table>
      </div>
      <div class="mt-3 text-end">
        <a href="{$base_url}/pending_courses.php" class="btn btn-sm btn-primary">Alle offenen Kursvorschläge anzeigen</a>
      </div>
    {else}
      <div class="alert alert-info">Keine offenen Kursvorschläge vorhanden.</div>
    {/if}
  </div>
</section>

  {/if}
</div>
{/block}

{block name="scripts"}
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-collapse-icon').forEach(btn => {
      const icon = btn.querySelector('i');
      const target = document.querySelector(btn.dataset.bsTarget);

      // Falls das Ziel-Element nicht existiert, beende die Funktion für diesen Button
      if (!target) return;

      // **Anpassung hier:** Prüfe den initialen Zustand des Collapse-Elements
      // Wenn es die Klasse 'show' hat, ist es offen.
      if (target.classList.contains('show')) {
        icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
      } else {
        // Andernfalls ist es geschlossen (oder wird geschlossen), also zeige den Pfeil nach unten
        icon.classList.replace('bi-chevron-up', 'bi-chevron-down');
      }

      // Event-Listener für das Bootstrap Collapse-Event
      target.addEventListener('show.bs.collapse', () => {
        icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
      });

      target.addEventListener('hide.bs.collapse', () => {
        icon.classList.replace('bi-chevron-up', 'bi-chevron-down');
      });
    });
  });
</script>
{/block}