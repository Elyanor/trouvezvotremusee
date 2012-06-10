		<div id="cadre_recherche">
			<div id="recherche_aide">
				<div class="help_top"></div>
				<div class="help_recherche">
					<p>
						Rechercher en tapant le nom du mus�e, de la ville, du d�partement ou de la r�gion que vous souhaitez visiter.<br/>
						<em>Des suggestions vous sauront faites automatiquement.</em>
					</p>
				</div>
				<div class="help_bottom"></div>
			</div>
			
			<form method="post" action="recherche.html">
			    <p class="gauche">
					<input type="text" class="recherche_musee" name="musee" id="musee" onclick="help_recherche_focus()" value="" accesskey="5" tabindex="30" autocomplete="off" />
				</p>

				<p>
					<input class="envoyer" value="Rechercher ce mus�e" accesskey="6" type="submit" />
				</p>   
				<div id="resultats"></div>
			</form>
		</div>
		<ul id="museum">
			<li id="museum-find">Recherchez un mus�e simplement,</li>
			<li id="museum-interesting">Soyez avertis lorsque le mus�e se modifie,</li>
			<li id="museum-friends">Et partagez avec vos ami(e)s, votre famille, etc.</li>
		</ul>
		
		<br/><br/>