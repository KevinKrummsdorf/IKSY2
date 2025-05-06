{extends file="./layouts/layout.tpl"}

{block name="title"}Dashboard{/block}

{block name="content"}
  <div class="container mt-5">
    <h1>Hallo, {$username}!</h1>

    {if $isAdmin}
      <section class="my-4">
        <h2>Letzte Login-Logs</h2>

        {if $login_logs|count gt 0}
          <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>User ID</th>
                    <th>IP (anonymisiert)</th>
                    <th>Erfolg</th>
                    <th>Grund</th>
                    <th>Zeitpunkt</th>
                </tr>
            </thead>
            <tbody>
              {foreach $login_logs as $i => $log}
                <tr>
                    <td>{$i+1}</td>
                    <td>{$log.username|default:'–'}</td>
                    <td>{$log.user_id|default:'–'}</td>
                    <td>{$log.ip_address}</td>
                    <td>
                        {if $log.success}
                            <span class="badge bg-success">Yes</span>
                        {else}
                            <span class="badge bg-danger">No</span>
                        {/if}
                    </td>
                    <td>{$log.reason}</td>
                    <td>{$log.created_at}</td>
                </tr>
              {/foreach}
            </tbody>
          </table>
        {else}
          <div class="alert alert-info" role="alert">
            Keine Login-Logs vorhanden.
          </div>
        {/if}

      </section>
      <section class="my-4">
        <h2>reCAPTCHA-Protokolle</h2>
        {if $captcha_logs|count gt 0}
          <table class="table table-sm table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>ID</th>
                <th>Success</th>
                <th>Score</th>
                <th>Action</th>
                <th>Host</th>
                <th>Grund</th>
                <th>Zeitpunkt</th>
              </tr>
            </thead>
            <tbody>
              {foreach $captcha_logs as $i => $log}
                <tr>
                  <td>{$i+1}</td>
                  <td>{$log.id}</td>
                  <td>
                    {if $log.success}
                      <span class="badge bg-success">Yes</span>
                    {else}
                      <span class="badge bg-danger">No</span>
                    {/if}
                  </td>
                  <td>{$log.score|default:'–'}</td>
                  <td>{$log.action|escape}</td>
                  <td>{$log.hostname|escape}</td>
                  <td>{$log.error_reason|default:'–'|escape}</td>
                  <td>{$log.created_at}</td>
                </tr>
              {/foreach}
            </tbody>
          </table>
        {else}
          <div class="alert alert-info">
            Keine reCAPTCHA-Logs vorhanden.
          </div>
        {/if}
      </section>
    {/if}
  </div>
{/block}
