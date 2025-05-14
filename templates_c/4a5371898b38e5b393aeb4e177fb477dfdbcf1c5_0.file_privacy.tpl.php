<?php
/* Smarty version 5.5.0, created on 2025-05-13 16:38:21
  from 'file:privacy.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.0',
  'unifunc' => 'content_6823595dc2f7f1_91037910',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '4a5371898b38e5b393aeb4e177fb477dfdbcf1c5' => 
    array (
      0 => 'privacy.tpl',
      1 => 1747130846,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_6823595dc2f7f1_91037910 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
$_smarty_tpl->getInheritance()->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_465294656823595dc28e29_00958310', "title");
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_11731994976823595dc2eab9_28290577', "content");
?>

<?php $_smarty_tpl->getInheritance()->endChild($_smarty_tpl, "./layouts/layout.tpl", $_smarty_current_dir);
}
/* {block "title"} */
class Block_465294656823595dc28e29_00958310 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>
Datenschutzerklärung<?php
}
}
/* {/block "title"} */
/* {block "content"} */
class Block_11731994976823595dc2eab9_28290577 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>

<div class="container my-5">
  <h1 id="main-heading">Datenschutzerklärung</h1>
  <p>Stand: April 2025</p>

  <section class="mt-4">
    <h2>1. Verantwortliche Stelle</h2>
    <p>
      StudyHub GmbH<br>
      Musterstraße 1<br>
      12345 Musterstadt<br>
      Deutschland
    </p>
    <p>Vertreten durch: Maximilian Mustermann</p>
    <p>
      Telefon: 01234 123-0<br>
      E-Mail: <a href="mailto:studyhub.iksy@gmail.com">studyhub.iksy@gmail.com</a>
    </p>
  </section>

  <section class="mt-4">
    <h2>2. Erhebung und Verarbeitung personenbezogener Daten</h2>
    <p>
      Wir erheben und verarbeiten personenbezogene Daten nur, soweit dies erforderlich ist,
      um unsere Webseite bereitzustellen und Ihre Anfragen zu beantworten.
    </p>

    <h3 class="mt-4">2.1 Server-Logfiles</h3>
    <p>Folgende Daten werden bei jedem Seitenaufruf automatisch erfasst:</p>
    <ul>
      <li>Browsertyp und -version</li>
      <li>Betriebssystem</li>
      <li>Referrer-URL</li>
      <li>Hostname des zugreifenden Rechners</li>
      <li>Uhrzeit der Serveranfrage</li>
      <li>anonymisierte IP-Adresse</li>
    </ul>
    <p>Diese Daten dienen statistischen Auswertungen und der Sicherheit.</p>

    <h3 class="mt-4">2.2 Kontaktformular</h3>
    <p>Wenn Sie unser Kontaktformular nutzen, erheben wir Ihre Angaben zur Bearbeitung der Anfrage.</p>

    <h3 class="mt-4">2.3 Cookies</h3>
    <p>Wir verwenden Cookies zur Optimierung der Nutzererfahrung. Sie können diese im Browser deaktivieren.</p>

    <h3 class="mt-4">2.4 Google reCAPTCHA</h3>
    <p>
      Zum Schutz vor Missbrauch unserer Formulare verwenden wir den Dienst reCAPTCHA der Google Ireland Limited,
      Gordon House, Barrow Street, Dublin 4, Irland. Damit wird überprüft, ob eine Eingabe durch einen Menschen oder
      missbräuchlich durch automatisierte, maschinelle Verarbeitung erfolgt.
    </p>
    <p>
      Dabei werden unter anderem IP-Adresse, Mausbewegungen sowie Verweildauer und ggf. weitere zur Funktion
      erforderliche Daten an Google übermittelt. Die Verarbeitung erfolgt auf Grundlage von Art. 6 Abs. 1 lit. f DSGVO
      und unserem berechtigten Interesse an der Verhinderung von Missbrauch und Spam.
    </p>
    <p>
      Weitere Informationen finden Sie in den <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Datenschutzhinweisen von Google</a>.
    </p>
  </section>

  <section class="mt-4">
    <h2>3. Ihre Rechte</h2>
    <p>Sie haben gemäß DSGVO insbesondere folgende Rechte:</p>
    <ul>
      <li>Auskunft (Art. 15 DSGVO)</li>
      <li>Berichtigung (Art. 16 DSGVO)</li>
      <li>Löschung (Art. 17 DSGVO)</li>
      <li>Einschränkung der Verarbeitung (Art. 18 DSGVO)</li>
      <li>Datenübertragbarkeit (Art. 20 DSGVO)</li>
      <li>Widerspruch (Art. 21 DSGVO)</li>
      <li>Beschwerde bei einer Aufsichtsbehörde (Art. 77 DSGVO)</li>
    </ul>
  </section>

  <section class="mt-4">
    <h2>4. Datensicherheit</h2>
    <p>
      Wir treffen angemessene technische und organisatorische Maßnahmen
      zum Schutz Ihrer Daten vor Verlust, Missbrauch oder unbefugtem Zugriff.
    </p>
  </section>

  <section class="mt-4">
    <h2>5. Änderungen dieser Datenschutzerklärung</h2>
    <p>
      Diese Datenschutzerklärung kann jederzeit angepasst werden,
      um gesetzlichen Anforderungen oder internen Änderungen gerecht zu werden.
    </p>
  </section>
</div>
<?php
}
}
/* {/block "content"} */
}
