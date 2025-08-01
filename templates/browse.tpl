{extends file="./layouts/layout.tpl"}

{block name="title"}Materialsuche{/block}

{block name="content"}
<div class="container my-5">
    <h1 class="mb-4 text-center">Verfügbare Materialien</h1>
    <br>

    {* Suchformular *}
    <form method="get" action="{url path='browse'}" class="mb-4">
        <div class="input-group">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                placeholder="Materialien suchen..." 
                value="{$searchTerm|escape}" 
                aria-label="Materialien suchen"
            >
            <button class="btn btn-primary" type="submit">Suchen</button>
        </div>
    </form>
    <br>

    {if $materials|@count > 0}
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            {foreach $materials as $material}
                <div class="col">
                    <div class="card h-100 position-relative">
                        <div class="card-body">
                            <h5 class="card-title">{$material.title|escape}</h5>
                            <p class="card-text">{$material.description|escape}</p>

                            {* Kursname anzeigen *}
                            {if $material.course_name}
                                <p class="text-muted mb-2"><i>Kurs: {$material.course_name|escape}</i></p>
                            {/if}

                            {* Download/Ansicht-Logik *}
                            {assign var="upload" value=$uploadsByMaterial[$material.id][0]|default:null}
                            {if $upload}
                                {if $isLoggedIn}
                                    <a href="{url path='download' id=$upload.id}" class="btn btn-sm btn-outline-primary me-2" target="_blank" download>Download</a>
                                    <a href="{url path='view_pdf' file=$upload.stored_name}" class="btn btn-sm btn-outline-secondary" target="_blank">Dokument anzeigen</a>
                                {/if}

                                    {* Durchschnittsbewertung anzeigen *}
                                    <p class="mt-2" id="rating-info-{$material.id}">
                                        Durchschnitt:
                                        {if $averageRatings[$material.id].average_rating > 0}
                                            {math equation="round(a,1)" a=$averageRatings[$material.id].average_rating} ★
                                            ({$averageRatings[$material.id].total_ratings} Bewertungen)
                                        {else}
                                            Noch keine Bewertung
                                        {/if}
                                    </p>

                                    {* Bewertungssterne anzeigen *}
                                    {if $isLoggedIn}
                                        <div>
                                            Deine Bewertung: 
                                            {section name=star loop=5}
                                                {assign var="starValue" value=$smarty.section.star.index+1}
                                                <span
                                                    class="star {if $userRatings[$material.id]|default:0 >= $starValue}text-warning{else}text-secondary{/if}"
                                                    data-material-id="{$material.id}"
                                                    style="font-size: 24px; cursor:pointer;"
                                                    onclick="submitRating({$material.id}, {$starValue})"
                                                >★</span>
                                            {/section}
                                        </div>
                                    {/if}

                                {* Profilbild anzeigen *}
                                {if isset($profiles[$upload.uploaded_by])}
                                    {assign var="profile" value=$profiles[$upload.uploaded_by]}
                                        <a href="{url path='profile' user=$profile.username}" class="profile-picture-link position-absolute bottom-0 end-0 m-2">
                                            {if $profile.profile_picture}
                                                <img src='{url file="profile_pictures/{$profile.profile_picture|escape:'url'}"}'
                                                     alt="{$profile.first_name|escape} {$profile.last_name|escape}"
                                                     class="rounded-circle shadow profile-picture"
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            {else}
                                                <img src="{$base_url}/assets/default_person.png" alt="Kein Profilbild"
                                                     class="rounded-circle shadow profile-picture"
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            {/if}
                                        </a>
                                {/if}
                            {else}
                                <span class="text-muted">Keine Datei verfügbar</span>
                            {/if}
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    {else}
        <p class="text-center">Keine Materialien gefunden.</p>
    {/if}
</div>
{/block}

{block name="scripts"}
<script src="{$base_url}/js/rating.js"></script>
{/block}
