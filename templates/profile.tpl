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
        {assign var=pfile value=$profile.profile_picture|escape:'url'}
        <img src='{url file="profile_pictures/$pfile"}' alt="Profilbild" class="rounded-circle shadow" style="width:150px;height:150px;object-fit:cover;">
      {else}
        <img src="{$base_url}/assets/default_person.png" alt="Kein Profilbild" class="rounded-circle shadow" style="width:150px;height:150px;object-fit:cover;">
      {/if}
      {if $isOwnProfile}
        <button class="btn btn-sm btn-outline-primary d-block mx-auto mt-2 mb-3" data-bs-toggle="modal" data-bs-target="#pictureModal">Profilbild ändern</button>
      {/if}
    </div>
    <div class="col-md-9">
      {if $isOwnProfile}
        <h2 class="h4">Login-Daten</h2>
        <div class="mb-3 d-flex justify-content-between align-items-center">
          <span>Benutzername: {$profile.username|escape}</span>
          <button class="btn btn-sm btn-outline-primary mb-2" data-bs-toggle="modal" data-bs-target="#usernameModal">Ändern</button>
        </div>
        <div class="mb-3 d-flex justify-content-between align-items-center">
          <span>E-Mail-Adresse: {$profile.email|escape}</span>
          <button class="btn btn-sm btn-outline-primary mb-2" data-bs-toggle="modal" data-bs-target="#emailModal">Ändern</button>
        </div>
        <div class="mb-4 d-flex justify-content-between align-items-center">
          <span>Passwort: ********</span>
          <button class="btn btn-sm btn-outline-primary mb-2" data-bs-toggle="modal" data-bs-target="#passwordModal">Ändern</button>
        </div>

        <div class="mb-4">
        <h2 class="h4">Zwei-Faktor-Authentifizierung</h2>
        {if isset($success)}<div class="alert alert-success">{$success}</div>{/if}
        {if isset($message)}<div class="alert alert-danger">{$message}</div>{/if}

        {if $twofa_enabled}
          <p>2FA ist aktiviert.</p>
          <form method="post" class="d-inline">
            <input type="hidden" name="action" value="disable_2fa">
            <button type="submit" class="btn btn-sm btn-outline-danger">Deaktivieren</button>
          </form>
        {else}
          {if $show_2fa_form}
            <p>Scanne den QR-Code mit einer Authenticator-App und gib anschließend den 6-stelligen Code ein.</p>
            <div class="my-3 text-center">
              <img src="{$qrCodeUrl}" alt="QR-Code" class="img-fluid" style="max-width:200px;">
            </div>
            <form method="post" class="needs-validation" novalidate>
              <input type="hidden" name="action" value="confirm_2fa">
              <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" pattern="^\d{6}$" required autocomplete="off">
                <div class="invalid-feedback">Bitte gültigen 6-stelligen Code eingeben.</div>
              </div>
              <button type="submit" class="btn btn-primary btn-sm">2FA aktivieren</button>
            </form>
          {else}
            <form method="post" class="d-inline">
              <input type="hidden" name="action" value="start_2fa">
              <button type="submit" class="btn btn-sm btn-outline-primary">Jetzt 2FA einrichten</button>
            </form>
          {/if}
        </div>
        {/if}
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
        <button class="btn btn-sm btn-outline-primary mb-3" data-bs-toggle="modal" data-bs-target="#personalModal">Ändern</button>
      {/if}

      <h2 class="h4 mt-2">Social Media</h2>
        <ul class="list-unstyled mb-3">
            {if !empty($socials.instagram)}
              <li><i class="bi bi-instagram"></i> Instagram: <a href="https://instagram.com/{$socials.instagram|escape:'url'}" target="_blank">{$socials.instagram|escape:'html'}</a></li>
            {/if}
            {if !empty($socials.tiktok)}
              <li><i class="bi bi-tiktok"></i> TikTok: <a href="https://www.tiktok.com/@{$socials.tiktok|escape:'url'}" target="_blank">{$socials.tiktok|escape:'html'}</a></li>
            {/if}
            {if !empty($socials.discord)}
              <li><i class="bi bi-discord"></i> Discord: {$socials.discord|escape:'html'}</li>
            {/if}
            {if !empty($socials.ms_teams)}
              <li><i class="bi bi-microsoft"></i> MS Teams: {$socials.ms_teams|escape:'html'}</li>
            {/if}
            {if !empty($socials.twitter)}
              <li><i class="bi bi-twitter"></i> Twitter: <a href="https://twitter.com/{$socials.twitter|escape:'url'}" target="_blank">{$socials.twitter|escape:'html'}</a></li>
            {/if}
            {if !empty($socials.linkedin)}
              <li><i class="bi bi-linkedin"></i> LinkedIn: <a href="https://www.linkedin.com/in/{$socials.linkedin|escape:'url'}" target="_blank">{$socials.linkedin|escape:'html'}</a></li>
            {/if}
            {if !empty($socials.github)}
            <li><i class="bi bi-github"></i> GitHub: <a href="https://github.com/{$socials.github|escape:'url'}" target="_blank">{$socials.github|escape:'html'}</a></li>
          {/if}
      </ul>
      {if $isOwnProfile}
        <button class="btn btn-sm btn-outline-primary mb-3" data-bs-toggle="modal" data-bs-target="#socialModal">Ändern</button>
      {/if}
    </div>
  </div>
