<?php
/* Smarty version 5.5.0, created on 2025-05-13 16:38:00
  from 'file:about.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.0',
  'unifunc' => 'content_68235948bb5586_64727735',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '02daa2ebd5e0d94b4bac244255249c20b5921693' => 
    array (
      0 => 'about.tpl',
      1 => 1746729837,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_68235948bb5586_64727735 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
$_smarty_tpl->getInheritance()->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_176141031168235948b9cac7_49967467', "title");
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_24931346868235948ba2219_65250471', "content");
?>

<?php $_smarty_tpl->getInheritance()->endChild($_smarty_tpl, "./layouts/layout.tpl", $_smarty_current_dir);
}
/* {block "title"} */
class Block_176141031168235948b9cac7_49967467 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>
Über uns – <?php echo $_smarty_tpl->getValue('app_name');
}
}
/* {/block "title"} */
/* {block "content"} */
class Block_24931346868235948ba2219_65250471 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>


  <h1 id="main-heading" class="text-center mb-5">Über uns</h1>

  <div class="container text-center mb-5">
    <h2 class="lead">
      Wir sind ein kleines, engagiertes Team mit einer gemeinsamen Mission:
      digitale Lösungen zu schaffen, die das Leben einfacher machen.<br>
      Was uns auszeichnet? Leidenschaft, Teamgeist und jede Menge Herzblut.
    </h2>
  </div>

  <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('team'), 'member');
$foreach0DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('member')->value) {
$foreach0DoElse = false;
?>
    <section class="container my-5">
      <div class="row justify-content-center align-items-center">
        <div class="col-md-4 text-center mb-3 mb-md-0">
          <div class="team-photo-wrapper mx-auto rounded shadow overflow-hidden" style="max-width: 500px;">
            <img src="<?php echo $_smarty_tpl->getValue('base_url');?>
/assets/<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('member')['img'], ENT_QUOTES, 'UTF-8', true);?>
" class="img-fluid" alt="<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('member')['name'], ENT_QUOTES, 'UTF-8', true);?>
">
          </div>
          <h4 class="mt-3"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('member')['name'], ENT_QUOTES, 'UTF-8', true);?>
</h4>
        </div>
        <div class="col-md-8">
          <p><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('member')['bio'], ENT_QUOTES, 'UTF-8', true);?>
</p>
        </div>
      </div>
    </section>
  <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>

  <div class="container text-center my-5">
    <h3 class="fw-normal">
      Dieses Projekt wurde mit viel Engagement von unseren Dozenten unterstützt:<br>
      <a href="https://www.hochschule-bochum.de/fbw/service/labor-fuer-wirtschaftsinformatik/" target="_blank" rel="noopener">Frank Brockmann</a> und
      <a href="https://www.hochschule-bochum.de/fbw/service/labor-fuer-wirtschaftsinformatik/" target="_blank" rel="noopener">Christoph Schennonek</a>.
    </h3>
  </div>
<?php
}
}
/* {/block "content"} */
}
