{* templates/errors/403.tpl *}
{extends file="../layouts/layout.tpl"}

{block name="title"}403 – Zugriff verweigert{/block}

{block name="content"}
    <div class="text-center py-5">
        <img src="{$base_url}/assets/403.png" alt="403 Fehlerroboter" class="mb-4" style="max-width: 320px;">
        <h1 class="display-4 text-danger">403 – Zugriff verweigert</h1>
        <p class="lead">{$reason|default:"Du hast keine Berechtigung, diese Seite aufzurufen."}</p>

{if $action == 'login' || $action == 'register' || $action == 'both'}
    <div class="d-flex justify-content-center align-items-center gap-3 mt-4 flex-wrap">
        {if $action == 'login' || $action == 'both'}
            <a href="/studyhub/index?show=login" class="btn btn-primary">Jetzt einloggen</a>
        {/if}

        {if $action == 'both'}
            <span class="fw-semibold text-muted">oder</span>
        {/if}

        {if $action == 'register' || $action == 'both'}
            <a href="/studyhub/index?show=register" class="btn btn-outline-secondary">Account erstellen</a>
        {/if}
    </div>
{/if}



        <a href="/studyhub/index" class="btn btn-link d-block mt-3">Zur Startseite</a>
    </div>
{/block}
