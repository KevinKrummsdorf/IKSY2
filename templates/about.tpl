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
        <div class="col-md-4 text-center mb-3 mb-md-0">
          <div class="team-photo-wrapper mx-auto rounded shadow overflow-hidden" style="max-width: 500px;">
            <img src="{$base_url}/assets/{$member.img|escape}" class="img-fluid" alt="{$member.name|escape}">
          </div>
          <h4 class="mt-3">{$member.name|escape}</h4>
        </div>
        <div class="col-md-8">
          <p>{$member.bio|escape:'html'}</p>
        </div>
      </div>
    </section>
  {/foreach}

  <div class="container text-center my-5">
    <h3 class="fw-normal">
      Dieses Projekt wurde mit viel Engagement von unseren Dozenten unterstützt:<br>
      <a href="https://www.hochschule-bochum.de/fbw/service/labor-fuer-wirtschaftsinformatik/" target="_blank" rel="noopener">Frank Brockmann</a> und
      <a href="https://www.hochschule-bochum.de/fbw/service/labor-fuer-wirtschaftsinformatik/" target="_blank" rel="noopener">Christoph Schennonek</a>.
    </h3>
  </div>
{/block}
