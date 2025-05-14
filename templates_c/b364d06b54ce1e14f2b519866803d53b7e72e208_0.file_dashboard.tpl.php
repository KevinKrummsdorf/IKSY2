<?php
/* Smarty version 5.5.0, created on 2025-05-13 16:37:51
  from 'file:dashboard.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.0',
  'unifunc' => 'content_6823593f0a6b14_54738199',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'b364d06b54ce1e14f2b519866803d53b7e72e208' => 
    array (
      0 => 'dashboard.tpl',
      1 => 1747147047,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_6823593f0a6b14_54738199 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
$_smarty_tpl->getInheritance()->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_16053126266823593f05df59_67044657', "title");
?>


<?php if ((true && ($_smarty_tpl->hasVariable('flash') && null !== ($_smarty_tpl->getValue('flash') ?? null)))) {?>
  <div class="alert alert-<?php echo (($tmp = $_smarty_tpl->getValue('flash')['type'] ?? null)===null||$tmp==='' ? 'info' ?? null : $tmp);?>
 alert-dismissible fade show" role="alert">
    <?php echo $_smarty_tpl->getValue('flash')['message'];?>

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
  </div>
<?php }?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_2332278336823593f070583_97240270', "content");
?>

<?php $_smarty_tpl->getInheritance()->endChild($_smarty_tpl, "./layouts/layout.tpl", $_smarty_current_dir);
}
/* {block "title"} */
class Block_16053126266823593f05df59_67044657 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>
Dashboard<?php
}
}
/* {/block "title"} */
/* {block "content"} */
class Block_2332278336823593f070583_97240270 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>

<div class="container mt-5">
  <h1>
    Hallo, <?php echo $_smarty_tpl->getValue('username');?>
!<br>
    <small class="text-muted">
        Rolle:
        <?php if ($_smarty_tpl->getValue('isAdmin')) {?>
            Administrator
        <?php } elseif ($_smarty_tpl->getValue('isMod')) {?>
            Moderator
        <?php } else { ?>
            Benutzer
        <?php }?>
    </small>
</h1>

  <?php if ($_smarty_tpl->getValue('isAdmin')) {?>
        <section class="my-5">
      <h2>
        <button class="btn btn-link p-0" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#collapseLoginLogs" 
                aria-expanded="true" 
                aria-controls="collapseLoginLogs">
          Letzte Login-Logs
        </button>
      </h2>
      <div class="collapse show" id="collapseLoginLogs">
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
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('login_logs'), 'log', false, 'i');
$foreach0DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('i')->value => $_smarty_tpl->getVariable('log')->value) {
$foreach0DoElse = false;
?>
                  <tr>
                    <td><?php echo $_smarty_tpl->getValue('i')+1;?>
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
            <div class="mt-3 text-end">
              <a href="login_logs.php" class="btn btn-sm btn-primary">
                Alle Login-Logs anzeigen
              </a>
            </div>
          </div>
        <?php } else { ?>
          <div class="alert alert-info">Keine Login-Logs vorhanden.</div>
        <?php }?>
      </div>
    </section>

        <section class="my-5">
      <h2>
        <button class="btn btn-link p-0" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#collapseCaptchaLogs" 
                aria-expanded="false" 
                aria-controls="collapseCaptchaLogs">
          reCAPTCHA-Protokolle
        </button>
      </h2>
      <div class="collapse" id="collapseCaptchaLogs">
        <?php if ($_smarty_tpl->getSmarty()->getModifierCallback('count')($_smarty_tpl->getValue('captcha_logs')) > 0) {?>
          <div class="table-responsive shadow-sm">
            <table class="table table-striped table-bordered table-sm align-middle">
              <thead class="table-dark text-center">
                <tr>
                  <th>#</th>
                  <th>ID</th>
                  <th>Erfolg</th>
                  <th>Score</th>
                  <th>Aktion</th>
                  <th>Hostname</th>
                  <th>Fehlergrund</th>
                  <th>Zeitpunkt</th>
                </tr>
              </thead>
              <tbody>
                <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('captcha_logs'), 'log', false, 'i');
