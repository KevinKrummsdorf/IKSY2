{extends file="./layouts/layout.tpl"}
{block name="title"}Gruppe erstellen{/block}
{block name="content"}
<div class="container mt-5">
  <h1 class="mb-4 text-center">Neue Gruppe erstellen</h1>
  {if $error}<div class="alert alert-danger">{$error|escape}</div>{/if}
  {if $success}<div class="alert alert-success">{$success|escape}</div>{/if}
  <form method="post">
    <div class="mb-3">
      <input type="text" name="group_name" class="form-control" placeholder="Gruppenname" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Beitrittsart</label>
      <select name="join_type" class="form-select">
        <option value="open">Offen</option>
        <option value="invite">Nur per Einladung</option>
        <option value="code">Nur per Einladungscode</option>
      </select>
    </div>
    <button name="create_group" class="btn btn-primary">Erstellen</button>
  </form>
</div>
{/block}
