{extends file="./layouts/layout.tpl"}

{block name="title"}Profil{/block}

{block name="content"}
<h1 class="text-center">Mein Profil</h1>

<div class="container my-5">
    <div class="profile-box">
        <strong>Name:</strong>
        <p class="text-muted">Max</p>

        <strong>Benutzername:</strong>
        <p class="text-muted">{$username}</p>

        <strong>E-Mail:</strong>
        <p class="text-muted">max@example.com</p>

        <strong>Andere Netzwerke:</strong>
        <p class="text-muted">Instagram, TikTok, Discord, MS Teams</p>

        <section class="text-center">
            <a href="bearbeiten.php" class="btn btn-primary btn-lg mt-30">Profil bearbeiten</a>
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
    </div>
</div>
{/block}
