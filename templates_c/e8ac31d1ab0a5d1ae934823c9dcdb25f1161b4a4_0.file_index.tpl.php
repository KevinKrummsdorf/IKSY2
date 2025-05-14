<?php
/* Smarty version 5.5.0, created on 2025-05-13 14:31:54
  from 'file:index.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.0',
  'unifunc' => 'content_68233bba21bb55_59022706',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e8ac31d1ab0a5d1ae934823c9dcdb25f1161b4a4' => 
    array (
      0 => 'index.tpl',
      1 => 1746790918,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_68233bba21bb55_59022706 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
$_smarty_tpl->getInheritance()->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_34970749668233bba2153f2_38640088', "title");
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_143699432368233bba21aae6_29255626', "content");
?>

<?php $_smarty_tpl->getInheritance()->endChild($_smarty_tpl, "./layouts/layout.tpl", $_smarty_current_dir);
}
/* {block "title"} */
class Block_34970749668233bba2153f2_38640088 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>
Startseite<?php
}
}
/* {/block "title"} */
/* {block "content"} */
class Block_143699432368233bba21aae6_29255626 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>

<div class="container-fluid my-5">
  <section class="central-content text-center p-3">
    <h1 id="main-heading" class="mb-4">Willkommen auf StudyHub</h1>
    <p class="lead mb-5">Die zentrale Plattform für deine Uni-Skripte, Zusammenfassungen & Lerngruppen</p>

    <section class="mb-5">
      <h2 class="mb-3">Material teilen</h2>
      <p class="mb-3">Lade deine Unterlagen hoch und hilf anderen Studierenden weiter.</p>
      <a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/upload.php" class="btn btn-primary btn-lg" rel="noopener">Jetzt hochladen</a>
    </section>

    <section>
      <h2 class="mb-3">Material finden</h2>
      <p class="mb-3">Durchsuche Skripte, Zusammenfassungen und Mitschriften deiner Uni.</p>
      <a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/browse.php" class="btn btn-primary btn-lg" rel="noopener">Jetzt entdecken</a>
    </section>
  </section>
</div>
<?php
}
}
/* {/block "content"} */
}
