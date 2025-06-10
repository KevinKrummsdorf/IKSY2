{extends file="./layouts/layout.tpl"}
{block name="content"}
<div class="container my-5">
    <h1 class="mb-4 text-center">Verfügbare Materialien</h1>

    {if $materials|@count > 0}
        <ul class="list-group">
            {foreach $materials as $material}
                <li class="list-group-item">
                    <h5 class="mb-1">{$material.title|escape}</h5>
                    <p class="mb-2">{$material.description|escape}</p>

                    {assign var="fileFound" value=false}
                    {foreach $uploads as $upload}
                        {if $upload.material_id == $material.id}
                            <a href="download.php?id={$upload.id}" class="btn btn-sm btn-outline-primary" target="_blank" download>
                                Datei herunterladen
                            </a>
                            {assign var="fileFound" value=true}
                            {break}
                        {/if}
                    {/foreach}

                    {if !$fileFound}
                        <span class="text-muted">Keine Datei verfügbar</span>
                    {/if}
                </li>
            {/foreach}
        </ul>
    {else}
        <p class="text-center">Keine Materialien gefunden.</p>
    {/if}
</div>
{/block}