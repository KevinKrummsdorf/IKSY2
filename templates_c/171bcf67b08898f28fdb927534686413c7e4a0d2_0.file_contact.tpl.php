<?php
/* Smarty version 5.5.0, created on 2025-05-13 16:38:08
  from 'file:contact.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.0',
  'unifunc' => 'content_68235950df79e7_23532458',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '171bcf67b08898f28fdb927534686413c7e4a0d2' => 
    array (
      0 => 'contact.tpl',
      1 => 1746810709,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_68235950df79e7_23532458 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
$_smarty_tpl->getInheritance()->init($_smarty_tpl, true);
?>


<?php if ($_smarty_tpl->getValue('errors')) {?>
  <div class="alert alert-danger">
    <ul>
    <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('errors'), 'err');
$foreach0DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('err')->value) {
$foreach0DoElse = false;
?>
      <li><?php echo $_smarty_tpl->getValue('err');?>
</li>
    <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
    </ul>
  </div>
<?php }?>

<?php if ($_smarty_tpl->getValue('success')) {?>
  <div class="alert alert-success">
    Deine Nachricht (ID: <?php echo $_smarty_tpl->getValue('contactId');?>
) wurde erfolgreich versendet!
  </div>
<?php }?>

<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_160028373468235950dd9b18_16211995', "head");
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_183166334868235950de09a3_43035061', "content");
?>

<?php $_smarty_tpl->getInheritance()->endChild($_smarty_tpl, "./layouts/layout.tpl", $_smarty_current_dir);
}
/* {block "head"} */
class Block_160028373468235950dd9b18_16211995 extends \Smarty\Runtime\Block
{
public $append = 'true';
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>

  <?php echo '<script'; ?>
 src="https://www.google.com/recaptcha/api.js?render=<?php echo $_smarty_tpl->getValue('recaptcha_site_key');?>
"><?php echo '</script'; ?>
>
  <style>
  </style>
<?php
}
}
/* {/block "head"} */
/* {block "content"} */
class Block_183166334868235950de09a3_43035061 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>


<div class="container my-5">
  <h1 id="main-heading" class="text-center mb-4">Kontakt</h1>

  <?php if ($_smarty_tpl->getValue('success')) {?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      Ihre Nachricht wurde erfolgreich gesendet.<br>
      <?php if ($_smarty_tpl->getValue('contactId')) {?>
        <strong>Ihre Kontakt-ID:</strong> <?php echo $_smarty_tpl->getValue('contactId');?>

      <?php }?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
    </div>
  <?php }?>

  <?php if ($_smarty_tpl->getValue('errors')) {?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('errors'), 'err');
$foreach1DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('err')->value) {
$foreach1DoElse = false;
?>
          <li><?php echo $_smarty_tpl->getValue('err');?>
</li>
        <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
    </div>
  <?php }?>

  <form id="contact-form" action="" method="POST" class="mx-auto" style="max-width:600px">
    <div class="mb-3">
      <label for="name" class="form-label">Name</label>
      <input id="name" name="name" type="text" class="form-control"
             value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('input')['name'], ENT_QUOTES, 'UTF-8', true);?>
" required>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">E-Mail</label>
      <input id="email" name="email" type="email" class="form-control"
             value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('input')['email'], ENT_QUOTES, 'UTF-8', true);?>
" required>
    </div>

    <div class="mb-3">
      <label for="subject" class="form-label">Betreff</label>
      <input id="subject" name="subject" type="text" class="form-control"
             value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('input')['subject'], ENT_QUOTES, 'UTF-8', true);?>
" required>
    </div>

    <div class="mb-3">
      <label for="message" class="form-label">Nachricht</label>
      <textarea id="message" name="message" rows="6" class="form-control" required><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('input')['message'], ENT_QUOTES, 'UTF-8', true);?>
</textarea>
    </div>

    <input type="hidden" name="recaptcha_token" id="recaptcha_token">

    <button type="submit" class="btn btn-primary w-100 position-relative">
      <span class="spinner-border spinner-border-sm me-2 d-none"
            id="btn-spinner" role="status"></span>
      Nachricht senden
    </button>
    <br></br>
    <div class="text-center mt-4">
      <h3>Oder kontaktieren Sie uns direkt:</h3>
      <p>E-Mail: <a href="mailto:studyhub.iksy@gmail.com">studyhub.iksy@gmail.com</a></p>
      <p>Servicezeiten: Montags bis Freitags 9:00 – 17:00 Uhr</p>
    </div>
  </form>
</div>

<?php echo '<script'; ?>
>
document.addEventListener('DOMContentLoaded', function() {
  const form    = document.getElementById('contact-form');
  const btn     = form.querySelector('button[type="submit"]');
  const spinner = document.getElementById('btn-spinner');
  const token   = document.getElementById('recaptcha_token');

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    btn.disabled = true;
    spinner.classList.remove('d-none');

    grecaptcha.ready(function() {
      grecaptcha.execute('<?php echo $_smarty_tpl->getValue('recaptcha_site_key');?>
', {action:'contact'})
        .then(function(t) {
          token.value = t;
          form.submit();
        })
        .catch(function() {
          // reCAPTCHA-Fehler: Button wieder aktivieren
          btn.disabled = false;
          spinner.classList.add('d-none');
          alert('Fehler bei der reCAPTCHA-Verifizierung. Bitte Seite neu laden.');
        });
    });
  });
});
<?php echo '</script'; ?>
>

<?php
}
}
/* {/block "content"} */
}
