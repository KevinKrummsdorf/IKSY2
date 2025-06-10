{extends file="./layouts/layout.tpl"}

{block name="title"}Passwort zurücksetzen{/block}

{block name="content"}
<h1>Neues Passwort setzen</h1>
{if $success}
    <div class="alert alert-success">Passwort wurde geändert.</div>
    <a href="{$base_url}/index.php" class="btn btn-primary">Zur Startseite</a>
{elseif $message}
    <div class="alert alert-danger">{$message}</div>
{/if}
{if !$success}
<form method="post" class="needs-validation" novalidate>
    <div class="mb-3">
        <label for="password" class="form-label">Neues Passwort</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <div class="mb-3">
        <label for="password_confirm" class="form-label">Passwort bestätigen</label>
        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
    </div>
    <button type="submit" class="btn btn-primary">Passwort speichern</button>
</form>
{/if}
{/block}
