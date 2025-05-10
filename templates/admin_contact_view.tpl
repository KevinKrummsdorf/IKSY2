{extends file="layout.tpl"}

{block name="title"}Kontaktanfrage {$contactId}{/block}

{block name="content"}
<div class="container mt-5">
  <h1 class="mb-4">Kontaktanfrage: <code>{$contactId}</code></h1>

  {if !$request}
    <div class="alert alert-danger">Anfrage nicht gefunden.</div>
  {else}
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <p><strong>Name:</strong> {$request.name|escape}</p>
        <p><strong>E-Mail:</strong> 
          <a href="mailto:{$request.email|escape}">{$request.email|escape}</a>
        </p>
        <p><strong>Betreff:</strong> {$request.subject|escape}</p>
        <p><strong>Nachricht:</strong><br>
          <pre class="border p-3 bg-light">{$request.message|escape}</pre>
        </p>
        <hr>
        <p><strong>IP-Adresse:</strong> {$request.ip_address|escape}</p>
        <p><strong>User Agent:</strong><br>
          <small>{$request.user_agent|escape}</small>
        </p>
        <p><strong>Erstellt am:</strong> {$request.created_at}</p>
      </div>
    </div>

    <a href="mailto:{$request.email|escape}?subject=Re: {$request.subject|escape}" 
       class="btn btn-primary">Antworten</a>
    <a href="admin_contact_requests.php" class="btn btn-secondary ms-2">Zurück zur Übersicht</a>
  {/if}
</div>
{/block}
