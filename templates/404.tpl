{extends file="./layouts/layout.tpl"}

{block name="title"}404 - Seite nicht gefunden{/block}

{block name="content"}
<section class="page_404 my-5">
  <div class="container text-center">
    <div class="four_zero_four_bg">
      <img src="{$base_url}/assets/robo.png" alt="404 Error" class="img-fluid" />
    </div>
    <div class="contant_box_404 mt-4">
      <h3 class="h2">Sieht aus, als wärst du verloren</h3>
      <p>Die Seite, die du suchst, ist nicht verfügbar!</p>
      <a href="{$base_url}/router.php?page=index" class="btn btn-primary btn-lg mt-3">Zur Startseite</a>
    </div>
  </div>
</section>
{/block}
