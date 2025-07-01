{* templates/errors/403.tpl *}
{extends file="../layouts/layout.tpl"}

{block name="title"}403 – Keine ausreichenden Rechte{/block}

{block name="content"}
    <div class="text-center py-5">
        <img src="{$base_url}/assets/403.png" alt="403 Fehlerroboter" class="mb-4" style="max-width: 320px;">
        <h1 class="display-4 text-danger">403 – Keine ausreichenden Rechte</h1>
        <p class="lead">{$reason|default:"Du hast nicht die nötigen Rechte, um auf diese Ressource zuzugreifen."|escape}</p>
        <a href="{$base_url}/" class="btn btn-link d-block mt-3">Zur Startseite</a>
    </div>
{/block}
