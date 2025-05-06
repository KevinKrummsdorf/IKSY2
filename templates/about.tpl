{extends file="./layouts/layout.tpl"}

{block name="title"}Über uns – {$app_name}{/block}

{block name="head"}
    <link href="{$base_url}/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{$base_url}/style.css" rel="stylesheet">
{/block}

{block name="content"}

    <main class="container text-center my-5">
        <h1>Über uns</h1>
        <h2>
            Wir sind ein kleines, engagiertes Team mit einer gemeinsamen Mission:
            digitale Lösungen zu schaffen, die das Leben einfacher machen.
            Was uns auszeichnet? Leidenschaft, Teamgeist und jede Menge Herzblut.
        </h2>
    </main>

{foreach from=$team item=member}
  <section class="container text-center my-5">
    <div class="row justify-content-center align-items-center">
      <div class="col-md-4 text-center">
        <h4>{$member.name}</h4>
        <div class="team-photo-wrapper mx-auto rounded shadow">
          <img src="{$base_url}/assets/{$member.img}"
               alt="{$member.name}">
        </div>
      </div>
      <div class="col-md-8">
        <p>{$member.bio}</p>
      </div>
    </div>
  </section>
{/foreach}

    <main class="container text-center my-5">
        <h3>
            Dieses Projekt wurde mit viel Engagement von unseren Dozenten
            Herrn <a href="https://www.hochschule-bochum.de/fbw/service/labor-fuer-wirtschaftsinformatik/" target="_blank">Frank Brockmann</a>
            und Herrn <a href="https://www.hochschule-bochum.de/fbw/service/labor-fuer-wirtschaftsinformatik/" target="_blank">Christoph Schennonek</a>
            unterstützt.
        </h3>
    </main>
{/block}
