{extends file="./layouts/layout.tpl"}

{block name="title"}Profil bearbeiten{/block}

{block name="content"}
<h1 class="text-center">Profil bearbeiten</h1>

<div class="container my-5">
    <form method="post" action="saveprofile.php" enctype="multipart/form-data" class="card p-4">

        <div class="mb-3">
            <label for="first_name" class="form-label">Vorname</label>
            <input type="text" class="form-control" name="first_name" value="{$profile.first_name}">
        </div>

        <div class="mb-3">
            <label for="last_name" class="form-label">Nachname</label>
            <input type="text" class="form-control" name="last_name" value="{$profile.last_name}">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-Mail</label>
            <input type="email" class="form-control" name="email" value="{$email}">
        </div>

        <div class="mb-3">
            <label for="birthdate" class="form-label">Geburtsdatum</label>
            <input type="date" class="form-control" name="birthdate" value="{$profile.birthdate}">
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Wohnort</label>
            <input type="text" class="form-control" name="location" value="{$profile.location}">
        </div>

        <div class="mb-3">
            <label for="about_me" class="form-label">Über mich</label>
            <textarea class="form-control" name="about_me" rows="4">{$profile.about_me}</textarea>
        </div>

        <div class="mb-3">
            <label for="instagram" class="form-label">Instagram</label>
            <input type="text" class="form-control" name="instagram" value="{$profile.instagram}">
        </div>

        <div class="mb-3">
            <label for="tiktok" class="form-label">TikTok</label>
            <input type="text" class="form-control" name="tiktok" value="{$profile.tiktok}">
        </div>

        <div class="mb-3">
            <label for="discord" class="form-label">Discord</label>
            <input type="text" class="form-control" name="discord" value="{$profile.discord}">
        </div>

        <div class="mb-3">
            <label for="ms_teams" class="form-label">MS Teams</label>
            <input type="text" class="form-control" name="ms_teams" value="{$profile.ms_teams}">
        </div>

        <div class="mb-3">
            <label for="profile_picture" class="form-label">Neues Profilbild</label>
            <input type="file" class="form-control" name="profile_picture" accept="image/*">
        </div>

        <button type="submit" class="btn btn-success">Speichern</button>
    </form>
</div>
{if $smarty.get.success == 1}
<div class="alert alert-success">Profil wurde erfolgreich gespeichert.</div>
{/if}

{/block}
