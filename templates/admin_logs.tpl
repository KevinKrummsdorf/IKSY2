{extends file="layout.tpl"}

{block name="content"}

<div class="container mt-5">
    <h1 class="mb-4 text-center">Login-Logbuch</h1>

    <div class="table-responsive shadow-sm">
        <table class="table table-striped table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Benutzername</th>
                    <th>IP-Adresse</th>
                    <th>Erfolg</th>
                    <th>Zeitpunkt</th>
                </tr>
            </thead>
            <tbody>
                {if $logs|@count == 0}
                    <tr>
                        <td colspan="5" class="text-center">Keine Logins gefunden.</td>
                    </tr>
                {else}
                    {foreach from=$logs item=log}
                        <tr>
                            <td>{$log.id|escape}</td>
                            <td>{$log.username|default:'Unbekannt'|escape}</td>
                            <td>{$log.ip_address|escape}</td>
                            <td>
                                {if $log.success == 1}
                                    <span class="badge bg-success">Erfolgreich</span>
                                {else}
                                    <span class="badge bg-danger">Fehlgeschlagen</span>
                                {/if}
                            </td>
                            <td>{$log.created_at|escape}</td>
                        </tr>
                    {/foreach}
                {/if}
            </tbody>
        </table>
    </div>
</div>

{/block}
