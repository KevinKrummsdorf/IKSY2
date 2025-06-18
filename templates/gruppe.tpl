{extends file="./layouts/layout.tpl"}
{block name="title"}Gruppe: {$group.name|escape}{/block}

{block name="content"}
<div class="container mt-5">
  {if $error}<div class="alert alert-danger">{$error}</div>{/if}
  {if $success}<div class="alert alert-success">{$success}</div>{/if}

  <h1 class="mb-4">{$group.name|escape}</h1>

  {if $myRole === 'none'}
    <form method="post"><button name="join_group" class="btn btn-primary">Beitreten</button></form>
  {else}
    <form method="post" class="d-inline"><button name="leave_group" class="btn btn-outline-warning">Verlassen</button></form>
    {if $myRole === 'admin'}
      <form method="post" class="d-inline ms-2">
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
          {$u.title|escape}
          <a href="{$base_url}/download.php?id={$u.id}" download class="btn btn-sm btn-outline-primary">Herunterladen</a>
        </li>
      {/foreach}
    </ul>
  {else}
    <p class="text-muted">Keine Materialien vorhanden.</p>
  {/if}

  {if $myRole !== 'none'}
    <form method="post"><button name="upload_group" class="btn btn-success">Für Lerngruppe hochladen</button></form>
  {/if}
</div>
{/block}
