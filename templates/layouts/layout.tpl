<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$app_name} - {block name="title"}Startseite{/block}</title>

  {* Ganz oben im head: recaptchaSiteKey setzen und API laden *}
  <script>
    window.recaptchaSiteKey = '{$recaptcha_site_key}';
    const baseUrl = '{$base_url}';
  </script>
  <script src="https://www.google.com/recaptcha/api.js?render={$recaptcha_site_key}" async defer></script>

  {* Jetzt erst deine CSS-Dateien *}
  <link href="{$base_url}/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="{$base_url}/css/style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

  {* Rest des head-Blocks unverändert (z.B. weitere append-Blocks) *}
  {block name="head" append}{/block}
</head>
<body>
<header class="site-header d-flex justify-content-between align-items-center px-4 py-2">
  <div class="header-logo">
    <a href="{$base_url}/index.php">
      <img src="{$base_url}/assets/logo.svg" alt="StudyHub Logo" width="160">
    </a>
  </div>
  <nav class="header-auth-links">
    {if $isLoggedIn}
      <span class="me-3">Willkommen, {$username}!</span>
      <a href="{$base_url}/logout.php" class="btn btn-sm btn-outline-secondary">Logout</a>
    {else}
      <a href="#" class="btn btn-sm btn-primary me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
      <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal">Registrierung</a>
    {/if}
  </nav>
</header>

<!-- Menü-Button (mobil) -->
<button class="btn btn-primary d-md-none menu-toggle-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">Menü</button>

<!-- Sidebar (Desktop) -->
<div class="menu d-none d-md-block">
  <ul class="menu-content mt-4">
    <li><a href="{$base_url}/profile.php"><span class="material-symbols-outlined">person</span><span>Mein Profil</span></a></li>
    <li><a href="{$base_url}/lerngruppen.php"><span class="material-symbols-outlined">group</span><span>Meine Lerngruppen</span></a></li>
    <li><a href="{$base_url}/nachrichten.php"><span class="material-symbols-outlined">message</span><span>Nachrichten</span></a></li>
    <li><a href="{$base_url}/todos.php"><span class="material-symbols-outlined">checklist</span><span>To Do's</span></a></li>
    <li><a href="{$base_url}/material.php"><span class="material-symbols-outlined">search</span><span>Material finden</span></a></li>
    <li><a href="{$base_url}/upload.php"><span class="material-symbols-outlined">arrow_circle_up</span><span>Material hochladen</span></a></li>
    <li><a href="{$base_url}/einstellungen.php"><span class="material-symbols-outlined">settings</span><span>Einstellungen</span></a></li>
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
      <li><a href="{$base_url}/profile.php"><span class="material-symbols-outlined">person</span><span>Mein Profil</span></a></li>
      <li><a href="{$base_url}/lerngruppen.php"><span class="material-symbols-outlined">group</span><span>Meine Lerngruppen</span></a></li>
      <li><a href="{$base_url}/nachrichten.php"><span class="material-symbols-outlined">message</span><span>Nachrichten</span></a></li>
      <li><a href="{$base_url}/todos.php"><span class="material-symbols-outlined">checklist</span><span>To Do's</span></a></li>
      <li><a href="{$base_url}/material.php"><span class="material-symbols-outlined">search</span><span>Material finden</span></a></li>
      <li><a href="{$base_url}/upload.php"><span class="material-symbols-outlined">arrow_circle_up</span><span>Material hochladen</span></a></li>
      <li><a href="{$base_url}/einstellungen.php"><span class="material-symbols-outlined">settings</span><span>Einstellungen</span></a></li>
    </ul>
  </div>
</div>

<main class="site-main">
  <div class="container mt-3">
    <div id="globalAlert"></div>
  </div>
  {block name="content"}{/block}
</main>


<footer class="text-center mt-4 py-3">
  <div class="container-fluid">
    <a href="{$base_url}/about.php" class="mx-2">Über uns</a> |
    <a href="{$base_url}/privacy.php" class="mx-2">Datenschutz</a> |
    <a href="{$base_url}/terms.php" class="mx-2">AGB</a> |
    <a href="{$base_url}/contact.php" class="mx-2">Kontakt</a> |
    <a href="{$base_url}/impressum.php" class="mx-2">Impressum</a>
    <p class="mt-2 text-center">&copy; {$smarty.now|date_format:"%Y"} {$app_name}. Alle Rechte vorbehalten.</p>
  </div>
</footer>

{include file="partials/modals.tpl"}

<script src="{$base_url}/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="{$base_url}/js/register.js"></script>
<script src="{$base_url}/js/sidebar.js"></script>
<script src="{$base_url}/js/login.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#loginModal') {
      var modalEl = document.getElementById('loginModal');
      if (modalEl) {
        new bootstrap.Modal(modalEl).show();
      }
    }
  });
</script>
</body>
</html>
