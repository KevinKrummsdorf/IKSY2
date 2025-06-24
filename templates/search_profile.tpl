{extends file="./layouts/layout.tpl"}

{block name="title"}Benutzer suchen{/block}

{block name="content"}
<h1 class="text-center">Benutzer suchen</h1>

<div class="container my-5">
    <form method="get" action="search_profile" class="mb-4">
        <input type="text" name="q" class="form-control" placeholder="Benutzername oder E-Mail..." value="{$searchTerm|escape:'html'}">
        <button type="submit" class="btn btn-primary mt-2">Suchen</button>
    </form>

    {if $results|@count > 0}
        <ul class="list-group">
            {foreach from=$results item=foundUser}
  <li class="list-group-item">
    <a href="{$base_url}/profile/{$foundUser.result_username|escape:'url'}">
      {$foundUser.result_username|escape:'html'}
    </a>
  </li>
{/foreach}


        </ul>
    {elseif $searchTerm != ''}
        <p class="text-muted">Keine Benutzer gefunden.</p>
    {/if}
</div>
{/block}
