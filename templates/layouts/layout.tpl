<!DOCTYPE html>
<html lang="de" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{$csrf_token}">
  <title>{$app_name} - {block name="title"}Startseite{/block}</title>
  <link rel="icon" href="{$base_url}/assets/favicon.ico" type="image/x-icon">

  <script>
    window.recaptchaSiteKey = '{$recaptcha_site_key}';
    const baseUrl = '{$base_url}';
    const usePrettyUrls = {$use_pretty_urls|json_encode};
  </script>
  <script src="https://www.google.com/recaptcha/api.js?render={$recaptcha_site_key}" async defer></script>

  <!-- STYLES -->
  <link href="{$base_url}/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="{$base_url}/css/style.css" rel="stylesheet">
  <link rel="stylesheet" href="{$base_url}/css/material-symbols.css">

  {block name="head" append}{/block}
</head>
<body>

<header class="site-header d-flex justify-content-between align-items-center px-4 py-2">
  <div class="header-logo">
    <a href="{if $isLoggedIn}{url path='dashboard'}{else}{url}{/if}">
      <img src="{$base_url}/assets/logo.svg" alt="StudyHub Logo" width="160">
    </a>
  </div>
  <nav class="header-auth-links">
    {if $isLoggedIn}
      <span class="me-3">Willkommen, {$username|escape}!</span>
      <a href="{url path='logout'}" class="btn btn-sm btn-outline-secondary">Logout</a>
    {else}
      <a href="#" class="btn btn-sm btn-primary me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
      <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal">Registrierung</a>
    {/if}
  </nav>
</header>

<div id="AlertContainer"></div>

{* Flash-Alert *}
{if isset($flash)}
  <div class="container mt-3">
    <div class="alert alert-{$flash.type|default:'info'} alert-dismissible fade show" role="alert">
      {$flash.message|escape}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
    </div>
  </div>
  {if isset($flash.context) && $flash.context == 'login'}
    <script>
      setTimeout(() => window.location.href = '{url path="dashboard"}', 3000);
    </script>
  {/if}
{/if}

<!-- Offcanvas Desktop -->
<button class="btn btn-primary d-md-none menu-toggle-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">Menü</button>

<div class="menu d-none d-md-block">
  <ul class="menu-content mt-4">
    <li><a href="{url path='profile/my'}"><span class="material-symbols-outlined">account_circle</span><span>Mein Profil</span></a></li>
    <li><a href="{url path='search_profile'}"><span class="material-symbols-outlined">person_search</span><span>Andere Mitglieder finden</span></a></li>
    <li><a href="{url path='my_groups'}"><span class="material-symbols-outlined">group</span><span>Meine Lerngruppen</span></a></li>
    <li><a href="{url path='groups'}"><span class="material-symbols-outlined">groups</span><span>Alle Lerngruppen</span></a></li>
    <li><a href="{url path='todos'}"><span class="material-symbols-outlined">checklist</span><span>To Do's</span></a></li>
    <li><a href="{url path='browse'}"><span class="material-symbols-outlined">search</span><span>Material finden</span></a></li>
    <li><a href="{url path='upload'}"><span class="material-symbols-outlined">arrow_circle_up</span><span>Material hochladen</span></a></li>
    <li><a href="{url path='my_uploads'}"><span class="material-symbols-outlined">folder</span><span>Meine Uploads</span></a></li>
    <li><a href="{url path='timetable'}"><span class="material-symbols-outlined">calendar_month</span><span>Stundenplan</span></a></li>
    <li><a href="#" class="theme-toggle"><span class="theme-icon material-symbols-outlined">dark_mode</span><span class="theme-label">Darkmode</span></a></li>

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
      <li><a href="{url path='profile/my'}"><span class="material-symbols-outlined">account_circle</span><span>Mein Profil</span></a></li>
      <li><a href="{url path='search_profile'}"><span class="material-symbols-outlined">person_search</span><span>Andere Mitglieder finden</span></a></li>
      <li><a href="{url path='my_groups'}"><span class="material-symbols-outlined">group</span><span>Meine Lerngruppen</span></a></li>
      <li><a href="{url path='groups'}"><span class="material-symbols-outlined">groups</span><span>Alle Lerngruppen</span></a></li>
      <li><a href="{url path='todos'}"><span class="material-symbols-outlined">checklist</span><span>To Do's</span></a></li>
      <li><a href="{url path='browse'}"><span class="material-symbols-outlined">search</span><span>Material finden</span></a></li>
      <li><a href="{url path='upload'}"><span class="material-symbols-outlined">arrow_circle_up</span><span>Material hochladen</span></a></li>
      <li><a href="{url path='my_uploads'}"><span class="material-symbols-outlined">folder</span><span>Meine Uploads</span></a></li>
      <li><a href="{url path='timetable'}"><span class="material-symbols-outlined">calendar_month</span><span>Stundenplan</span></a></li>
      <li><a href="#" class="theme-toggle"><span class="theme-icon material-symbols-outlined">dark_mode</span><span class="theme-label">Darkmode</span></a></li>

     </ul>
  </div>
</div>

<main class="site-main" aria-labelledby="main-heading">
  <div class="container mt-3">
    {block name="content"}{/block}
  </div>
</main>

<footer class="text-center mt-4 py-3">
  <div class="container-fluid">
    <a href="{url path='about'}" class="mx-2">Über uns</a> |
    <a href="{url path='privacy'}" class="mx-2">Datenschutz</a> |
    <a href="{url path='terms'}" class="mx-2">AGB</a> |
    <a href="{url path='contact'}" class="mx-2">Kontakt</a> |
    <a href="{url path='impressum'}" class="mx-2">Impressum</a>
    <p class="mt-2 text-center">&copy; {$smarty.now|date_format:"%Y"} {$app_name}. Alle Rechte vorbehalten.</p>
  </div>
</footer>

{include file="partials/modals.tpl"}

<!-- Bootstrap & JS -->
<script src="{$base_url}/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="{$base_url}/js/login.js"></script>
<script src="{$base_url}/js/sidebar.js"></script>
<script src="{$base_url}/js/login-success.js"></script>
<script src="{$base_url}/js/theme-toggle.js"></script>
<script src="{$base_url}/js/register.js"></script>
<script src="{$base_url}/js/password-requirements.js"></script>

{block name="scripts"}{/block}

<script>
</script>


</body>
</html>

