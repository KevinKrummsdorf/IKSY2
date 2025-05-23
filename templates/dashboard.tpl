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
    {* Abschnitt 1 : Login-Logs *}
    <section class="my-5">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="mb-0">Login Logs</h2>
        <button class="btn btn-outline-secondary btn-sm toggle-collapse-icon"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapseLoginLogs"
                aria-expanded="false" {* Standardmäßig auf false setzen, da es am Anfang zugeklappt ist *}
                aria-controls="collapseLoginLogs">
          <i class="bi bi-chevron-down"></i>
        </button>
      </div>

      <div class="collapse" id="collapseLoginLogs"> {* Dies ist das EINZIGE DIV mit dieser ID *}
        {if $login_logs|count > 0}
          <div class="card card-body">
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
                {foreach $login_logs as $i => $log}
                  <tr>
                    <td>{$i+1}</td>
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
            <div class="mt-3 text-end">
              <a href="login_logs.php" class="btn btn-sm btn-primary">Alle Login-Logs anzeigen</a>
            </div>
          </div>
        {else}
          <div class="alert alert-info">Keine Login-Logs vorhanden.</div>
        {/if}
      </div>
    </section>

    {* Abschnitt 2 : Captcha-Logs *}
    <section class="my-5">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="mb-0">Captcha Logs</h2>
        <button class="btn btn-outline-secondary btn-sm toggle-collapse-icon"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapseCaptchaLogs"
                aria-expanded="false"
                aria-controls="collapseCaptchaLogs">
          <i class="bi bi-chevron-down"></i>
        </button>
      </div>

      <div class="collapse" id="collapseCaptchaLogs">
        {if $captcha_logs|count > 0}
          <div class="card card-body">
            <table class="table table-striped table-bordered align-middle">
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
                {foreach $captcha_logs as $i => $log}
                  <tr>
                    <td>{$i+1}</td>
                    <td>{$log.id}</td>
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
            <div class="mt-3 text-end">
              <a href="captcha_logs.php" class="btn btn-sm btn-primary">Alle Captcha-Logs anzeigen</a>
            </div>
          </div>
        {else}
          <div class="alert alert-info">Keine Captcha-Logs vorhanden.</div>
        {/if}
      </div>
    </section>
    {* Abschnitt 3 : locked User *}
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
    {* Abschnitt 4 : Kontaktanfragen *}
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
    {* Abschnitt 5 : Upload-Logs *}
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
                  <th>User ID</th>
                  <th>Dateiname</th>
                  <th>Zeitpunkt</th>
                </tr>
              </thead>
              <tbody>
                {foreach $upload_logs as $i => $log}
                  <tr>
                    <td>{$i+1}</td>
                    <td>{$log.user_id}</td>
                    <td>{$log.stored_name|escape}</td>
                    <td>{$log.uploaded_at}</td>
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