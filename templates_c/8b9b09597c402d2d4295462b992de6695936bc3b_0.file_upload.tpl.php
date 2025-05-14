<?php
/* Smarty version 5.5.0, created on 2025-05-14 13:45:32
  from 'file:upload.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.0',
  'unifunc' => 'content_6824825cb04b87_13429398',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '8b9b09597c402d2d4295462b992de6695936bc3b' => 
    array (
      0 => 'upload.tpl',
      1 => 1746879998,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_6824825cb04b87_13429398 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
$_smarty_tpl->getInheritance()->init($_smarty_tpl, true);
?>

<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_8593683786824825cad9674_37271878', "title");
?>

<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_20296916876824825cae1ce1_78034692', "content");
?>

<?php $_smarty_tpl->getInheritance()->endChild($_smarty_tpl, "./layouts/layout.tpl", $_smarty_current_dir);
}
/* {block "title"} */
class Block_8593683786824825cad9674_37271878 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>
Material hochladen<?php
}
}
/* {/block "title"} */
/* {block "content"} */
class Block_20296916876824825cae1ce1_78034692 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>

<div class="container">
    <h1 class="mb-4 text-center">Material hochladen</h1>
    <?php if ((true && ($_smarty_tpl->hasVariable('error') && null !== ($_smarty_tpl->getValue('error') ?? null)))) {?>
    <div class="alert alert-danger"><?php echo $_smarty_tpl->getValue('error');?>
</div>
    <?php }?>
    <?php if ((true && ($_smarty_tpl->hasVariable('success') && null !== ($_smarty_tpl->getValue('success') ?? null)))) {?>
    <div class="alert alert-success"><?php echo $_smarty_tpl->getValue('success');?>
</div>
    <?php }?>

    <form action="<?php echo $_smarty_tpl->getValue('base_url');?>
/upload.php" method="post" enctype="multipart/form-data">
        <!-- CSRF-Token -->
        <input type="hidden" name="csrf_token" value="<?php echo $_smarty_tpl->getValue('csrf_token');?>
">

        <div class="mb-3">
            <label for="title" class="form-label">Titel</label>
            <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('title'), ENT_QUOTES, 'UTF-8', true);?>
" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Beschreibung</label>
            <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('description'), ENT_QUOTES, 'UTF-8', true);?>
</textarea>
        </div>

        <div class="mb-3">
            <label for="course" class="form-label">Kurs</label>
            <select id="course" name="course" class="form-select" required>
                <option value="" disabled <?php if (!$_smarty_tpl->getValue('selectedCourse')) {?>selected<?php }?>>Bitte wählen...</option>
                <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('courses'), 'course');
$foreach0DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('course')->value) {
$foreach0DoElse = false;
?>
                <option value="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('course')['value'], ENT_QUOTES, 'UTF-8', true);?>
" <?php if ($_smarty_tpl->getValue('course')['value'] == $_smarty_tpl->getValue('selectedCourse')) {?>selected<?php }?>><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('course')['name'], ENT_QUOTES, 'UTF-8', true);?>
</option>
                <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
            </select>
        </div>

        <div class="mb-3">
            <label for="file" class="form-label">Datei auswählen</label>
            <input type="file" id="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.txt" required>
            <div class="form-text">Erlaubte Dateitypen: PDF, JPG, PNG, TXT. Max. 10 MB.</div>
        </div>

        <button type="submit" class="btn btn-primary">Hochladen</button>
    </form>
</div>
<?php
}
}
/* {/block "content"} */
}
