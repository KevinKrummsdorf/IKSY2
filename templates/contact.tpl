{extends file="./layouts/layout.tpl"}

{block name="title"}Kontakt{/block}

{block name="content"}
<div class="container my-5">
  <h1 class="text-center">Kontakt</h1>

  <div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
      <section class="kontakt-section">
        <h2 class="mt-4 mb-3 text-center">Kontaktformular</h2>

        <form action="kontakt-senden.php" method="POST" class="kontakt-form">
          <div class="mb-3 position-relative">
            <label for="name" class="form-label visually-hidden">Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Ihr Name" required>
          </div>

          <div class="mb-3 position-relative">
            <label for="email" class="form-label visually-hidden">E-Mail</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Ihre E-Mail" required>
          </div>

          <div class="mb-3">
            <label for="nachricht" class="form-label visually-hidden">Nachricht</label>
            <textarea class="form-control" id="nachricht" name="nachricht" rows="6" placeholder="Ihre Nachricht" required></textarea>
          </div>

          <button type="submit" class="btn btn-primary">Absenden</button>
        </form>

        <h2 class="mt-5 mb-3 text-center">Oder kontaktieren Sie uns direkt</h2>
        <p class="text-center">
          <a href="mailto:studyhub.iksy@gmail.com">studyhub.iksy@gmail.com</a>
        </p>

        <div class="mt-4 text-center">
          <h3>Servicezeiten:</h3>
          <p>Montag bis Freitag: 9:00 - 17:00 Uhr</p>
        </div>
      </section>
    </div>
  </div>
</div>
{/block}
