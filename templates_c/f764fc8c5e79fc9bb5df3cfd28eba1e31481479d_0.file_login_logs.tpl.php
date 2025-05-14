<?php
/* Smarty version 5.5.0, created on 2025-05-14 13:42:44
  from 'file:login_logs.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.0',
  'unifunc' => 'content_682481b43f10d6_49714793',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f764fc8c5e79fc9bb5df3cfd28eba1e31481479d' => 
    array (
      0 => 'login_logs.tpl',
      1 => 1746882250,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_682481b43f10d6_49714793 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
$_smarty_tpl->getInheritance()->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_395349386682481b38e4dc0_05682088', "title");
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_1817329184682481b3a84ae0_87378446', "content");
?>

<?php $_smarty_tpl->getInheritance()->endChild($_smarty_tpl, "./layouts/layout.tpl", $_smarty_current_dir);
}
/* {block "title"} */
class Block_395349386682481b38e4dc0_05682088 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>
Login-Logs Übersicht<?php
}
}
/* {/block "title"} */
/* {block "content"} */
class Block_1817329184682481b3a84ae0_87378446 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>

<div class="container mt-5">
  <h1>Login-Logs</h1>

  <?php if ($_smarty_tpl->getSmarty()->getModifierCallback('count')($_smarty_tpl->getValue('login_logs')) > 0) {?>
    <div class="table-responsive shadow-sm">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>User ID</th>
            <th>IP (anonymisiert)</th>
            <th>Erfolg</th>
            <th>Grund</th>
            <th>Zeitpunkt</th>
          </tr>
        </thead>
        <tbody>
          <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('login_logs'), 'log', false, NULL, 'logs', array (
  'index' => true,
));
$foreach0DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('log')->value) {
$foreach0DoElse = false;
$_smarty_tpl->tpl_vars['__smarty_foreach_logs']->value['index']++;
?>
            <tr>
              <td><?php echo ($_smarty_tpl->getValue('__smarty_foreach_logs')['index'] ?? null)+1+($_smarty_tpl->getValue('currentPage')-1)*25;?>
</td>
              <td><?php echo (($tmp = $_smarty_tpl->getValue('log')['username'] ?? null)===null||$tmp==='' ? '–' ?? null : $tmp);?>
</td>
              <td><?php echo (($tmp = $_smarty_tpl->getValue('log')['user_id'] ?? null)===null||$tmp==='' ? '–' ?? null : $tmp);?>
</td>
              <td><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('log')['ip_address'], ENT_QUOTES, 'UTF-8', true);?>
</td>
              <td class="text-center">
                <?php if ($_smarty_tpl->getValue('log')['success']) {?>
                  <span class="badge bg-success">Ja</span>
                <?php } else { ?>
                  <span class="badge bg-danger">Nein</span>
                <?php }?>
              </td>
              <td><?php echo htmlspecialchars((string)(($tmp = $_smarty_tpl->getValue('log')['reason'] ?? null)===null||$tmp==='' ? '–' ?? null : $tmp), ENT_QUOTES, 'UTF-8', true);?>
</td>
              <td><?php echo $_smarty_tpl->getValue('log')['created_at'];?>
</td>
            </tr>
          <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
        </tbody>
      </table>
    </div>

        <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center mt-4">
        <?php if ($_smarty_tpl->getValue('currentPage') > 1) {?>
          <li class="page-item">
            <a class="page-link" href="?page=<?php echo $_smarty_tpl->getValue('currentPage')-1;?>
" aria-label="Previous">
              &laquo; Zurück
            </a>
          </li>
        <?php }?>

        <?php
$__section_page_0_loop = (is_array(@$_loop=$_smarty_tpl->getValue('totalPages')+1) ? count($_loop) : max(0, (int) $_loop));
$__section_page_0_start = min(1, $__section_page_0_loop);
$__section_page_0_total = min(($__section_page_0_loop - $__section_page_0_start), $__section_page_0_loop);
$_smarty_tpl->tpl_vars['__smarty_section_page'] = new \Smarty\Variable(array());
if ($__section_page_0_total !== 0) {
for ($__section_page_0_iteration = 1, $_smarty_tpl->tpl_vars['__smarty_section_page']->value['index'] = $__section_page_0_start; $__section_page_0_iteration <= $__section_page_0_total; $__section_page_0_iteration++, $_smarty_tpl->tpl_vars['__smarty_section_page']->value['index']++){
?>
          <li class="page-item <?php if (($_smarty_tpl->getValue('__smarty_section_page')['index'] ?? null) == $_smarty_tpl->getValue('currentPage')) {?>active<?php }?>">
            <a class="page-link" href="?page=<?php echo ($_smarty_tpl->getValue('__smarty_section_page')['index'] ?? null);?>
">
              <?php echo ($_smarty_tpl->getValue('__smarty_section_page')['index'] ?? null);?>

            </a>
          </li>
        <?php
}
}
?>

        <?php if ($_smarty_tpl->getValue('currentPage') < $_smarty_tpl->getValue('totalPages')) {?>
          <li class="page-item">
            <a class="page-link" href="?page=<?php echo $_smarty_tpl->getValue('currentPage')+1;?>
" aria-label="Next">
              Weiter &raquo;
            </a>
          </li>
        <?php }?>
      </ul>
    </nav>

  <?php } else { ?>
    <div class="alert alert-info">Keine Login-Logs gefunden.</div>
  <?php }?>
</div>
  <div class="mt-4">
    <a href="dashboard.php" class="btn btn-sm btn-primary">Zurück zum Dashboard</a>
  </div>
<?php
}
}
/* {/block "content"} */
}
