{extends file="../layouts/layout.tpl"}

{block name="title"}500 – Interner Serverfehler{/block}

{block name="content"}
    <div class="text-center py-5">
        <img src="{$base_url}/assets/500.png" alt="500 Roboter" class="mb-4" style="max-width: 320px;">
        <h1 class="display-4 text-danger">500 – Interner Serverfehler</h1>
        <p class="lead">{$reason|default:"Etwas ist schiefgelaufen. Bitte versuche es später erneut."}</p>
        <a href="/studyhub/index.php" class="btn btn-primary mt-4">Zur Startseite</a>
    </div>
{/block}
