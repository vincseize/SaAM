
<style>
	.acl_N	 { font-size:1.2em; color:lightcoral; }
	.acl_A	 { font-size:1.2em; color:lightgreen; }
	.acl_O	 { font-size:1.2em; color:#0b93d5; }
</style>
<b>Gestion des utilisateurs</b>
<table class="tableListe center">
	<tr>
		<th class="bordBottom bordColInv2 leftText">Action</th>
		<th class="w100 bordBottom bordColInv2">Visiteur</th>
		<th class="w150 bordBottom bordColInv2">demo</th>
		<th class="w150 bordBottom bordColInv2">artiste</th>
		<th class="w150 bordBottom bordColInv2">lead</th>
		<th class="w200 bordBottom bordColInv2">superviseur</th>
		<th class="w200 bordBottom bordColInv2">dir. prod.</th>
		<th class="w200 bordBottom bordColInv2">magic</th>
		<th class="w100 bordBottom bordColInv2">dev</th>
		<th class="w100 bordBottom bordColInv2">root</th>
	</tr>
	<tr>
		<td class="leftText">Afficher utilisateurs</td>
		<td class="acl_N">NON</td>
		<td class="acl_O">Demo seulement</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_O">Les siens</td>
		<td class="acl_A">TOUS</td>
		<td class="acl_O">Les siens</td>
		<td class="acl_A">TOUS</td>
		<td class="acl_A">TOUS</td>
	</tr>
	<tr>
		<td class="leftText">Assigner projets à utilisateur</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_O">Les siens<br />(projets créés seulement)</td>
		<td class="acl_O">Les siens<br />(projets créés seulement)</td>
		<td class="acl_O">Les siens<br />(tous les projets)</td>
		<td class="acl_A">TOUS</td>
		<td class="acl_A">TOUS</td>
	</tr>
	<tr>
		<td class="leftText">Modifier / supprimer utilisateur</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_O">Les siens</td>
		<td class="acl_O">Les siens</td>
		<td class="acl_O">Les siens</td>
		<td class="acl_A">TOUS</td>
		<td class="acl_A">TOUS</td>
	</tr>
	<tr>
		<td class="leftText">Ajouter utilisateur</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_A">OUI</td>
		<td class="acl_A">OUI</td>
		<td class="acl_A">OUI</td>
		<td class="acl_A">OUI</td>
		<td class="acl_A">OUI</td>
	</tr>
</table>

<b>Gestion des projets</b>
<table class="tableListe center">
	<tr>
		<th class="bordBottom bordColInv2 leftText">Action</th>
		<th class="w100 bordBottom bordColInv2">Visiteur</th>
		<th class="w150 bordBottom bordColInv2">demo</th>
		<th class="w150 bordBottom bordColInv2">artiste</th>
		<th class="w150 bordBottom bordColInv2">lead</th>
		<th class="w200 bordBottom bordColInv2">superviseur</th>
		<th class="w200 bordBottom bordColInv2">dir. prod.</th>
		<th class="w200 bordBottom bordColInv2">magic</th>
		<th class="w100 bordBottom bordColInv2">dev</th>
		<th class="w100 bordBottom bordColInv2">root</th>
	</tr>
	<tr>
		<td class="leftText">Afficher les projets</td>
		<td class="acl_N">NON</td>
		<td class="acl_O">Assignés seulement</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_O">Assignés seulement</td>
		<td class="acl_O">Assignés seulement</td>
		<td class="acl_A">TOUS</td>
		<td class="acl_A">TOUS</td>
		<td class="acl_A">TOUS</td>
	</tr>
	<tr>
		<td class="leftText">Modifier / supprimer projet</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_O">Créés seulement</td>
		<td class="acl_O">Assignés seulement</td>
		<td class="acl_O">Assignés seulement</td>
		<td class="acl_A">TOUS</td>
		<td class="acl_A">TOUS</td>
	</tr>
	<tr>
		<td class="leftText">Ajouter un projet</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_N">NON</td>
		<td class="acl_A">OUI</td>
		<td class="acl_A">OUI</td>
		<td class="acl_A">OUI</td>
		<td class="acl_A">OUI</td>
		<td class="acl_A">OUI</td>
	</tr>
</table>