{extends file="./layouts/layout.tpl"}
{block name="title"}Meine Lerngruppe{/block}

{block name="content"}
<div class="container mt-5">
  {if $group}
    <h1 class="mb-4 text-center">Meine Lerngruppe: {$group.name|escape}</h1>
  {else}
    <h1 class="mb-4 text-center">Meine Lerngruppe</h1>
  {/if}

  {if isset($error)}
    <div class="alert alert-danger">{$error}</div>
  {/if}
  {if isset($success)}
    <div class="alert alert-success">{$success}</div>
  {/if}

  {if $group}
    <!-- Gruppeninformationen anzeigen -->
    <div class="text-end mb-3">
      <form method="post">
        <button type="submit" name="leave_group" class="btn btn-outline-danger">
          Gruppe verlassen
        </button>
      </form>
    </div>
    <h3 class="mb-3">Mitglieder</h3>
    <div class="table-responsive card shadow-sm mb-4">
      <table class="table table-striped table-bordered align-middle mb-0">
        <thead class="table-dark text-center">
          <tr><th>Username</th><th>E-Mail</th></tr>
        </thead>
        <tbody>
          {foreach $members as $member}
            <tr>
              <td>{$member.username|escape}</td>
              <td>{$member.email|escape}</td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
    <h3 class="mb-2">Lernmaterialien</h3>
    {if $group_uploads|@count > 0}
      <ul class="list-group mb-4">
        {foreach $group_uploads as $upload}
          <li class="list-group-item d-flex justify-content-between align-items-center">
            {$upload.title|escape}
            <a href="download.php?id={$upload.id}" class="btn btn-sm btn-outline-primary" download>Herunterladen</a>
          </li>
        {/foreach}
      </ul>
    {else}
      <p class="text-muted">Keine Materialien vorhanden.</p>
    {/if}
  {else}
    <!-- Optionen zum Beitreten oder Erstellen einer Gruppe -->
    <p class="mb-4 text-center">Du bist derzeit in keiner Lerngruppe.</p>
    <div class="row">
      <div class="col-md-6">
        <h3 class="mb-3">Neue Gruppe erstellen</h3>
        <form method="post" action="{$base_url}/lerngruppen.php">
          <div class="mb-3">
            <label for="new_group_name" class="form-label">Gruppenname</label>
            <input type="text" name="group_name" id="new_group_name" class="form-control" required>
          </div>
          <button type="submit" name="create_group" class="btn btn-primary">Erstellen</button>
        </form>
      </div>
      <div class="col-md-6">
        <h3 class="mb-3">Bestehender Gruppe beitreten</h3>
        <form method="post" action="{$base_url}/lerngruppen.php">
          <div class="mb-3">
            <label for="join_group_name" class="form-label">Gruppenname</label>
            <input type="text" name="group_name" id="join_group_name" class="form-control" required>
          </div>
          <button type="submit" name="join_group" class="btn btn-primary">Beitreten</button>
        </form>
      </div>
    </div>
  {/if}
</div>
{/block}
