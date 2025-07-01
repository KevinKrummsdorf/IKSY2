{extends file="../layouts/layout.tpl"}

{block name="title"}404 – Seite nicht gefunden{/block}

{block name="content"}
    <div class="text-center py-5">
        <img src="{$base_url}/assets/404.png" alt="404 Roboter" class="mb-4" style="max-width: 320px;">
        <h1 class="display-4 text-warning">404 – Seite nicht gefunden</h1>
        <p class="lead">{$reason|default:"Die angeforderte Seite konnte nicht gefunden werden."|escape}</p>
        <a href="{$base_url}/" class="btn btn-primary mt-4">Zur Startseite</a>
    </div>
{/block}
