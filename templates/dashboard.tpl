{extends file="./layouts/layout.tpl"}

{block name="title"}Dashboard{/block}

{block name="content"}
<div class="container mt-5">
  <h1>
    Hallo, {$username|escape}!<br>
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

  {if !$isAdmin}
    {* Eigene Uploads *}
    <section class="my-4">
      <h2 class="mb-2">Deine Uploads</h2>
      <div>
        {if $user_uploads|@count > 0}
          <div id="myUploadsCarousel" class="carousel slide" data-bs-interval="false">
            <div class="carousel-inner">
              {foreach $user_uploads as $index => $u}
                <div class="carousel-item {if $index == 0}active{/if}">
                  <div class="pdf-slide">
                  <a href="{url path='view_pdf' file=$u.stored_name}" target="_blank" class="pdf-link">
                      {if $u.type == 'image'}
                        <img src='{url file="{$u.stored_name|escape:'url'}"}' alt="{$u.title|escape}" class="img-fluid mb-2" style="max-height:160px;">
                      {else}
                        <span class="material-symbols-outlined">picture_as_pdf</span>
                      {/if}
                      <h5>{$u.title|escape:'html'}</h5>
                      <p>{$u.course_name|escape:'html'} – {$u.uploaded_at|date_format:"%d.%m.%Y %H:%M"}</p>
                    </a>
                  </div>
                </div>
              {/foreach}
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#myUploadsCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Zurück</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#myUploadsCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Weiter</span>
            </button>
          </div>
        {else}
          <div class="alert alert-info">Du hast noch keine Materialien hochgeladen.</div>
        {/if}
      </div>
      <div class="mt-3 text-end">
        <a href="{url path='my_uploads'}" class="btn btn-primary">Alle Uploads ansehen</a>
      </div>
    </section>

  <div class="row row-cols-1 g-4 my-4">
  <div class="col-12 col-md-4">
    <section class="h-100 p-3 border rounded" id="learn-timer-section">
      <h2 class="h4 mb-3 text-center">Lerntimer</h2>
      <div class="row g-2 align-items-center">
        <div class="col-auto">
          <label for="timerDuration" class="col-form-label">Dauer (Minuten)</label>
        </div>
        <div class="col-auto">
          <input type="number" class="form-control" id="timerDuration" min="1" value="30">
        </div>
        <div class="col-auto">
          <button type="button" class="btn btn-primary" id="startTimerBtn">Start</button>
        </div>
      </div>
    </section>
  </div>

  <div class="col-12 col-md-8">
    <section class="h-100 p-3 border rounded">
      <h2 class="h4 mb-3 text-center">Kalender</h2>
      {include file='partials/today_tasks.tpl'}
      {include file='partials/calendar_month.tpl'}
    </section>
  </div>
  </div>
  {/if}

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
          <span class="material-symbols-outlined collapse-icon">expand_more</span>
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
              <a href="{url path='locked_users'}" class="btn btn-sm btn-primary">Zur Benutzerverwaltung</a>
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
          <span class="material-symbols-outlined collapse-icon">expand_more</span>
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
              <a href="{url path='contact_request'}" class="btn btn-sm btn-primary">Alle Kontaktanfragen anzeigen</a>
            </div>
          </div>
        {else}
          <div class="alert alert-info">Keine Kontaktanfragen vorhanden.</div>
        {/if}
      </div>
    </section>

    {* Abschnitt 3 : Ungeprüfte Uploads *}

    <section class="my-5">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="mb-0">Ungeprüfte Uploads</h2>
        <button class="btn btn-outline-secondary btn-sm toggle-collapse-icon"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapsePendingUploads"
                aria-expanded="false"
                aria-controls="collapsePendingUploads">
          <span class="material-symbols-outlined collapse-icon">expand_more</span>
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
            <a href="{url path='pending_uploads'}" class="btn btn-sm btn-primary">Alle ungeprüften Uploads anzeigen</a>
          </div>
        {else}
          <div class="alert alert-info">Keine neuen Uploads vorhanden.</div>
        {/if}
      </div>
    </section>

    {* Abschnitt 4 : Offene Kursvorschläge *}
    <section class="my-5">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="mb-0">Offene Kursvorschläge</h2>
        <button class="btn btn-outline-secondary btn-sm toggle-collapse-icon"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapsePendingCourses"
                aria-expanded="false"
                aria-controls="collapsePendingCourses">
          <span class="material-symbols-outlined collapse-icon">expand_more</span>
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
            <a href="{url path='pending_courses'}" class="btn btn-sm btn-primary">Alle offenen Kursvorschläge anzeigen</a>
          </div>
        {else}
          <div class="alert alert-info">Keine offenen Kursvorschläge vorhanden.</div>
        {/if}
      </div>
    </section>
  {/if}

</div>

<!-- Lerntimer Modal -->
<div class="modal fade" id="timerModal" tabindex="-1" aria-labelledby="timerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title" id="timerModalLabel">Lerntimer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
      <div class="modal-body">
        <div id="timerDisplay" class="display-1 fw-bold">00:00</div>
        <p id="timerMessage" class="h4 mt-3"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary d-none" id="closeTimerBtn" data-bs-dismiss="modal">Beenden</button>
      </div>
    </div>
  </div>
</div>
{/block}

{block name="scripts"}
<script src="{$base_url}/js/dashboard.js"></script>
{/block}