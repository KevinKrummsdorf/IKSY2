<?php
/* Smarty version 5.5.0, created on 2025-05-13 15:51:53
  from 'file:profile.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.0',
  'unifunc' => 'content_68234e79c1eca7_96994349',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '425a474aeb478475fa8c744792d1880ed554688d' => 
    array (
      0 => 'profile.tpl',
      1 => 1747135408,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_68234e79c1eca7_96994349 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
$_smarty_tpl->getInheritance()->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_120139254868234e79c09947_25835806', "title");
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_30587995368234e79c0ef10_14993999', "content");
?>

<?php $_smarty_tpl->getInheritance()->endChild($_smarty_tpl, "./layouts/layout.tpl", $_smarty_current_dir);
}
/* {block "title"} */
class Block_120139254868234e79c09947_25835806 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>
Profil<?php
}
}
/* {/block "title"} */
/* {block "content"} */
class Block_30587995368234e79c0ef10_14993999 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>

<h1 class="text-center">Mein Profil</h1>

<div class="container my-5">
    <div class="profile-box">
        <strong>Name:</strong>
        <p class="text-muted">Max</p>

        <strong>Benutzername:</strong>
        <p class="text-muted"><?php echo $_smarty_tpl->getValue('username');?>
</p>

        <strong>E-Mail:</strong>
        <p class="text-muted">max@example.com</p>

        <strong>Andere Netzwerke:</strong>
        <p class="text-muted">Instagram, TikTok, Discord, MS Teams</p>

        <section class="text-center">
            <a href="bearbeiten.php" class="btn btn-primary btn-lg mt-30">Profil bearbeiten</a>
        </section>

        <hr class="my-5">

        <h3 class="mb-3">Zwei-Faktor-Authentifizierung</h3>

        <?php if ((true && ($_smarty_tpl->hasVariable('success') && null !== ($_smarty_tpl->getValue('success') ?? null)))) {?>
            <div class="alert alert-success"><?php echo $_smarty_tpl->getValue('success');?>
</div>
        <?php }?>

        <?php if ((true && ($_smarty_tpl->hasVariable('message') && null !== ($_smarty_tpl->getValue('message') ?? null)))) {?>
            <div class="alert alert-danger"><?php echo $_smarty_tpl->getValue('message');?>
</div>
        <?php }?>

        <?php if ($_smarty_tpl->getValue('twofa_enabled')) {?>
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
        <?php } elseif ($_smarty_tpl->getValue('show_2fa_form')) {?>
            <p>Scanne diesen QR-Code mit deiner Authenticator-App und gib den Bestätigungscode ein:</p>
            <img src="<?php echo $_smarty_tpl->getValue('qrCodeUrl');?>
" alt="QR Code" class="img-fluid mb-3" style="max-width: 200px;">

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

            <?php echo '<script'; ?>
>
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
            <?php echo '</script'; ?>
>
        <?php } else { ?>
            <p>2FA ist <strong>nicht aktiviert</strong>.</p>
            <form method="post">
                <input type="hidden" name="action" value="start_2fa">
                <button type="submit" class="btn btn-primary">2FA einrichten</button>
            </form>
        <?php }?>
    </div>
</div>
<?php
}
}
/* {/block "content"} */
}
