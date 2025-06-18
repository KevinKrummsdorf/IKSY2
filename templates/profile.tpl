{extends file="./layouts/layout.tpl"}

{block name="title"}Profil{/block}

{block name="content"}
<div class="container my-5">
  <h1 class="text-center mb-4">
    {if $isOwnProfile}
      Mein Profil
    {else}
      Profil von {$profile.username|escape}
    {/if}
  </h1>
  <div class="row">
    <div class="col-md-3 text-center mb-4">
      {if $profile.profile_picture}
        <img src="{$base_url}/uploads/profile_pictures/{$profile.profile_picture|escape}" alt="Profilbild" class="rounded-circle shadow" style="width:150px;height:150px;object-fit:cover;">
      {else}
        <img src="{$base_url}/assets/default_person.png" alt="Kein Profilbild" class="rounded-circle shadow" style="width:150px;height:150px;object-fit:cover;">
      {/if}
    </div>
    <div class="col-md-9">
      {if $isOwnProfile}
        <h2 class="h4">Login-Daten</h2>
        <div class="mb-3 d-flex justify-content-between align-items-center">
          <span>Benutzername: {$profile.username|escape}</span>
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#usernameModal">Ändern</button>
        </div>
        <div class="mb-3 d-flex justify-content-between align-items-center">
          <span>E-Mail-Adresse: {$profile.email|escape}</span>
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#emailModal">Ändern</button>
        </div>
        <div class="mb-4 d-flex justify-content-between align-items-center">
          <span>Passwort: ********</span>
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#passwordModal">Ändern</button>
        </div>
      {/if}

      <h2 class="h4">Persönliche Angaben</h2>
      <p class="mb-1">Vorname: {$profile.first_name|escape}</p>
      <p class="mb-1">Nachname: {$profile.last_name|escape}</p>
      <p class="mb-3">
        Geburtsdatum:
        {if $profile.birthdate}
          {$profile.birthdate|escape}
          {if $profile.age}({$profile.age} Jahre){/if}
        {else}
          -
        {/if}
      </p>
      <div class="mb-4">
        <label class="form-label">About Me</label>
        <textarea class="form-control" rows="4" readonly>{$profile.about_me|escape}</textarea>
      </div>
      {if $isOwnProfile}
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#personalModal">Ändern</button>
      {/if}

      <h2 class="h4">Social Media</h2>
      <p class="mb-1">Instagram: {$profile.instagram|escape}</p>
      <p class="mb-1">Discord: {$profile.discord|escape}</p>
      <p class="mb-3">MS Teams: {$profile.ms_teams|escape}</p>
      {if $isOwnProfile}
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#socialModal">Ändern</button>
      {/if}
    </div>
  </div>
</div>

{if $isOwnProfile}
<!-- Username Modal -->
<div class="modal fade" id="usernameModal" tabindex="-1" aria-labelledby="usernameModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="usernameModalLabel">Benutzername ändern</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
      <div class="modal-body">
        <form method="post" action="update_profile.php">
          <input type="hidden" name="action" value="update_username">
          <div class="mb-3">
            <label for="new_username" class="form-label">Neuer Benutzername</label>
            <input type="text" class="form-control" id="new_username" name="username" required>
          </div>
          <button type="submit" class="btn btn-primary">Speichern</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="emailModalLabel">E-Mail-Adresse ändern</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
      <div class="modal-body">
        <form method="post" action="update_profile.php">
          <input type="hidden" name="action" value="update_email">
          <div class="mb-3">
            <label for="new_email" class="form-label">Neue E-Mail-Adresse</label>
            <input type="email" class="form-control" id="new_email" name="email" required>
          </div>
          <button type="submit" class="btn btn-primary">Speichern</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Password Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="passwordModalLabel">Passwort ändern</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
      <div class="modal-body">
        <form method="post" action="update_profile.php">
          <input type="hidden" name="action" value="update_password">
          <div class="mb-3">
            <label for="current_password" class="form-label">Aktuelles Passwort</label>
            <input type="password" class="form-control" id="current_password" name="old_password" required>
          </div>
          <div class="mb-3">
            <label for="new_password" class="form-label">Neues Passwort</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required>
          </div>
          <div class="mb-3">
            <label for="new_password_confirm" class="form-label">Passwort bestätigen</label>
            <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm" required>
          </div>
          <button type="submit" class="btn btn-primary">Speichern</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Personal Data Modal -->
<div class="modal fade" id="personalModal" tabindex="-1" aria-labelledby="personalModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="personalModalLabel">Persönliche Angaben bearbeiten</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
      <div class="modal-body">
        <form method="post" action="update_profile.php">
          <input type="hidden" name="action" value="update_personal">
          <div class="mb-3">
            <label for="first_name" class="form-label">Vorname</label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="{$profile.first_name|escape}" required>
          </div>
          <div class="mb-3">
            <label for="last_name" class="form-label">Nachname</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="{$profile.last_name|escape}" required>
          </div>
          <div class="mb-3">
            <label for="birthdate" class="form-label">Geburtsdatum</label>
            <input type="date" class="form-control" id="birthdate" name="birthdate" value="{$profile.birthdate|escape}">
          </div>
          <div class="mb-3">
            <label for="about_me_edit" class="form-label">About Me</label>
            <textarea class="form-control" id="about_me_edit" name="about_me" rows="4">{$profile.about_me|escape}</textarea>
          </div>
          <button type="submit" class="btn btn-primary">Speichern</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Social Media Modal -->
<div class="modal fade" id="socialModal" tabindex="-1" aria-labelledby="socialModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="socialModalLabel">Social Media bearbeiten</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
      <div class="modal-body">
        <form method="post" action="update_profile.php">
          <input type="hidden" name="action" value="update_socials">
          <div class="mb-3">
            <label for="instagram" class="form-label">Instagram</label>
            <input type="text" class="form-control" id="instagram" name="instagram" value="{$profile.instagram|escape}">
          </div>
          <div class="mb-3">
            <label for="discord" class="form-label">Discord</label>
            <input type="text" class="form-control" id="discord" name="discord" value="{$profile.discord|escape}">
          </div>
          <div class="mb-3">
            <label for="ms_teams" class="form-label">MS Teams</label>
            <input type="text" class="form-control" id="ms_teams" name="ms_teams" value="{$profile.ms_teams|escape}">
          </div>
          <button type="submit" class="btn btn-primary">Speichern</button>
        </form>
      </div>
    </div>
  </div>
</div>

{/if}

{/block}
