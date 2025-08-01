{extends file="./layouts/layout.tpl"}
{block name="title"}Gruppe: {$group.name|escape}{/block}

{block name="content"}
<div class="container mt-5">
  {if $error}<div class="alert alert-danger">{$error|escape}</div>{/if}
  {if $success}<div class="alert alert-success">{$success|escape}</div>{/if}

  <div class="text-center mb-3">
    {if $group.group_picture}
      <img src='{url file="group_pictures/{$group.group_picture|escape:'url'}"}' alt="Gruppenbild" class="rounded-circle shadow mb-2" style="max-width:150px;object-fit:cover;">
    {else}
      <img src="{$base_url}/assets/default_group.png" alt="Gruppenbild" class="rounded-circle shadow mb-2" style="max-width:150px;object-fit:cover;">
    {/if}
    {if $myRole === 'admin'}
      <form method="post" enctype="multipart/form-data" class="mt-2">
        <input type="hidden" name="csrf_token" value="{$csrf_token}">
        <input type="file" name="group_picture" accept="image/*" class="form-control mb-2">
        <button name="update_picture" class="btn btn-outline-primary btn-sm">Bild aktualisieren</button>
      </form>
    {/if}
  </div>

  <h1 class="mb-4">{$group.name|escape}</h1>
  {if $group.join_type === 'invite'}
    <p class="text-muted">Beitritt nur per Einladung.</p>
  {elseif $group.join_type === 'code'}
    <p class="text-muted">Beitritt per Einladungscode oder Einladung.</p>
    {if $myRole === 'admin'}
      <div class="alert alert-info d-flex align-items-center">
        <span>Einladungscode: <code id="inviteCode">{$group.invite_code|escape}</code></span>
        <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="copyInviteCode" data-code="{$group.invite_code|escape}">Kopieren</button>
      </div>
    {/if}
  {/if}

  <h3>Gruppentermine</h3>
  {if $events|@count}
    <ul class="list-group mb-3">
      {foreach $events as $ev}
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            {$ev.title|escape}
            <div class="text-muted small">
              {$ev.event_date|date_format:"%d.%m.%Y"}
              {if $ev.event_time}
                &nbsp;{$ev.event_time|date_format:"%H:%M"} Uhr
              {/if}
              {if $ev.repeat_interval !== 'none'}
                &middot; {$ev.repeat_label|escape}
              {/if}
            </div>
          </span>
          {if $myRole === 'admin'}
            <form method="post" class="ms-2" onsubmit="return confirm('Termin wirklich löschen?');">
              <input type="hidden" name="csrf_token" value="{$csrf_token}">
              <input type="hidden" name="event_id" value="{$ev.id}">
              <button type="submit" name="delete_event" class="btn btn-sm btn-outline-danger">Löschen</button>
            </form>
          {/if}
        </li>
      {/foreach}
    </ul>
  {else}
    <p class="text-muted">Keine Termine vorhanden.</p>
  {/if}

  {if $myRole === 'admin'}
    <h4>Neuen Termin erstellen</h4>
    <form method="post" class="mb-4">
      <input type="hidden" name="csrf_token" value="{$csrf_token}">
      <div class="row g-2">
        <div class="col-md-6"><input type="text" name="event_title" class="form-control" placeholder="Titel" required></div>
        <div class="col-md-3"><input type="date" name="event_date" class="form-control" required></div>
        <div class="col-md-3"><input type="time" name="event_time" class="form-control"></div>
      </div>
      <div class="row g-2 mt-2">
        <div class="col-md-6">
          <select name="event_repeat" class="form-select">
            <option value="none">Einmalig</option>
            <option value="weekly">Wöchentlich</option>
            <option value="biweekly">Alle 2 Wochen</option>
            <option value="monthly">Monatlich</option>
          </select>
        </div>
      </div>
      <button name="create_event" class="btn btn-primary mt-2">Erstellen</button>
    </form>
  {/if}

  {if $myRole === 'none'}
    {if $group.join_type === 'open'}
      <form method="post">
        <input type="hidden" name="csrf_token" value="{$csrf_token}">
        <button name="join_group" class="btn btn-primary">Beitreten</button>
      </form>
    {elseif $group.join_type === 'code'}
      <form method="post" class="mb-3">
        <input type="hidden" name="csrf_token" value="{$csrf_token}">
        <div class="mb-2"><input type="text" name="invite_code" class="form-control" placeholder="Einladungscode" required></div>
        <button name="join_group" class="btn btn-primary">Beitreten</button>
      </form>
    {else}
      
    {/if}
  {else}
    <form method="post" class="d-inline">
      <input type="hidden" name="csrf_token" value="{$csrf_token}">
      <button name="leave_group" class="btn btn-outline-warning">Verlassen</button>
    </form>
    {if $myRole === 'admin'}
      <form method="post" class="d-inline ms-2">
        <input type="hidden" name="csrf_token" value="{$csrf_token}">
        <button name="delete_group" class="btn btn-outline-danger">Gruppe löschen</button>
      </form>
    {/if}
  {/if}

  <hr/>

  <h3>Mitglieder</h3>
  <ul class="list-group mb-4">
    {foreach $members as $m}
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <span>
          {$m.username|escape}
          {if $m.role}
            <span class="badge bg-secondary ms-1">{$m.role|escape}</span>
          {/if}
        </span>
        {if $myRole === 'admin' && $m.user_id != $smarty.session.user_id}
          <form method="post" style="margin:0">
            <input type="hidden" name="csrf_token" value="{$csrf_token}">
            <input type="hidden" name="user_id" value="{$m.user_id}">
            <button name="remove_member" class="btn btn-sm btn-outline-danger">Entfernen</button>
          </form>
        {/if}
      </li>
    {/foreach}
  </ul>

  {if $myRole === 'admin'}
    <h4>Benutzer einladen</h4>
    <form method="post" class="mb-4">
      <input type="hidden" name="csrf_token" value="{$csrf_token}">
      <div class="mb-3">
        <input type="text" name="invite_username" class="form-control" placeholder="Benutzername" required>
      </div>
      <button name="invite_user" class="btn btn-primary">Einladen</button>
    </form>
  {/if}

  <h3>Materialien</h3>
  {if $uploads|@count}
    <ul class="list-group mb-3">
      {foreach $uploads as $u}
        <li class="list-group-item d-flex justify-content-between">
          <span>
            <a href="{url path='view_pdf' file=$u.stored_name}" target="_blank">{$u.title|escape}</a>
          </span>
          <span>
            <a href="{url path='download' id=$u.id}" download class="btn btn-sm btn-outline-primary{if $myRole === 'admin'} me-2{/if}">Herunterladen</a>
            {if $myRole === 'admin'}
              <form method="post" class="d-inline" onsubmit="return confirm('Upload wirklich löschen?');">
                <input type="hidden" name="csrf_token" value="{$csrf_token}">
                <input type="hidden" name="upload_id" value="{$u.id}">
                <button type="submit" name="delete_upload" class="btn btn-sm btn-danger">Löschen</button>
              </form>
            {/if}
          </span>
        </li>
      {/foreach}
    </ul>
  {else}
    <p class="text-muted">Keine Materialien vorhanden.</p>
  {/if}

{if $myRole !== 'none'}
    <form method="post">
      <input type="hidden" name="csrf_token" value="{$csrf_token}">
      <button name="upload_group" class="btn btn-success">Für Lerngruppe hochladen</button>
    </form>
{/if}
</div>
{/block}

{block name="scripts"}
<script>
  document.getElementById('copyInviteCode')?.addEventListener('click', function() {
    const code = this.dataset.code;
    navigator.clipboard.writeText(code).then(() => {
      this.textContent = 'Kopiert!';
      setTimeout(() => { this.textContent = 'Kopieren'; }, 2000);
    });
  });
</script>
{/block}
