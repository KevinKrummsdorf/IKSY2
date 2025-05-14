<?php
/* Smarty version 5.5.0, created on 2025-05-13 16:32:18
  from 'file:2fa_login.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.0',
  'unifunc' => 'content_682357f23d6b78_59914547',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '4c0c34435c85283546a4a61b9560ff8f72f4a2cc' => 
    array (
      0 => '2fa_login.tpl',
      1 => 1747146715,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_682357f23d6b78_59914547 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
$_smarty_tpl->getInheritance()->init($_smarty_tpl, false);
echo '<script'; ?>
>
    window.recaptchaSiteKey = '<?php echo $_smarty_tpl->getValue('recaptcha_site_key');?>
';
    const baseUrl = '<?php echo $_smarty_tpl->getValue('base_url');?>
';
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="https://www.google.com/recaptcha/api.js?render=<?php echo $_smarty_tpl->getValue('recaptcha_site_key');?>
" async defer><?php echo '</script'; ?>
>

<link href="<?php echo $_smarty_tpl->getValue('base_url');?>
/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="<?php echo $_smarty_tpl->getValue('base_url');?>
/css/style.css" rel="stylesheet">

<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_1289527302682357f23c1b71_45521371', "content");
?>

<?php }
/* {block "content"} */
class Block_1289527302682357f23c1b71_45521371 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center mb-4">2-Faktor-Authentifizierung</h2>

            <?php if ((true && (true && null !== ($_smarty_tpl->getValue('flash')['message'] ?? null))) || (true && ($_smarty_tpl->hasVariable('message') && null !== ($_smarty_tpl->getValue('message') ?? null)))) {?>
                <div class="alert alert-<?php if ((true && (true && null !== ($_smarty_tpl->getValue('flash')['type'] ?? null)))) {
echo (($tmp = $_smarty_tpl->getValue('flash')['type'] ?? null)===null||$tmp==='' ? 'info' ?? null : $tmp);
} else { ?>danger<?php }?>">
                    <?php if ((true && (true && null !== ($_smarty_tpl->getValue('flash')['message'] ?? null)))) {?>
                        <?php echo $_smarty_tpl->getValue('flash')['message'];?>

                    <?php } else { ?>
                        <?php echo $_smarty_tpl->getValue('message');?>

                    <?php }?>
                </div>
            <?php }?>

            <form method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="code" class="form-label">Bestätigungscode</label>
                    <input type="text"
                           class="form-control"
                           id="code"
                           name="code"
                           inputmode="numeric"
                           maxlength="6"
                           required
                           autocomplete="one-time-code"
                           placeholder="123456"
                           title="Bitte genau 6 Ziffern eingeben">
                    <div class="invalid-feedback">
                        Bitte gib einen gültigen 6-stelligen Code ein.
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Bestätigen</button>
            </form>
        </div>
    </div>
</div>

<?php echo '<script'; ?>
>
(() => {
    'use strict';
    const form = document.querySelector('.needs-validation');
    form?.addEventListener('submit', e => {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Eingabe auf 6 Ziffern begrenzen (nur Zahlen)
    const input = document.getElementById('code');
    input?.addEventListener('input', e => {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 6);
    });
})();
<?php echo '</script'; ?>
>
<?php
}
}
/* {/block "content"} */
}
