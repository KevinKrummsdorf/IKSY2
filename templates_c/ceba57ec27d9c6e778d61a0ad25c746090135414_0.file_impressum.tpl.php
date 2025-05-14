<?php
/* Smarty version 5.5.0, created on 2025-05-13 16:38:13
  from 'file:impressum.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.0',
  'unifunc' => 'content_68235955377ec3_59822380',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'ceba57ec27d9c6e778d61a0ad25c746090135414' => 
    array (
      0 => 'impressum.tpl',
      1 => 1746730079,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_68235955377ec3_59822380 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
$_smarty_tpl->getInheritance()->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_13824043668235955372c90_70778816', "title");
?>


<?php 
$_smarty_tpl->getInheritance()->instanceBlock($_smarty_tpl, 'Block_155264672768235955377526_25286291', "content");
?>

<?php $_smarty_tpl->getInheritance()->endChild($_smarty_tpl, "./layouts/layout.tpl", $_smarty_current_dir);
}
/* {block "title"} */
class Block_13824043668235955372c90_70778816 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>
Impressum<?php
}
}
/* {/block "title"} */
/* {block "content"} */
class Block_155264672768235955377526_25286291 extends \Smarty\Runtime\Block
{
public function callBlock(\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = 'C:\\xampp\\htdocs\\www\\IKSY2\\templates';
?>

<div class="container my-5">
  <h1 id="main-heading">Impressum</h1>

  <div class="impressum-content text-start">
    <section>
      <h2>Angaben gemäß § 5 Telemediengesetz (TMG)</h2>
      <p>
        StudyHub GmbH<br>
        Musterstraße 1<br>
        12345 Musterstadt<br>
        Deutschland
      </p>
    </section>

    <section>
      <h2>Vertreten durch</h2>
      <p>Maximilian Mustermann</p>
    </section>

    <section>
      <h2>Kontakt</h2>
      <p>
        Telefon: 01234 123-0<br>
        E-Mail: <a href="mailto:studyhub.iksy@gmail.com">studyhub.iksy@gmail.com</a>
      </p>
    </section>

    <section>
      <h2>Informationen zur Online-Streitbeilegung</h2>
      <p>
        Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit:
        <a href="https://ec.europa.eu/consumers/odr/main/index.cfm?event=main.home2.show&lng=DE" target="_blank" rel="noopener noreferrer">
          OS-Plattform der EU
        </a>
      </p>
      <p>
        Wir sind nicht verpflichtet und nicht bereit, an einem Streitbeilegungsverfahren teilzunehmen.
      </p>
    </section>

    <section>
      <h2>Haftung für Inhalte</h2>
    <p>
      Die Inhalte unserer Seiten wurden mit größter Sorgfalt erstellt.
      Als Diensteanbieter sind wir gemäß § 7 Abs.1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich.
      Nach §§ 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen.
    </p>
    </section>

    <section>
      <h2>Haftung für Links</h2>
    <p>
      Unser Angebot enthält Links zu externen Webseiten Dritter, auf deren Inhalte wir keinen Einfluss haben.
      Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich.
    </p>
    </section>

    <section>
      <h2>Urheberrecht</h2>
    <p>
      Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht.
      Downloads und Kopien dieser Seite sind nur für den privaten, nicht kommerziellen Gebrauch gestattet.
    </p>
    </section>
  </div>
</div>
<?php
}
}
/* {/block "content"} */
}
