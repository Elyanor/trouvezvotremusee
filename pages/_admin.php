	<?php
		if(isset($_SESSION['connexion']) AND $_SESSION['niveau'] == 5 AND $_SESSION['connexion']){ 
	?>
		<div id="contenu">
			<h1>Administration</h1>
			
			<br/>
			
			<form method="POST" action="affichage.html" >
				<fieldset>
					<legend>Acc�s � l'affichage des donn�es </legend>
						<input name="affich" type="radio" value="region" />R�gion <br />
						<input name="affich" type="radio" value="departement" />D�partement <br />
						<input name="affich" type="radio" value="ville" />Ville <br />
						<input name="affich" type="radio" value="musee" />Mus�e <br />
						<br />
						<input type="submit" name="affiche" value="afficher" />
				</fieldset>
			</form>
				
			<br/>
			
			<form method="POST" action="ajout.html">
				<fieldset>
					<legend>Acc�s ajout de donn�es</legend>
						<input name="ajout" type="radio" value="region" />R�gion <br />
						<input name="ajout" type="radio" value="departement" />D�partement <br />
						<input name="ajout" type="radio" value="ville" />Ville <br />
						<input name="ajout" type="radio" value="musee" />Mus�e <br />
						<br />
						<input type="submit" name="choix_modif" value="ajouter" />
				</fieldset>
			</form>
			
			<br/>
			
			<form method="POST" action="modification.html">
				<fieldset>
					<legend>Acc�s au modificateur de donn�es</legend>
						<input name="modif" type="radio" value="region" />R�gion <br />
						<input name="modif" type="radio" value="departement" />D�partement <br />
						<input name="modif" type="radio" value="ville" />Ville <br />
						<input name="modif" type="radio" value="musee" />Mus�e <br />
						<br />
						<input type="submit" name="choix_modif" value="modifier" />
				</fieldset>
			</form>
			
			<br/>
			
			<form>
				<fieldset>
					<legend>Acc�s liste des utilisateurs</legend>
						<a href="./utilisateurs.html">Liste des utilisateurs</a>
				</fieldset>
			</form>
			
		</div>
	<?php 
		}
		elseif(isset($_SESSION['connexion']) AND $_SESSION['niveau'] == 1 AND $_SESSION['connexion']){
	?>
			<p>Vous n'etes pas autoris� � all� sur cette zone de Trouvez Votre Mus�e !</P>
			<script type="text/javascript">
				document.location.href = "connexion.html";
			</script>
	<?php
		}else{
	?>
		<script type="text/javascript">
			document.location.href = "connexion.html";
		</script>
	<?php
		}
	?>
