{extends file="./layouts/layout.tpl"}

{block name="title"}Über uns – {$app_name}{/block}

{block name="content"}

  <h1 id="main-heading" class="text-center mb-5">Über uns</h1>

  <div class="container text-center mb-5">
    <h2 class="lead">
      Wir sind ein kleines, engagiertes Team mit einer gemeinsamen Mission:
      digitale Lösungen zu schaffen, die das Leben einfacher machen.<br>
      Was uns auszeichnet? Leidenschaft, Teamgeist und jede Menge Herzblut.
    </h2>
  </div>

  {foreach from=$team item=member}
    <section class="container my-5">
      <div class="row justify-content-center align-items-center">
        <div class="col-md-8">
          <p>{$member.bio|escape:'html'}</p>
        </div>
      </div>
    </section>
  {/foreach}

  <div class="container text-center my-5">
    <h3 class="fw-normal">
      Dieses Projekt wurde mit viel Engagement von unseren Dozenten unterstützt:<br>
      <a href="#" target="_blank" rel="noopener">Dozent A</a> und
      <a href="#" target="_blank" rel="noopener">Dozent B</a>.
    </h3>
  </div>
{/block}
