{extends file="./layouts/layout.tpl"}
{block name="title"}Lerngruppen{/block}

{block name="content"}
<div class="container mt-5">

  <h1 class="mb-4 text-center">Lerngruppen</h1>

  {if $error}<div class="alert alert-danger">{$error}</div>{/if}
  {if $success}<div class="alert alert-success">{$success}</div>{/if}

  <div class="row mb-5">
    <div class="col-md-6">
      <h3>Neue Gruppe erstellen</h3>
      <form method="post">
        <div class="mb-3">
          <input type="text" name="group_name" class="form-control" placeholder="Gruppenname" required>
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

  <div class="mb-3">
    <input type="text" id="group-search" class="form-control" placeholder="Gruppe suchen…">
  </div>

  <ul id="group-list" class="list-group">
    {foreach $groups as $g}
      <li class="list-group-item d-flex justify-content-between align-items-center">
        {$g.name|escape}
        <a href="{$base_url}/gruppe.php?id={$g.id}" class="btn btn-sm btn-outline-primary">Ansehen</a>
      </li>
    {/foreach}
  </ul>
</div>
{/block}

{block name="scripts"}
<script>
  document.getElementById('group-search').addEventListener('input', function(e) {
    const filter = e.target.value.toLowerCase();
    document.querySelectorAll('#group-list li').forEach(li => {
      li.style.display = li.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
  });
</script>
{/block}
