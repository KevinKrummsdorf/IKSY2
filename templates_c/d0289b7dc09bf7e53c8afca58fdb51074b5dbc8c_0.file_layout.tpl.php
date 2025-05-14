<?php
/* Smarty version 5.5.0, created on 2025-05-13 14:31:54
  from 'file:C:\xampp\htdocs\www\IKSY2\templates\./layouts/layout.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.0',
  'unifunc' => 'content_68233bba471ae9_64976976',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'd0289b7dc09bf7e53c8afca58fdb51074b5dbc8c' => 
    array (
      0 => 'C:\\xampp\\htdocs\\www\\IKSY2\\templates\\./layouts/layout.tpl',
      1 => 1746803840,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:partials/modals.tpl' => 1,
  ),
))) {
function content_68233bba471ae9_64976976 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates\\layouts';
$_smarty_tpl->getInheritance()->init($_smarty_tpl, false);
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $_smarty_tpl->getValue('app_name');?>
 - <?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_166511494268233bba44e310_78868175', "title");
?>
</title>

  <?php echo '<script'; ?>
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
  <link rel="stylesheet" href="/css/material-symbols.css">

  <?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_96591221368233bba44ff47_60765009', "head");
?>

</head>
<body>

<header class="site-header d-flex justify-content-between align-items-center px-4 py-2">
  <div class="header-logo">
    <a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/index.php">
      <img src="<?php echo $_smarty_tpl->getValue('base_url');?>
/assets/logo.svg" alt="StudyHub Logo" width="160">
    </a>
  </div>
  <nav class="header-auth-links">
    <?php if ($_smarty_tpl->getValue('isLoggedIn')) {?>
      <span class="me-3">Willkommen, <?php echo $_smarty_tpl->getValue('username');?>
!</span>
      <a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/logout.php" class="btn btn-sm btn-outline-secondary">Logout</a>
    <?php } else { ?>
      <a href="#" class="btn btn-sm btn-primary me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
      <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal">Registrierung</a>
    <?php }?>
  </nav>
</header>

<div id="AlertContainer"></div>

<?php if ((true && ($_smarty_tpl->hasVariable('flash') && null !== ($_smarty_tpl->getValue('flash') ?? null)))) {?>
    
  <div class="container mt-3">
    <div class="alert alert-<?php echo (($tmp = $_smarty_tpl->getValue('flash')['type'] ?? null)===null||$tmp==='' ? 'info' ?? null : $tmp);?>
 alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars((string)$_smarty_tpl->getValue('flash')['message'], ENT_QUOTES, 'UTF-8', true);?>

      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
    </div>
  </div>

    <?php if ((true && (true && null !== ($_smarty_tpl->getValue('flash')['context'] ?? null))) && $_smarty_tpl->getValue('flash')['context'] == 'login') {?>
    <?php echo '<script'; ?>
>
      setTimeout(() => window.location.href = 'dashboard.php', 3000);
    <?php echo '</script'; ?>
>
  <?php }
}?>

<!-- Menü-Button (mobil) -->
<button class="btn btn-primary d-md-none menu-toggle-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">Menü</button>

<!-- Sidebar (Desktop) -->
<div class="menu d-none d-md-block">
  <ul class="menu-content mt-4">
    <li><a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/profile.php"><span class="material-symbols-outlined">account_circle</span><span>Mein Profil</span></a></li>
    <li><a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/lerngruppen.php"><span class="material-symbols-outlined">group</span><span>Meine Lerngruppen</span></a></li>
    <li><a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/nachrichten.php"><span class="material-symbols-outlined">message</span><span>Nachrichten</span></a></li>
    <li><a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/todos.php"><span class="material-symbols-outlined">checklist</span><span>To Do's</span></a></li>
    <li><a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/material.php"><span class="material-symbols-outlined">search</span><span>Material finden</span></a></li>
    <li><a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/upload.php"><span class="material-symbols-outlined">arrow_circle_up</span><span>Material hochladen</span></a></li>
    <li><a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/einstellungen.php"><span class="material-symbols-outlined">settings</span><span>Einstellungen</span></a></li>
  </ul>
</div>

<!-- Offcanvas (mobil) -->
<div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="sidebarOffcanvas">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Menü</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Schließen"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="menu-content">
      <li><a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/profile.php"><span class="material-symbols-outlined">account_circle</span><span>Mein Profil</span></a></li>
      <li><a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/lerngruppen.php"><span class="material-symbols-outlined">group</span><span>Meine Lerngruppen</span></a></li>
      <li><a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/nachrichten.php"><span class="material-symbols-outlined">message</span><span>Nachrichten</span></a></li>
      <li><a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/todos.php"><span class="material-symbols-outlined">checklist</span><span>To Do's</span></a></li>
      <li><a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/material.php"><span class="material-symbols-outlined">search</span><span>Material finden</span></a></li>
      <li><a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/upload.php"><span class="material-symbols-outlined">arrow_circle_up</span><span>Material hochladen</span></a></li>
      <li><a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/einstellungen.php"><span class="material-symbols-outlined">settings</span><span>Einstellungen</span></a></li>
    </ul>
  </div>
</div>

<main class="site-main" aria-labelledby="main-heading">
  <div class="container mt-3">
    <?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_200285580068233bba4626d0_33607173', "content");
?>

  </div>
</main>

<footer class="text-center mt-4 py-3">
  <div class="container-fluid">
    <a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/about.php" class="mx-2">Über uns</a> |
    <a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/privacy.php" class="mx-2">Datenschutz</a> |
    <a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/terms.php" class="mx-2">AGB</a> |
    <a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/contact.php" class="mx-2">Kontakt</a> |
    <a href="<?php echo $_smarty_tpl->getValue('base_url');?>
/impressum.php" class="mx-2">Impressum</a>
    <p class="mt-2 text-center">&copy; <?php echo $_smarty_tpl->getSmarty()->getModifierCallback('date_format')(time(),"%Y");?>
 <?php echo $_smarty_tpl->getValue('app_name');?>
. Alle Rechte vorbehalten.</p>
  </div>
</footer>

<?php $_smarty_tpl->renderSubTemplate("file:partials/modals.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), (int) 0, $_smarty_current_dir);
?>

<!-- JS-Dateien -->
<?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->getValue('base_url');?>
/vendor/bootstrap/js/bootstrap.bundle.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->getValue('base_url');?>
/js/login.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->getValue('base_url');?>
/js/register.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->getValue('base_url');?>
/js/sidebar.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->getValue('base_url');?>
/js/login-success.js"><?php echo '</script'; ?>
>

<?php echo '<script'; ?>
>
  document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#loginModal') {
      const modalEl = document.getElementById('loginModal');
      if (modalEl) {
        new bootstrap.Modal(modalEl).show();
      }
    }
  });
<?php echo '</script'; ?>
>
</body>
</html>
<?php }
/* {block "title"} */
class Block_166511494268233bba44e310_78868175 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates\\layouts';
?>
Startseite<?php
}
}
/* {/block "title"} */
/* {block "head"} */
class Block_96591221368233bba44ff47_60765009 extends \Smarty\Runtime\Block
{
public $append = 'true';
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates\\layouts';
}
}
/* {/block "head"} */
/* {block "content"} */
class Block_200285580068233bba4626d0_33607173 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates\\layouts';
}
}
/* {/block "content"} */
}
