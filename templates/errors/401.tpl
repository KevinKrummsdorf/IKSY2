{extends file="../layouts/layout.tpl"}

{block name="title"}401 – Anmeldung erforderlich{/block}

{block name="content"}
    <div class="text-center py-5">
        <img src="{$base_url}/assets/403.png" alt="401 Fehlerroboter" class="mb-4" style="max-width: 320px;">
        <h1 class="display-4 text-danger">401 – Anmeldung erforderlich</h1>
        <p class="lead">{$reason|default:"Du musst eingeloggt sein, um diese Seite aufzurufen."}</p>

{if $action == 'login' || $action == 'register' || $action == 'both'}
    <div class="d-flex justify-content-center align-items-center gap-3 mt-4 flex-wrap">
        {if $action == 'login' || $action == 'both'}
            <a href="{$base_url}/?show=login" class="btn btn-primary">Jetzt einloggen</a>
        {/if}

        {if $action == 'both'}
            <span class="fw-semibold text-muted">oder</span>
        {/if}

        {if $action == 'register' || $action == 'both'}
            <a href="{$base_url}/?show=register" class="btn btn-outline-secondary">Account erstellen</a>
        {/if}
    </div>
{/if}

        <a href="{$base_url}/" class="btn btn-link d-block mt-3">Zur Startseite</a>
    </div>
{/block}
