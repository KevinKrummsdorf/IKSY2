<?php
/* Smarty version 5.5.0, created on 2025-05-14 12:24:48
  from 'file:terms.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.0',
  'unifunc' => 'content_68246f708d4724_15858187',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '3883e0d91b07db27f4cd5c4010be7958f53950cc' => 
    array (
      0 => 'terms.tpl',
      1 => 1746878885,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_68246f708d4724_15858187 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
$_smarty_tpl->getInheritance()->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_63091702068246f707382a6_48017079', "title");
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_60271079668246f708d3673_37007219', "content");
$_smarty_tpl->getInheritance()->endChild($_smarty_tpl, "./layouts/layout.tpl", $_smarty_current_dir);
}
/* {block "title"} */
class Block_63091702068246f707382a6_48017079 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>
AGB<?php
}
}
/* {/block "title"} */
/* {block "content"} */
class Block_60271079668246f708d3673_37007219 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>

<div class="container my-5">
  <h1 class="mb-4">Allgemeine Geschäftsbedingungen (AGB)</h1>

  <!-- Einleitung -->
  <section class="mb-5">
    <p>Willkommen bei <strong>StudyHub</strong>. Durch die Nutzung unserer Plattform erklären Sie sich mit diesen Allgemeinen Geschäftsbedingungen (AGB) einverstanden. Bitte lesen Sie diese sorgfältig durch.</p>
  </section>

  <!-- Nutzungsbedingungen -->
  <section class="mb-5">
    <h2>1. Geltungsbereich</h2>
    <p>Diese AGB gelten für die Nutzung der Online-Lernplattform <strong>StudyHub</strong>, erreichbar unter www.studyhub.de.</p>
  </section>

  <section class="mb-5">
    <h2>2. Registrierung und Nutzung</h2>
    <p>Zur vollständigen Nutzung von StudyHub ist eine kostenlose Registrierung erforderlich. Sie verpflichten sich, wahrheitsgemäße Angaben zu Ihrer Person zu machen und Ihre Zugangsdaten sicher aufzubewahren.</p>
  </section>

  <section class="mb-5">
    <h2>3. Inhalte der Nutzer</h2>
    <p>Sie können eigene Inhalte hochladen (z.B. Zusammenfassungen, Mitschriften). Sie gewähren StudyHub ein einfaches Nutzungsrecht zur Darstellung und Verbreitung dieser Inhalte auf unserer Plattform.</p>
  </section>

  <section class="mb-5">
    <h2>4. Verbotene Inhalte</h2>
    <p>Es ist untersagt, rechtswidrige, diskriminierende, beleidigende oder urheberrechtlich geschützte Inhalte ohne Berechtigung hochzuladen.</p>
  </section>

  <section class="mb-5">
    <h2>5. Haftung</h2>
    <p>StudyHub übernimmt keine Haftung für die Richtigkeit und Vollständigkeit der von Nutzern bereitgestellten Inhalte. Eine ständige Überprüfung der eingestellten Inhalte erfolgt nicht.</p>
  </section>

  <section class="mb-5">
    <h2>6. Beendigung der Nutzung</h2>
    <p>Sie können Ihre Mitgliedschaft jederzeit beenden. StudyHub behält sich vor, bei Verstößen gegen diese AGB Nutzerkonten zu sperren oder zu löschen.</p>
  </section>

  <section class="mb-5">
    <h2>7. Datenschutz</h2>
    <p>Details zur Verarbeitung Ihrer personenbezogenen Daten entnehmen Sie bitte unserer <a href="privacy.php">Datenschutzerklärung</a>.</p>
  </section>

  <section class="mb-5">
    <h2>8. Änderungen der AGB</h2>
    <p>StudyHub behält sich das Recht vor, diese AGB jederzeit zu ändern. Änderungen werden den Nutzern rechtzeitig bekannt gegeben.</p>
  </section>

  <section class="mb-5">
    <h2>9. Anwendbares Recht</h2>
    <p>Es gilt das Recht der Bundesrepublik Deutschland unter Ausschluss des UN-Kaufrechts.</p>
  </section>
</div>
<?php
}
}
/* {/block "content"} */
}
