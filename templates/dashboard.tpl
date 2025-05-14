{extends file="./layouts/layout.tpl"}

{block name="title"}Dashboard{/block}

{if isset($flash)}
  <div class="alert alert-{$flash.type|default:'info'} alert-dismissible fade show" role="alert">
    {$flash.message}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
  </div>
{/if}


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
    {* Abschnitt 1: Login-Logs *}
    <section class="my-5">
      <h2>
        <button class="btn btn-link p-0" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#collapseLoginLogs" 
                aria-expanded="true" 
                aria-controls="collapseLoginLogs">
          Letzte Login-Logs
        </button>
      </h2>
      <div class="collapse show" id="collapseLoginLogs">
        {if $login_logs|count > 0}
          <div class="table-responsive shadow-sm">
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
              <a href="login_logs.php" class="btn btn-sm btn-primary">
                Alle Login-Logs anzeigen
              </a>
            </div>
          </div>
        {else}
          <div class="alert alert-info">Keine Login-Logs vorhanden.</div>
        {/if}
      </div>
    </section>

    {* Abschnitt 2: reCAPTCHA-Protokolle *}
    <section class="my-5">
      <h2>
        <button class="btn btn-link p-0" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#collapseCaptchaLogs" 
                aria-expanded="false" 
                aria-controls="collapseCaptchaLogs">
          reCAPTCHA-Protokolle
        </button>
      </h2>
      <div class="collapse" id="collapseCaptchaLogs">
        {if $captcha_logs|count > 0}
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
              <a href="captcha_logs.php" class="btn btn-sm btn-primary">
                Alle reCAPTCHA-Logs anzeigen
              </a>
            </div>
          </div>
        {else}
          <div class="alert alert-info">Keine reCAPTCHA-Logs vorhanden.</div>
        {/if}
      </div>
    </section>
  {/if}

  {if $isAdmin || $isMod}
    {* Abschnitt 3: Kontaktanfragen *}
    <section class="my-5">
      <h2>
        <button class="btn btn-link p-0" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#collapseContactRequests" 
                aria-expanded="false" 
                aria-controls="collapseContactRequests">
          Kontaktanfragen
        </button>
      </h2>
      <div class="collapse" id="collapseContactRequests">
        {if $contact_requests|count > 0}
          <div class="table-responsive shadow-sm">
            <table class="table table-bordered table-hover align-middle">
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
              <a href="contact_request.php" class="btn btn-sm btn-primary">
                Alle Kontaktanfragen anzeigen
              </a>
            </div>
          </div>
        {else}
          <div class="alert alert-info">Keine Kontaktanfragen vorhanden.</div>
        {/if}
      </div>
    </section>

    {* Abschnitt 4: Upload-Logs *}
    <section class="my-5">
      <h2>
        <button class="btn btn-link p-0"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapseUploadLogs"
                aria-expanded="false"
                aria-controls="collapseUploadLogs">
          Upload-Logs
        </button>
      </h2>
      <div class="collapse" id="collapseUploadLogs">
        {if $upload_logs|count > 0}
          <div class="table-responsive shadow-sm">
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
              <a href="upload_logs.php" class="btn btn-sm btn-primary">
                Alle Upload-Logs anzeigen
              </a>
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
