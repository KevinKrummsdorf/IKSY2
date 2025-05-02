{extends file="layouts/layout.tpl"}

{block name="title"}E-Mail-Verifizierung{/block}

{block name="content"}
  <main class="d-flex flex-column justify-content-center align-items-center text-center vh-100">
    <h1 class="mb-4">E-Mail-Verifizierung</h1>
    <p>{$message}</p>

    {if $showButton}
      <p>
        <a href="{$buttonLink}" class="btn btn-primary">
          {$buttonText}
        </a>
      </p>
    {/if}
  </main>
{/block}
