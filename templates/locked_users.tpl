{extends file="./layouts/layout.tpl"}
{block name="title"}Gesperrte Benutzer{/block}

{block name="content"}
<div class="container mt-5">
  <h1>Gesperrte Benutzer</h1>

  {if isset($flash)}
    <div class="alert alert-{$flash.type} alert-dismissible fade show mt-3" role="alert">
      {$flash.message|escape}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  {/if}

  <!-- Filterformular -->
  <form class="row g-3 mt-3 mb-4" method="get" action="{$base_url}/locked_users">
    <div class="col-md-3">
      <label for="username" class="form-label">Benutzername</label>
      <input type="text" class="form-control" id="username" name="username" value="{$filters.username|escape}">
    </div>
    <div class="col-md-2">
      <label for="min_attempts" class="form-label">Min. Versuche</label>
      <input type="number" class="form-control" id="min_attempts" name="min_attempts" value="{$filters.min_attempts|escape}">
    </div>
    <div class="col-md-2">
      <label for="max_attempts" class="form-label">Max. Versuche</label>
      <input type="number" class="form-control" id="max_attempts" name="max_attempts" value="{$filters.max_attempts|escape}">
    </div>

    <div class="col-12 d-flex justify-content-end">
      <button type="submit" class="btn btn-primary me-2">Filtern</button>
      <a href="{$base_url}/locked_users" class="btn btn-outline-secondary me-2">Zurücksetzen</a>
      <button type="submit" name="export" value="csv" class="btn btn-success">Exportieren als CSV</button>
    </div>
  </form>

  <!-- Ergebnisanzeige -->
  {if $locked_users|@count > 0}
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark text-center">
              <tr>
                <th>#</th>
                <th>User-ID</th>
                <th>Benutzername</th>
                <th>E-Mail</th>
                <th>Fehlversuche</th>
                <th>Aktion</th>
              </tr>
            </thead>
            <tbody>
              {foreach $locked_users as $index => $user}
              <tr>
                <td>{$index + 1}</td>
                <td>{$user.id}</td>
                <td>{$user.username|escape}</td>
                <td>{$user.email|escape}</td>
                <td>{$user.failed_attempts}</td>
                <td class="text-center">
                  <form method="post" action="{$base_url}/locked_users" onsubmit="return confirm('Benutzer wirklich entsperren?');">
                    <input type="hidden" name="unlock_user_id" value="{$user.id}">
                    <button type="submit" class="btn btn-sm btn-outline-success">Entsperren</button>
                  </form>
                </td>
              </tr>
              {/foreach}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  {else}
    <div class="alert alert-info mt-4">Keine gesperrten Benutzer gefunden.</div>
  {/if}
  <div class="mt-4">
    <a href="dashboard" class="btn btn-sm btn-primary">Zurück zum Dashboard</a>
  </div>
</div>
{/block}
