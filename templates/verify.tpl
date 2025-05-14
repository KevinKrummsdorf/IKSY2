{extends file="./layouts/layout.tpl"}

{block name="title"}E-Mail-Verifizierung{/block}

{block name="content"}
  <div class="container my-5">
    <div class="alert alert-{$alertType} text-center" role="alert">
      <p class="mb-3">{$message}</p>
      {if $showButton}
        <a href="{$buttonLink}" class="btn btn-primary">{$buttonText}</a>
      {/if}
    </div>
  </div>
{/block}
