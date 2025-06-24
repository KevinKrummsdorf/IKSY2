{extends file="./layouts/layout.tpl"}

{block name="title"}Profil bearbeiten{/block}

{block name="content"}

<div class="container my-5">

    <h1 class="text-center">Profil bearbeiten</h1>

    {* PROFILBILD OBEN + LÖSCHBUTTON *}
    <div class="text-center mb-4">
        {if $profile.profile_picture}
            <img src="{$base_url}/uploads/profile_pictures/{$profile.profile_picture|escape}" alt="Profilbild"
                 class="rounded-circle shadow mb-2" style="max-width: 150px; display: block; margin: 0 auto;">
            <div>
                <form method="post" action="delete_profile_picture" class="d-inline-block mt-2">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    <button type="submit" class="btn btn-outline-danger btn-sm"
                            onclick="return confirm('Profilbild wirklich löschen?')">
                        Profilbild löschen
                    </button>
                </form>
            </div>
        {else}
            <img src="{$base_url}/assets/default_person.png" alt="Kein Profilbild"
                 class="rounded-circle shadow mb-2" style="max-width: 150px; display: block; margin: 0 auto;">
        {/if}
    </div>

    {if $smarty.get.img_deleted == 1}
        <div class="alert alert-success text-center">Profilbild wurde erfolgreich gelöscht.</div>
    {/if}

    {* PROFILBEARBEITUNGSFORMULAR *}
    <form method="post" action="saveprofile" enctype="multipart/form-data" class="card p-4 mb-4">

        <div class="mb-3">
            <label for="profile_picture" class="form-label">Neues Profilbild</label>
            <input type="file" class="form-control" name="profile_picture" accept="image/*">
        </div>

        <div class="mb-3">
            <label for="first_name" class="form-label">Vorname</label>
            <input type="text" class="form-control" name="first_name" value="{$profile.first_name|escape}">
        </div>

        <div class="mb-3">
            <label for="last_name" class="form-label">Nachname</label>
            <input type="text" class="form-control" name="last_name" value="{$profile.last_name|escape}">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-Mail</label>
            <input type="email" class="form-control" name="email" value="{$email|escape}">
        </div>

        <div class="mb-3">
            <label for="birthdate" class="form-label">Geburtsdatum</label>
            <input type="date" class="form-control" name="birthdate" value="{$profile.birthdate|escape}">
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Wohnort</label>
            <input type="text" class="form-control" name="location" value="{$profile.location|escape}">
        </div>

        <div class="mb-3">
            <label for="about_me" class="form-label">Über mich</label>
            <textarea class="form-control" name="about_me" rows="4">{$profile.about_me|escape}</textarea>
        </div>

        {* Social-Media Handles *}
        <div class="mb-3">
            <label for="instagram" class="form-label">Instagram</label>
            <input type="text" class="form-control" name="instagram" value="{$socials.instagram|default:''|escape}">
        </div>

        <div class="mb-3">
            <label for="tiktok" class="form-label">TikTok</label>
            <input type="text" class="form-control" name="tiktok" value="{$socials.tiktok|default:''|escape}">
        </div>

        <div class="mb-3">
            <label for="discord" class="form-label">Discord</label>
            <input type="text" class="form-control" name="discord" value="{$socials.discord|default:''|escape}">
        </div>

        <div class="mb-3">
            <label for="ms_teams" class="form-label">MS Teams</label>
            <input type="text" class="form-control" name="ms_teams" value="{$socials.ms_teams|default:''|escape}">
        </div>

        <div class="mb-3">
            <label for="twitter" class="form-label">Twitter (X)</label>
            <input type="text" class="form-control" name="twitter" value="{$socials.twitter|default:''|escape}">
        </div>

        <div class="mb-3">
            <label for="linkedin" class="form-label">LinkedIn</label>
            <input type="text" class="form-control" name="linkedin" value="{$socials.linkedin|default:''|escape}">
        </div>

        <div class="mb-3">
            <label for="github" class="form-label">GitHub</label>
            <input type="text" class="form-control" name="github" value="{$socials.github|default:''|escape}">
        </div>

        <button type="submit" class="btn btn-success">Speichern</button>
    </form>

    {if $smarty.get.success == 1}
        <div class="alert alert-success text-center">Profil wurde erfolgreich gespeichert.</div>
    {/if}

</div>
{/block}
