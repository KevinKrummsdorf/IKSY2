{extends file="./layouts/layout.tpl"}

{block name="title"}Profil{/block}

{block name="content"}
<h1 class="text-center">
    {if $isOwnProfile}
        Mein Profil
    {else}
        Profil von {$profile.username}
    {/if}
</h1>


<div class="container my-5">
    <div class="profile-box">
      
{* PROFILBILD OBEN *}
<div class="text-center mb-4">
    {if $profile.profile_picture}
        <img src="{$base_url}/uploads/profile_pictures/{$profile.profile_picture}" alt="Profilbild"
             class="rounded-circle shadow mb-2" style="max-width: 150px;">
    {else}
        <img src="{$base_url}/assets/default_person.png" alt="Kein Profilbild"
             class="rounded-circle shadow mb-2" style="max-width: 150px;">
    {/if}
</div>
       

        {* PERSÖNLICHE INFOS *}
        <div class="card mb-4">
            <div class="card-body">
                

                <strong>Benutzername:</strong>
                <p class="text-muted">{$profile.username}</p>

                <strong>Vorname:</strong>
                <p class="text-muted">{$profile.first_name}</p>

                <strong>Nachname:</strong>
                <p class="text-muted">{$profile.last_name}</p>

                <strong>Geburtsdatum:</strong>
                <p class="text-muted">
                    {if $profile.birthdate}{$profile.birthdate|date_format:"%d.%m.%Y"}{else}-{/if}
                </p>

                <strong>Wohnort:</strong>
                <p class="text-muted">{$profile.location}</p>

                <strong>Über mich:</strong>
                <p class="text-muted">{$profile.about_me|default:"Noch nichts eingetragen."}</p>

                {if $profile.instagram || $profile.tiktok || $profile.discord || $profile.ms_teams}
    <strong>Andere Netzwerke:</strong>
    <ul class="text-muted list-unstyled">
        {if $profile.instagram}<li>📸 Instagram: {$profile.instagram}</li>{/if}
        {if $profile.tiktok}<li>🎵 TikTok: {$profile.tiktok}</li>{/if}
        {if $profile.discord}<li>💬 Discord: {$profile.discord}</li>{/if}
        {if $profile.ms_teams}<li>🧑‍💼 MS Teams: {$profile.ms_teams}</li>{/if}
    </ul>
{/if}
        </div>
        
{if $isOwnProfile}
        <section class="text-center">
            <a href="edit_profile.php" class="btn btn-primary btn-lg mt-30">Profil bearbeiten</a>
        </section>
        

        <hr class="my-5">

        <h3 class="mb-3">Zwei-Faktor-Authentifizierung</h3>

        {if isset($success)}
            <div class="alert alert-success">{$success}</div>
        {/if}

        {if isset($message)}
            <div class="alert alert-danger">{$message}</div>
        {/if}

        {if $twofa_enabled}
            <p>2FA ist <strong>aktiviert</strong>.</p>
            <!-- Modal-Button -->
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDisable2FAModal">
                2FA deaktivieren
            </button>

            <!-- Bootstrap Modal -->
            <div class="modal fade" id="confirmDisable2FAModal" tabindex="-1" aria-labelledby="confirmDisable2FALabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="post">
                            <input type="hidden" name="action" value="disable_2fa">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmDisable2FALabel">2FA deaktivieren</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                            </div>
                            <div class="modal-body">
                                Möchtest du die Zwei-Faktor-Authentifizierung wirklich deaktivieren?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                                <button type="submit" class="btn btn-danger">Ja, deaktivieren</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        {elseif $show_2fa_form}
            <p>Scanne diesen QR-Code mit deiner Authenticator-App und gib den Bestätigungscode ein:</p>
            <img src="{$qrCodeUrl}" alt="QR Code" class="img-fluid mb-3" style="max-width: 200px;">

            <form method="post" class="mb-3 needs-validation" novalidate>
                <input type="hidden" name="action" value="confirm_2fa">
                <div class="mb-2">
                    <label for="code" class="form-label">Bestätigungscode:</label>
                    <input type="text"
                           name="code"
                           id="code"
                           class="form-control"
                           required
                           inputmode="numeric"
                           maxlength="6"
                           autocomplete="one-time-code"
                           placeholder="6-stelliger Code"
                           title="Bitte genau 6 Ziffern eingeben">
                    <div class="invalid-feedback">Bitte genau 6 Ziffern eingeben.</div>
                </div>
                <button type="submit" class="btn btn-success">2FA aktivieren</button>
            </form>

            <script>
                // Bootstrap Validierung & Eingabe filtern
                (() => {
                    'use strict';
                    const forms = document.querySelectorAll('.needs-validation');
                    Array.from(forms).forEach(form => {
                        form.addEventListener('submit', event => {
                            if (!form.checkValidity()) {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                            form.classList.add('was-validated');
                        }, false);
                    });

                    const codeInput = document.getElementById('code');
                    codeInput?.addEventListener('input', e => {
                        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 6);
                    });
                })();
            </script>
        {else}
            <p>2FA ist <strong>nicht aktiviert</strong>.</p>
            <form method="post">
                <input type="hidden" name="action" value="start_2fa">
                <button type="submit" class="btn btn-primary">2FA einrichten</button>
            </form>
        {/if}
        <hr class="my-5">
        <h3 class="mb-3">Passwort ändern</h3>
        {if isset($pw_success)}
            <div class="alert alert-success">{$pw_success}</div>
        {/if}
        {if isset($pw_message)}
            <div class="alert alert-danger">{$pw_message}</div>
        {/if}
        <div id="pwFormAlert" class="alert alert-danger d-none">Bitte alle Felder ausfüllen.</div>
        <form id="pwChangeForm" method="post" class="needs-validation" novalidate>
            <input type="hidden" name="action" value="change_password">
            <div class="mb-3">
                <label for="old_password" class="form-label">Aktuelles Passwort</label>
                <input type="password" class="form-control" id="old_password" name="old_password" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">Neues Passwort</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="new_password_confirm" class="form-label">Passwort bestätigen</label>
                <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm" required>
            </div>
            <button type="submit" class="btn btn-primary">Passwort speichern</button>
        </form>
        {/if}
        <script>
            (() => {
                'use strict';
                const form = document.getElementById('pwChangeForm');
                const alertBox = document.getElementById('pwFormAlert');
                form?.addEventListener('submit', e => {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        alertBox.classList.remove('d-none');
                    } else {
                        alertBox.classList.add('d-none');
                    }
                    form.classList.add('was-validated');
                });
            })();
        </script>
    </div>
</div>
{/block}