</div>

{if $isOwnProfile}
<!-- Profile Picture Modal -->
<div class="modal fade" id="pictureModal" tabindex="-1" aria-labelledby="pictureModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pictureModalLabel">Profilbild ändern</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
      <div class="modal-body">
        <form method="post" action="{url path='update_profile'}" enctype="multipart/form-data">
          <input type="hidden" name="action" value="update_picture">
          <div class="mb-3">
            <label for="profile_picture" class="form-label">Neues Profilbild</label>
            <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*" required>
          </div>
          <button type="submit" class="btn btn-primary">Speichern</button>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- Username Modal -->
<div class="modal fade" id="usernameModal" tabindex="-1" aria-labelledby="usernameModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="usernameModalLabel">Benutzername ändern</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
      <div class="modal-body">
        <form method="post" action="{url path='update_profile'}">
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
        <form method="post" action="{url path='update_profile'}">
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
        <form method="post" action="{url path='update_profile'}">
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
        <form method="post" action="{url path='update_profile'}">
          <input type="hidden" name="action" value="update_personal">
          <div class="mb-3">
            <label for="first_name" class="form-label">Vorname</label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="{$profile.first_name|escape}">
          </div>
          <div class="mb-3">
            <label for="last_name" class="form-label">Nachname</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="{$profile.last_name|escape}">
          </div>
          <div class="mb-3">
            <label for="birthdate" class="form-label">Geburtsdatum</label>
            <input type="date" class="form-control" id="birthdate" name="birthdate" value="{$profile.birthdate|escape}" max="{$max_birthdate}">
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
        <form method="post" action="{url path='update_profile'}">
          <input type="hidden" name="action" value="update_socials">
          <div class="mb-3">
            <label for="instagram" class="form-label">Instagram</label>
            <input type="text" class="form-control" id="instagram" name="instagram" value="{$socials.instagram|default:''|escape}">
          </div>
          <div class="mb-3">
            <label for="tiktok" class="form-label">TikTok</label>
            <input type="text" class="form-control" id="tiktok" name="tiktok" value="{$socials.tiktok|default:''|escape}">
          </div>
          <div class="mb-3">
            <label for="discord" class="form-label">Discord</label>
            <input type="text" class="form-control" id="discord" name="discord" value="{$socials.discord|default:''|escape}">
          </div>
          <div class="mb-3">
            <label for="ms_teams" class="form-label">MS Teams</label>
            <input type="text" class="form-control" id="ms_teams" name="ms_teams" value="{$socials.ms_teams|default:''|escape}">
          </div>
          <div class="mb-3">
            <label for="twitter" class="form-label">Twitter (X)</label>
            <input type="text" class="form-control" id="twitter" name="twitter" value="{$socials.twitter|default:''|escape}">
          </div>
          <div class="mb-3">
            <label for="linkedin" class="form-label">LinkedIn</label>
            <input type="text" class="form-control" id="linkedin" name="linkedin" value="{$socials.linkedin|default:''|escape}">
          </div>
          <div class="mb-3">
            <label for="github" class="form-label">GitHub</label>
            <input type="text" class="form-control" id="github" name="github" value="{$socials.github|default:''|escape}">
          </div>
          <button type="submit" class="btn btn-primary">Speichern</button>
        </form>
      </div>
    </div>
  </div>
</div>

{/if}

{/block}
