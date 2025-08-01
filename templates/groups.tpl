{extends file="./layouts/layout.tpl"}
{block name="title"}Lerngruppen{/block}

{block name="content"}
<div class="container mt-5">

  <h1 class="mb-4 text-center">Lerngruppen</h1>

  {if $error}<div class="alert alert-danger">{$error|escape}</div>{/if}
  {if $success}<div class="alert alert-success">{$success|escape}</div>{/if}

 

  <div class="mb-3">
    <input type="text" id="group-search" class="form-control" placeholder="Gruppe suchen…">
  </div>

  <ul id="group-list" class="list-group">
    {foreach $groups as $g}
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <span class="d-flex align-items-center">
          <img src="{if $g.group_picture}{url file="group_pictures/{$g.group_picture|escape:'url'}"}{else}{$base_url}/assets/default_group.png{/if}"
               alt="Gruppenbild" class="rounded-circle me-2" style="width:40px;height:40px;object-fit:cover;">
          {$g.name|escape}
        </span>
        <a href="{url path='groups' name=$g.name}" class="btn btn-sm btn-outline-primary">Ansehen</a>
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