$foreach1DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('i')->value => $_smarty_tpl->getVariable('log')->value) {
$foreach1DoElse = false;
?>
                  <tr>
                    <td><?php echo $_smarty_tpl->getValue('i')+1;?>
</td>
                    <td><?php echo $_smarty_tpl->getValue('log')['id'];?>
</td>
                    <td class="text-center">
                      <?php if ($_smarty_tpl->getValue('log')['success']) {?>
                        <span class="badge bg-success">Ja</span>
                      <?php } else { ?>
                        <span class="badge bg-danger">Nein</span>
                      <?php }?>
                    </td>
                    <td><?php echo (($tmp = $_smarty_tpl->getValue('log')['score'] ?? null)===null||$tmp==='' ? '–' ?? null : $tmp);?>
</td>
                    <td><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('log')['action'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                    <td><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('log')['hostname'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                    <td><?php echo htmlspecialchars((string)(($tmp = $_smarty_tpl->getValue('log')['error_reason'] ?? null)===null||$tmp==='' ? '–' ?? null : $tmp), ENT_QUOTES, 'UTF-8', true);?>
</td>
                    <td><?php echo $_smarty_tpl->getValue('log')['created_at'];?>
</td>
                  </tr>
                <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
              </tbody>
            </table>
            <div class="mt-3 text-end">
              <a href="captcha_logs.php" class="btn btn-sm btn-primary">
                Alle reCAPTCHA-Logs anzeigen
              </a>
            </div>
          </div>
        <?php } else { ?>
          <div class="alert alert-info">Keine reCAPTCHA-Logs vorhanden.</div>
        <?php }?>
      </div>
    </section>
  <?php }?>

  <?php if ($_smarty_tpl->getValue('isAdmin') || $_smarty_tpl->getValue('isMod')) {?>
        <section class="my-5">
      <h2>
        <button class="btn btn-link p-0" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#collapseContactRequests" 
                aria-expanded="false" 
                aria-controls="collapseContactRequests">
          Kontaktanfragen
        </button>
      </h2>
      <div class="collapse" id="collapseContactRequests">
        <?php if ($_smarty_tpl->getSmarty()->getModifierCallback('count')($_smarty_tpl->getValue('contact_requests')) > 0) {?>
          <div class="table-responsive shadow-sm">
            <table class="table table-bordered table-hover align-middle">
              <thead class="table-dark text-center">
                <tr>
                  <th>Kontakt-ID</th>
                  <th>Name</th>
                  <th>E-Mail</th>
                  <th>Betreff</th>
                  <th>Datum</th>
                </tr>
              </thead>
              <tbody>
                <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('contact_requests'), 'req');
$foreach2DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('req')->value) {
$foreach2DoElse = false;
?>
                  <tr>
                    <td><code><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('req')['contact_id'], ENT_QUOTES, 'UTF-8', true);?>
</code></td>
                    <td><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('req')['name'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                    <td><a href="mailto:<?php echo htmlspecialchars((string)$_smarty_tpl->getValue('req')['email'], ENT_QUOTES, 'UTF-8', true);?>
"><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('req')['email'], ENT_QUOTES, 'UTF-8', true);?>
</a></td>
                    <td><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('req')['subject'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                    <td><?php echo $_smarty_tpl->getValue('req')['created_at'];?>
</td>
                  </tr>
                <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
              </tbody>
            </table>
            <div class="mt-3 text-end">
              <a href="contact_request.php" class="btn btn-sm btn-primary">
                Alle Kontaktanfragen anzeigen
              </a>
            </div>
          </div>
        <?php } else { ?>
          <div class="alert alert-info">Keine Kontaktanfragen vorhanden.</div>
        <?php }?>
      </div>
    </section>

        <section class="my-5">
      <h2>
        <button class="btn btn-link p-0"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapseUploadLogs"
                aria-expanded="false"
                aria-controls="collapseUploadLogs">
          Upload-Logs
        </button>
      </h2>
      <div class="collapse" id="collapseUploadLogs">
        <?php if ($_smarty_tpl->getSmarty()->getModifierCallback('count')($_smarty_tpl->getValue('upload_logs')) > 0) {?>
          <div class="table-responsive shadow-sm">
            <table class="table table-striped table-bordered align-middle">
              <thead class="table-dark text-center">
                <tr>
                  <th>#</th>
                  <th>User ID</th>
                  <th>Dateiname</th>
                  <th>Zeitpunkt</th>
                </tr>
              </thead>
              <tbody>
                <?php
$_from = $_smarty_tpl->getSmarty()->getRuntime('Foreach')->init($_smarty_tpl, $_smarty_tpl->getValue('upload_logs'), 'log', false, 'i');
$foreach3DoElse = true;
foreach ($_from ?? [] as $_smarty_tpl->getVariable('i')->value => $_smarty_tpl->getVariable('log')->value) {
$foreach3DoElse = false;
?>
                  <tr>
                    <td><?php echo $_smarty_tpl->getValue('i')+1;?>
</td>
                    <td><?php echo $_smarty_tpl->getValue('log')['user_id'];?>
</td>
                    <td><?php echo htmlspecialchars((string)$_smarty_tpl->getValue('log')['stored_name'], ENT_QUOTES, 'UTF-8', true);?>
</td>
                    <td><?php echo $_smarty_tpl->getValue('log')['uploaded_at'];?>
</td>
                  </tr>
                <?php
}
$_smarty_tpl->getSmarty()->getRuntime('Foreach')->restore($_smarty_tpl, 1);?>
              </tbody>
            </table>
            <div class="mt-3 text-end">
              <a href="upload_logs.php" class="btn btn-sm btn-primary">
                Alle Upload-Logs anzeigen
              </a>
            </div>
          </div>
        <?php } else { ?>
          <div class="alert alert-info">Keine Upload-Logs vorhanden.</div>
        <?php }?>
      </div>
    </section>
  <?php }?>

</div>
<?php
}
}
/* {/block "content"} */
}
