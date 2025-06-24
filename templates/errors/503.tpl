{extends file="../layouts/layout.tpl"}

{block name="title"}503 – Service nicht verfügbar{/block}

{block name="content"}
    <div class="text-center py-5">
        <img src="{$base_url}/assets/503.png" alt="503 Roboter" class="mb-4" style="max-width: 320px;">
        <h1 class="display-4 text-secondary">503 – Service nicht verfügbar</h1>
        <p class="lead">{$reason|default:"Der Dienst ist vorübergehend nicht erreichbar. Bitte versuche es später erneut."}</p>
        <a href="/studyhub/index" class="btn btn-primary mt-4">Zur Startseite</a>
    </div>
{/block}
