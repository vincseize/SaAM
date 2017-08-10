
<a id="navigation"></a>
<h2 class="ui-state-default ui-corner-top pad3">Naviguer dans le SaAM<div class="floatR marge30r doigt btnTop"><span class="ui-icon ui-icon-arrowreturn-1-n"></span></div></h2>
	<ol>
		<li>
			<h4>Menu principal</h4>
			<div class="margeTop10" id="help_main_menu_top">
				<img src="<?php echo $dirHelp ?>/help_menu_top_left.jpg" class="shadowOut"/>
				<ul class="pad10">
					<li>Bouton [<span class="inline fondPage ui-corner-all top ui-icon ui-icon-arrowrefresh-1-s"></span>] : permet de recharger l'interface du SaAM. Cela peut être utile en cas de bug. (raccourci F5)</li>
					<li><b>STATS</b> : quelques statistiques globales de votre SaAM.</li>
					<li><b>PRÉFÉRENCES</b> : préférences de l'utilisateur connecté (vous).</li>
					<li><b>AIDE</b> : lien vers la page d'aide du SaAM.</li>
					<li><b>TUTORIELS</b> : ensemble de tutoriels vidéos généralistes, pour bien débuter dans SaAM.</li>
					<li><b>FAQ</b> : une foire aux questions.</li>
					<li>[<span class="inline fondPage ui-corner-all top ui-icon ui-icon-search"></span>] : recherche globale dans tout le SaAM (raccourci CTRL+F).</li>
				</ul>
			</div>
		</li>
		<li>
			<h4>CHAT / Avatar / Déconnexion</h4>
			<div class="margeTop10" id="help_chat_user_logout">
				<img src="<?php echo $dirHelp ?>/menu_top_right.jpg" class="shadowOut"/>
				<ul class="pad10">
					<li>Le bouton [<span class="inline fondPage ui-corner-all top ui-icon ui-icon-close"></span>] permet de vous <b>déconnecter</b> du SaAM.</li>
					<li>En survolant votre pseudo avec la souris, vous pouvez voir votre <b>statut utilisateur</b> (niveau d'ACL).</li>
					<li>En suvolant votre avatar avec la souris, vous verrez le <b>temps restant</b> avant la déconnexion automatique <i>(réglable dans les préférences)</i>.</li>
					<li>
						<b>Chat plugin</b> : S'il est activé, et quand d'autres utilisateurs (assignés aux même projets que vous) sont connectés,
						vous voyez leurs avatars apparaître à gauche. En cliquant sur un avatar vous pouvez commencer une discussion en messagerie instantanée avec l'utilisateur.
					</li>
				</ul>
			</div>
		</li>
		<li>
			<h4>Menu des raccourcis</h4>
			<div class="margeTop10" id="help_menu_shortcuts">
				<img src="<?php echo $dirHelp ?>/menu_contextuel_left.jpg" class="shadowOut"/>
				<p>Le <b>Menu des raccourcis</b> est un menu contextuel, lié à la section actuellement ouverte :</p>
				<ul class="padV10">
					<li><b>RACINE</b> : n'affiche rien.</li>
					<li><b>SEQ & PLANS</b> : Affiche la liste des séquences du projet. Cliquer sur une séquence
						affiche la <b>liste des plans</b> de cette séquence. Cliquer sur un plan ouvre le plan
						dans la vue principale, dans le dernier département choisi. Pour revenir à la liste
						des séquences, cliquer sur l'entête du menu (qui contient le nom du projet).
					</li>
					<li><b>SCÈNES</b> : Affiche la liste des séquences du projet (le chiffre entre parenthèse est
						le nombre de scènes associées à la séquence. Cliquer sur une séquence affiche la <b>liste
						des scènes "Master" associées</b> à la séquence. Cliquer sur une scène ouvre la scène
						dans la vue principale, dans le dernier département choisi. Pour revenir à la liste
						des séquences, cliquer sur l'entête du menu (qui contient le nom du projet).
					</li>
					<li><b>ASSETS</b> : Affiche la liste des dossiers principaux de l'<b>arborescence des assets.</b>
						Cliquer sur un dossier affiche la liste des assets contenus dans ce dossier (et ses
						sous-dossiers). Cliquer sur un asset permet de l'ouvrir dans la vue principale, dans
						le dernier département choisi. Cliquer sur l'entête pour revenir à la liste des
						dossiers principaux.
					</li>
					<li><b>TÂCHES</b> : n'affiche rien.</li>
				</ul>
			</div>
		</li>
		<li>
			<h4>Panneau des outils</h4>
			<div class="margeTop10" id="help_tools_panel">
				<img src="<?php echo $dirHelp ?>/menu_panel_right.jpg" class="floatL marge30r shadowOut"/>
				<ul class="padV10">
					<li><b>Mini calendrier</b></li>
					<li><b>Gestion nouvelles</b> : Pour ajouter, modifier ou supprimer les nouvelles qui sont affichées en page d'accueil</li>
					<li><b>Gestion utilisateurs</b> : Pour ajouter, modifier ou supprimer des utilisateurs pour votre SaAM. C'est ici que se fait l'assignation des utilisateurs aux projets.</li>
					<li><b>Gestion Projets</b> : Pour voir, modifier, archiver ou restaurer vos projets.</li>
					<li><b>NOTES</b> : Pour la gestion de vos notes personnelles</li>
					<li><b>TAGS</b> : Afficher et gérer les tags</li>
					<li><b>SCRIPTS</b> : Télécharger des scripts à lancer sur votre machine.</li>
					<li><b>PLUGINS</b> : Affiche la liste des plugins, activés ou pas</li>
					<li><b>BUG HUNTER</b> : Signaler un bug, et voir la liste des bugs déjà connus</li>
					<li><b>DEV</b> : Outils réservés aux développeurs.</li>
					<br>
				</ul>
				<p>NB : <i>Certains boutons ne sont pas visibles par tout le monde, cela dépend du statut utilisateur (niveau d'habilitation) !</i></p>
				<div class="fixFloat"></div>
			</div>
		</li>
		<li>
			<h4>Panneau des messages</h4>
			<div class="margeTop10" id="help_messages_panel">
				<img src="<?php echo $dirHelp ?>/message_bottom.jpg" class="shadowOut" />
				<p>Liste des messages non lus qui vous concernent directement, à propos des published de plans, scènes et assets.</p>
				<p>Vous pouvez cliquer sur un message pour ouvrir directement le plan/scène/asset correspondant dans la vue principale.</p>
				<p>En cliquant sur l'icône [<span class="inline fondPage ui-corner-all top ui-icon ui-icon-check"></span>],
					vous pouvez marquer le message comme lu afin de ne plus l'afficher dans la liste.</p>
			</div>
		</li>
		<li>
			<h4>Onglets des projets</h4>
			<div class="margeTop10" id="help_projects_navigation">
				<p>
					Les onglet en haut permettent de <b>naviquer parmi les projets</b>. Cliquez sur un onglet pour ouvrir un projet.
					L'onglet "Home" n'est pas un projet, mais l'accueil du SaAM.
				</p>
				<p>
					Si vous avez les droits nécessaires, vous pouvez créer un nouveau projet en cliquant sur l'icone
					<span class="inline fondPage ui-corner-all top ui-icon ui-icon-plusthick"></span>
				</p>
			</div>
		</li>
	</ol>