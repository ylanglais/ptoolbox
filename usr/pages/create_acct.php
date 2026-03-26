<section>
<header>Connectez-vous</header>
<section>
	<article class="panel" id="login">
		<label for='email'>Email</label>
		<input type="text" id='email'/>
		<label for='passwd'>Mot de passe</label>
		<input type="password" id='passwd'/>
		<button id="connect" onclick="acct_login()">Se connecter</button>
	</article>
	<article class="panel" id="createacct">
		<input type="button" id="professionnel" onclick="acct_professionnel()"/>
		<input type="button" id="particulier"   onclick="acct_particulier()"/>
	</article>
</section>
