{extends file="./layouts/layout.tpl"}
{block name="title"}Meine Lerngruppen{/block}

{block name="content"}
<div class="container mt-5">
  <h1 class="mb-4 text-center">Meine Lerngruppen</h1>
  {if $error}<div class="alert alert-danger">{$error}</div>{/if}
  {if $success}<div class="alert alert-success">{$success}</div>{/if}

  {if $myGroups|@count > 0}
    <div class="mb-3">
      <input type="text" id="group-search" class="form-control" placeholder="Gruppe suchen…">
    </div>
    <ul id="group-list" class="list-group mb-4">
      {foreach $myGroups as $g}
        <li class="list-group-item d-flex justify-content-between align-items-center">
          {$g.name|escape}
          <a href="{url path='groups' name=$g.name}" class="btn btn-sm btn-outline-primary">Ansehen</a>
        </li>
      {/foreach}
    </ul>
  {else}
    <p class="text-muted">Du bist noch in keiner Lerngruppe.</p>
  {/if}

  <hr class="my-4">
  <div class="row">
    <div class="col-md-6">
      <h3>Neue Gruppe erstellen</h3>
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
    <div class="col-md-6">
      <h3>Bestehender Gruppe beitreten</h3>
      <form method="post">
        <div class="mb-3">
          <input type="text" name="group_name" class="form-control" placeholder="Gruppenname" required>
        </div>
        <button name="join_group" class="btn btn-primary">Beitreten</button>
      </form>
    </div>
  </div>
</div>
{/block}

{block name="scripts"}
<script>
  const inp = document.getElementById('group-search');
  if (inp) {
    inp.addEventListener('input', function(e) {
      const filter = e.target.value.toLowerCase();
      document.querySelectorAll('#group-list li').forEach(li => {
        li.style.display = li.textContent.toLowerCase().includes(filter) ? '' : 'none';
      });
    });
  }
</script>
{/block}
