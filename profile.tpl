{extends file="./layouts/layout.tpl"}

{block name="title"}Profil{/block}

{block name="content"}
  
  
 {require_once __DIR__ . '/../includes/db.inc.php'}
   
   
    <h1 class="text-center">Mein Profil</h1>
   
   <div class="container my-5">
      <div class="profile-box">
     	 <strong>Name:</strong>
      	<p class="text-muted">Max</p>
      	<strong>Benutzername:</strong>
      	<p class="text-muted">{$username}</p> 
      	<strong>E-Mail:</strong>
     	<p class="text-muted">max@example.com</p>
      	<strong>Andere Netzwerke:</strong>
     	<p class="text-muted">Instagram, TikTok, Discord, MS Teams</p>

	 <section class="text-center">
      <a href="bearbeiten.php" class="btn btn-primary btn-lg mt-30">Profil bearbeiten</a>
    </section>
    
  		</div>
	</div>

	
{/block}
