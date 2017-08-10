
<a id="scenes"></a>
<h2 class="ui-state-default ui-corner-top pad3">SCÈNES<div class="floatR marge30r doigt btnTop"><span class="ui-icon ui-icon-arrowreturn-1-n"></span></div></h2>
	<ol>
		<li>
			<h4>Définition d'une scène</h4>
			<img src="<?php echo $dirHelp ?>/help_scene_diagram.jpg" class="floatR marge10 shadowOut" style="max-width: 55%;"/>
			<div class="margeTop10">
				<p>
					Une <b>scène</b> est caractérisée par un lieu, une action. Il ne faut pas les confondre avec une séquence (qui n'est qu'une succession
					de plans), ni avec un plan (qui est le résultat d'une caméra qui filme la scène).
				</p>
				Dans une scène, il y a :
				<ul>
					<li>des <b>assets</b> qui définissent les personnages, le lieu, les accessoires, le décor, etc.</li>
					<li>l'<b>animation</b> qui défini l'action, filmée par les caméras.</li>
					<li>une (ou plusieurs) <b>caméras</b>. Chaque caméra est ensuite associée à un plan dans une séquence.</li>
				</ul>
				<p>
					Il existe deux types de scènes : les scènes <b>MASTER</b>, et les scènes <b>FILLES</b> (aussi appellées <b>"DÉRIVÉES"</b>).<br />
					Les scènes "master" sont l'entité de base des scènes, c'est à dire les assets, le lieu et l'action. Elles ne comportent en général
					aucune caméra. Les scènes filles sont des <b>copies instancées</b> de la scène master, c'est à dire qu'elles comportent les mêmes
					assets, (même lieu, mêmes personnages, etc) et la même action que leur scène master, mais dans une scène fille se trouvent les
					caméras filmant la scène. Une scène fille peut aussi <b>exclure quelques assets</b> qui ne sont pas nécessaires (car ils sont hors cadre,
					par exemple).
				</p>
				<p>
					En règle générale, nous construisons <b>d'abord une scène master</b>, pour définir une action entière dans un lieu (correspondant au synopsis du
					film). Puis nous <b>dérivons cette scène</b> en plusieurs scènes filles ("dérivées"), selon le découpage prévu dans le <b>storyboard</b>,
					afin d'y ajouter les caméras, qui elles mêmes seront associées à des plans dans des séquences du film.
				</p>
				<p>
					Ce procédé permet de pouvoir <b>ajouter des assets</b> plus tard dans les scènes master, sans avoir à les ajouter aussi dans les scènes filles,
					car elles dépendent de leur master, et donc ont les même assets par défaut. Cela est très pratique, surtout si il existe beaucoup de dérivées
					de la scène master. De plus, l'avantage est que les caméras au sein des scènes filles peuvent être <b>animées indépendamment</b>.
				</p>
				<p>
					La gestion des scènes se fait dans la section "Scènes" (menu déroulant en haut à gauche de la fenêtre du projet).
				</p>
			</div>
		</li>
		<li>
			<h4>Départements des scènes</h4>
			<img src="<?php echo $dirHelp ?>/help_scene_departments.jpg" class="marge5 shadowOut" />
			<p>
				Ces départements ne sont présents qu'une fois qu'ils ont été spécifiquement choisis dans la configuration du projet.
				Il est possible de les renommer et d'en ajouter dans le panneau des outils, bouton "SaAM administration" (pour ceux qui y ont accès).<br />
				Dans tout les départements, la fenêtre des scènes se compose de 4 parties :
			</p>
			<ul>
				<li>
					<h4>Listing des scènes</h4>
					À gauche, le <b>listing des scènes</b>, qui peut être affiché de plusieurs manières :
					<div id="help_scenes_list_tabs">
						<h5>Choix du type de listing des scènes</h5>
						<img src="<?php echo $dirHelp ?>/help_scene_list_tabs.jpg" class="marge5 shadowOut" /><br /><br />
						Ces onglets vous permettent de choisir la façon dont vous voulez trier les scènes :
						<ul>
							<li>Lister toutes les scènes du projet, même les scènes n'ayant pas encore été associées à des plans.</li>
							<li>Trier les scènes par séquence. Ici s'affichent seulement les scènes qui ont été associées à des plans.</li>
							<li>Trier les scènes par tag. <i>(pas encore développé)</i></li>
						</ul>
						Vous trouverez aussi un <b>module de recherche</b>. Cliquez sur le bouton
						<small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-search"></i></button></small>
						pour afficher un champ de recherche. Pendant que vous tapez un terme, la liste des scènes est <b>filtrée dessous en temps réel</b>. Appuyez
						sur la touche "Echap" de votre clavier pour enlever le filtrage.
					</div>
					<div id="help_scenes_list_all">
						<h5>Liste de toutes les scènes du projet</h5>
						<img src="<?php echo $dirHelp ?>/help_scene_list_all.jpg" class="floatL marge10 shadowOut" /><br />
						<p>
							La liste des scènes est classée par paquets de 10.<br />
							Les scènes en <b class="colorBtnFake">bleu</b> sont les scènes master.<br />
							les scènes en <b class="colorDark">gris</b> sont les scènes filles ("dérivées").<br />
							La scène en <b class="colorErrText">jaune</b> est la scène qui est sélectionnée, affichée dans la partie droite de la fenêtre.<br />
						</p>
						<p>
							En cliquant sur une scène master, vous faites apparaître ses scènes filles dessous (indentées à droite), mais vous affichez aussi
							ses informations dans la partie droite de la fenêtre.<br />
							La nomenclature du titre des scènes n'est pas gérée par SaAM, vous pouvez les nommer comme vous voulez. Cependant, par défaut SaAM
							attribue le préfixe "M_SC" aux scènes master, et "#_SC" aux scènes filles. Vous pouvez bien entendu changer ce comportement,
							dans la configuration du SaAM si vous en avez l'accès.
						</p>
						<p>
							Le bouton <small class="petit"><button class="bouton ui-state-default">Create Master scene<i class="inline mid ui-icon ui-icon-plusthick"></i></button></small>
							sert à ajouter une scène master dans la liste (voir >Ajouter une scène Master<).
						</p>
					</div>
					<div class="fixFloat"></div>
					<div id="help_scenes_list_sequences">
						<h5>Liste des scènes par séquence</h5>
						<img src="<?php echo $dirHelp ?>/help_scene_list_seq.jpg" class="floatL marge10 shadowOut" /><br />
						<p>
							La liste des scènes est classée par séquence. Les séquences correspondent à celles définies dans la section "SÉQ. & PLANS".<br />
							Les scènes en <b class="colorBtnFake">bleu</b> sont les scènes master.<br />
							les scènes en <b class="colorDark">gris</b> sont les scènes filles ("dérivées").<br />
							La scène en <b class="colorErrText">jaune</b> est la scène qui est sélectionnée, affichée dans la partie droite de la fenêtre.<br />
						</p>
						<p>
							En cliquant sur une séquence, vous pouvez montrer la liste des scènes qu'elle contient. Cela aura aussi pour effet de cacher les
							autres séquences.<br/>
							En cliquant sur une scène master, vous faites apparaître ses scènes filles dessous (indentées à droite), mais vous affichez aussi
							ses informations dans la partie droite de la fenêtre.<br />
							La nomenclature du titre des scènes n'est pas gérée par SaAM, vous pouvez les nommer comme vous voulez. Cependant, par défaut SaAM
							attribue le préfixe "M_SC" aux scènes master, et "#_SC" aux scènes filles. Vous pouvez bien entendu changer ce comportement,
							dans la configuration du SaAM si vous en avez l'accès.
						</p>
						<p>
							Le bouton <small class="petit"><button class="bouton ui-state-default">Create Master scene<i class="inline mid ui-icon ui-icon-plusthick"></i></button></small>
							sert à ajouter une scène master dans la liste (voir >Ajouter une scène Master<).
						</p>
					</div>
					<div class="fixFloat"></div>
				</li>
				<li id="help_scenes_center_view">
					<h4>Information de la scène MASTER</h4>
					Au centre, les informations de la scène MASTER sélectionnée. Si vous sélectionnez une scène FILLE, les informations de sa
					scène master seront affichées ici.<br />
					Tout en haut dans la barre noire, le titre de la scène master écrit en jaune.<br /><br />
					Dessous, 4 onglets : <br /><br />
					- <b>"Dérivées"</b> :
					<div id="help_scenes_master_infos_derivates">
						<img src="<?php echo $dirHelp ?>/help_scene_master_infos_deriv.jpg" class="floatL marge10 shadowOut" /><br />
						Affiche la <b>liste des dérivées</b> (filles) de la scène master sélectionnée. Vous pouvez cliquer sur une
						dérivée pour la sélectionner.
						<p>
							Vous pouvez créer une dérivée grâce au bouton <b>"Create Derivative"</b>, qui ouvrira un formulaire
							sur la partie droite de la fenêtre des scènes.<br />(voir ><a class="helpBtn noBorder" content="scenes">Créer une dérivée</a><)
						</p>
					</div>
					<div class="fixFloat"></div>
					- <b>"Infos"</b> :
					<div id="help_scenes_master_scene_infos">
						<img src="<?php echo $dirHelp ?>/help_scene_master_infos_infos.jpg" class="floatL marge10 shadowOut" /><br />
						Affiche les <b>informations</b> de la scène master sélectionnée (ou de la scène master de la fille sélectionnée).
					</div>
					<div class="fixFloat"></div>
					- <b>"Assets"</b> :
					<div id="help_scenes_master_infos_assets">
						<img src="<?php echo $dirHelp ?>/help_scene_master_infos_assets.jpg" class="floatL marge10 shadowOut" /><br />
						<p>
							Affiche la <b>liste des assets</b> que contient la scène.<br />
							<br />
							Les assets sont triés par catégorie. Cliquez sur une catégorie pour dérouler la liste des assets.
						</p>
						<p>
							Pour gérer la liste des assets, (ajouter ou enlever des assets à la scène master), vous pouvez cliquer sur le bouton
							<b>"Manage assets (MASTER)"</b><br />
							(voir ><a class="helpBtn noBorder" content="scenes">Associer des assets à une scène</a><)
						</p>
					</div>
					<div class="fixFloat"></div>
					- <b>"Plans"</b> :
					<div id="help_scenes_master_infos_shots">
						<img src="<?php echo $dirHelp ?>/help_scene_master_infos_shots.jpg" class="floatL marge10 shadowOut" /><br />
						Affiche la <b>liste des plans</b> associés à la scène. Vous pouvez cliquer sur la vignette d'un plan pour ouvrir
						la fenêtre du plan.<br />
						<br />
						Pour gérer les assigations des plans, cliquez sur le bouton <b>"Assigner plans (MASTER)"</b><br />
						(voir ><a class="helpBtn noBorder" content="scenes">Associer une scène à des plans</a><)
					</div>
					<div class="fixFloat"></div>
				</li>
				<li id="help_scenes_top_right_view">
					<h4>Informations de la scène sélectionnée</h4>
					<p>
						En haut à droite de la fenêtre des scènes, se trouvent :<br />
						- les <b>étapes</b> pour le département actuel,<br />
						- les <b>informations</b> de la scène sélectionnée,<br />
						- une <b>image</b> qui identifie la scène
					</p>
					<p id="help_scenes_info_bar">
						Au dessus, dans la barre noire, est affiché le <b>titre de la scène</b> sélectionnée, son <b>numéro de version</b>, ainsi que
						le nom de <b>l'utilisateur</b> qui a "pris la main" sur le fichier.<br />
						<br />
						Si la scène sélectionnée est une scène master, un <b>cartouche jaune</b> "MASTER" apparaît à gauche pour vous le rappeler.<br />
						Sinon, un <b>cartouche blanc</b> "DÉRIVÉE" est affiché dans le cas d'une scène fille.
					</p>
					<div id="help_scenes_department_steps">
						<h5>Étapes du département</h5>
						<p>
							Si <b>aucune étape</b> n'est encore définie pour la scène et pour le département, un <b>liseret jaune</b> entoure les étapes. Le
							bouton du département dans le menu en haut reste gris (avec un liseret bleu pour savoir qu'il est sélectionné).<br />
							<img src="<?php echo $dirHelp ?>/help_scene_steps_no.jpg" class="marge10 shadowOut" /><br />
							Une fois qu'une étape a été sélectionnée, le liseret jaune disparaît, et l'étape choisie devient bleue. Le bouton
							du département dans le menu en haut devient bleu lui aussi.<br />
							<img src="<?php echo $dirHelp ?>/help_scene_steps.jpg" class="marge10 shadowOut" />
						</p>
					</div>
					<div id="help_scenes_informations_top_right">
						<h5>Informations de la scène sélectionnée</h5>
						<img src="<?php echo $dirHelp ?>/help_scene_infos_right.jpg" class="floatL marge5 shadowOut" />
						<p>
							Les informations affichées sont les suivantes :<br />
							- Les pseudos du <b>superviseur</b> et du <b>lead</b> de la scène,<br />
							- La liste des utilisateurs de l'<b>équipe d'artistes</b> travaillant sur la scène,<br />
							- Les dates de <b>début</b> et <b>fin</b>,<br />
							- Le nombre de <b>dérivées</b> de la scène,<br />
							- Le nombre d'<b>assets</b> liés à la scène<br />
							- Le nombre de <b>plans</b> qu'elle contient (scène MASTER), ou le nombre de <b>cameras</b> qu'elle contient (scène FILLE).<br />
							- Un bouton <small class="nano"><button class="bouton"><i class="ui-icon ui-icon-bookmark"></i></button></small> pour
							  assigner des tâches à des utilisateurs concernant cette scène.
						</p>
					</div>
					<div class="fixFloat"></div>
					<div id="help_scenes_vignette">
						<h5>Vignette de la scène sélectionnée</h5>
						<p>
							Une image qui permet d'identifier la scène au premier coup d'oeil. Vous pouvez changer l'image en glissant-déposant un
							fichier image sur l'emplacement de la vignette.
						</p>
					</div>
					<div id="help_scenes_hung_by">
						<h5>L'utilisateur ayant pris la main</h5>
						<p>
							Quand le fichier de la scène est <b>libre</b>, vous pouvez voir : <img src="<?php echo $dirHelp ?>/help_scene_hungFree.jpg" class="inline mid marge5" /><br />
							En revanche, quand il est <b>utilisé par quelqu'un</b> qui travaille dessus (a "pris la main" sur le fichier), voici
							ce qui est affiché : <img src="<?php echo $dirHelp ?>/help_scene_hungBy.jpg" class="inline mid marge5" />
						</p>
						<p>
							Cependant, pour qu'une assignation à un utilisateur soit possible, il faut qu'<b>une étape ait été choisie</b> dans un département.<br />
							<br />
							Sinon, les assignations sont <b>bloquées</b>, même si le fichier est "libre" (tenu par personne) :
							<img src="<?php echo $dirHelp ?>/help_scene_hungLock.jpg" class="inline mid marge5" />
						</p>
					</div>
				</li>
				<li id="help_scenes_right_view">
					<h4>Panneau de la scène sélectionnée</h4>
					<p>
						Pour les scènes master, ce panneau est le même que la partie centrale de la fenêtre, sauf pour l'onglet "infos",
						qui permet de modifier les informations de la scène (et pas un simple affichage).<br />
						Dans tout les cas, ce panneau comporte des onglets en haut :
					</p>
					<ol id="help_scenes_right_view_tabs">
						<li id="help_scenes_published_panel">
							<b>Published</b> : Affiche les messages et les published de la scène pour le département choisi (voir "Discussions autour d'une scène").
						</li>
						<li>
							<b>Dérivées</b> (sc. "master" seulement) : La liste des dérivées (scènes filles) de la scène sélectionnée. Le bouton
							<small class="mini"><button class="bouton">Create Derivative</button></small> sert à créer une dérivée (scène fille) de la scène master sélectionnée.
							(voir "Créer une dérivée").
						</li>
						<li id="help_scenes_informations_panel">
							<b>Infos</b> : Pour modifier les informations de la scène (utilisateurs, description, dates...)<br />
							Cliquez sur un bouton <small class="nano"><button class="bouton"><i class="ui-icon ui-icon-pencil"></i></button></small> pour
							modifier une information.<br /><br />
							Le <b>label</b> est aussi affiché pour info (avec le chemin).<br /><br />
						</li>
						<li id="help_scenes_assets_panel">
							<b>Assets</b> : La liste des assets inclus dans la scène. Le bouton <small class="mini"><button class="bouton">Manage Assets</button></small>
							sert à gérer l'<b>inclusion d'assets</b> (pour les scènes Master), ou l'<b>exclusion d'assets</b> (pour les scènes Filles)
							(voir "Associer des assets à une scène").
						</li>
						<li id="help_scenes_shots_panel">
							<b>Plans</b> : La liste des plans qui sont associés à la scène, avec la caméra utilisée pour l'association.
							Le bouton <small class="mini"><button class="bouton">Assigner Plans</button></small>
							sert à gérer l'association des plans à la scène. Si c'est une scène "master", le bouton servira à l'assignation des plans au sein des dérivées
							de la scène master.<br />
							Le bouton <small class="mini"><button class="bouton">Gérer Cameras</button></small> sert à créer des caméras dans la scène (fille seulement)
							et à les associer à des plans. (voir "Associer une scène à des plans").
						</li>
					</ol>
				</li>
			</ul>
		</li>
		<li>
			<h4>Ajouter une scène MASTER</h4>
			<div class="margeTop10" id="help_create_master_scene">
				<p>
					Pour créer une scène "Master", cliquez sur le bouton
					<small class="petit"><button class="bouton ui-state-default">Create Master scene<i class="inline mid ui-icon ui-icon-plusthick"></i></button></small>.<br />
					<br />
					Un formulaire apparaît en dessous :
				</p>
				<img src="<?php echo $dirHelp ?>/help_scene_create_master.jpg" class="floatL marge5 shadowOut" />
				<p>
					Ici vous pouvez définir un titre, affecter un superviseur, un lead, et une équipe pour la scène, ainsi que les dates de début
					et fin ("deadline"), et une description. Seul le titre est obligatoire.<br />
					Attention, le titre est non modifiable une fois que la scène a été créée.<br />
					Le label est généré automatiquement par SaAM, pour le fonctionnement interne et l'accès via l'API.<br />
					Une fois terminé, cliquez sur le bouton
					<small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-check"></i></button></small>.<br />
					Vous pouvez annuler l'opération avec le bouton
					<small class="pico"><button class="bouton ui-state-error"><i class="ui-icon ui-icon-cancel"></i></button></small>.
				</p>
				<p>
					Une fois que la scène a été créée, elle apparaît tout en bas de la liste. Notez qu'il faut afficher la liste de toutes les
					scènes du projet (onglet "list all"), parce que cette nouvelle scène n'est assignée à aucune séquence pour le moment,
					donc sera invisible dans la liste triée par séquence.
				</p>
			</div>
			<div class="fixFloat"></div>
		</li>
		<li>
			<h4>Créer une dérivée (scène FILLE)</h4>
			<div class="margeTop10" id="help_scenes_create_derivative">
				<img src="<?php echo $dirHelp ?>/help_scene_create_deriv.jpg" class="floatL marge5 shadowOut" />
				<p>
					Pour créer une dérivée, il faut cliquer sur le bouton <small class="mini"><button class="bouton">Create Derivative</button></small>.
					Un formulaire apparaît, dans lequel vous devez renseigner les informations de la scène dérivée :<br />
					- un <b>titre</b> pour la dérivée<br />
					- le <b>label</b> est donné par SaAM automatiquement<br />
					- les <b>utilisateurs</b> concernés par la scène (superviseur, lead, artistes)<br />
					- les <b>dates</b> de début et fin (deadline)<br />
					- une <b>description</b> rapide de la scène<br />
				</p>
				<p>
					Une fois que la dérivée a été créée, elle apparaît dans la liste à gauche, sous la scène master sélectionnée,
					mais aussi dans le panneau central (informations de la scène master) et en dessous du bouton "create derivative"
					du panneau de droite.
				</p>
				<div class="fixFloat"></div>
			</div>
		</li>
		<li>
			<h4>Discussions autour d'une scène</h4>
			<div class="margeTop10" id="help_scenes_published">
				<p>
					Un "published" correspond à une étape clé dans la réalisation d'une scène. C'est une image ou une vidéo qui montre l'avancement
					de la réalisation, autour de laquelle les personnes concernées (équipe de la scène, lead, superviseur) peuvent entammer une discussion.
				</p>
				<img src="<?php echo $dirHelp ?>/help_shot_published.jpg" class="floatL marge5 shadowOut"/>
				<p>
					Tout en haut de la liste des published, se trouve le <b>dernier published</b>. Les messages (partie droite de la fenêtre) sont associés à ce
					published.
				</p>
				<p>
					Si l'icône <img src="gfx/icones/icone_valid.png" /> est présent, cela veut dire que le published a été <b>VALIDÉ</b> par un superviseur,
					et que la discussion autour de ce published est terminée (aucun nouveau message ne peut être posté).
				</p>
				<p>
					Vous pouvez sélectionner un published plus ancien, dans la liste, en cliquant sur son numéro. Cela fera apparaître l'ancien published en dessous du
					dernier, et les messages associés à ce published seront affichés à droite (mais non modifiables).
				</p>
				<p>
					Les boutons se trouvant en bas à droite du published actuel sont les suivants <i>(cette liste peut varier selon les cas, comme par exemple
					votre statut utilisateur, ou l'état du published)</i> :<br />
					- <small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-arrowthickstop-1-s"></i></button></small>
					Pour télécharger le fichier du published sur votre machine.<br />
					- <small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-pencil"></i></button></small>
					Pour dessiner sur l'image du published (visible seulement si le plugin "DrawTool" est activé, et le published non validé).<br />
					- <small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-wrench"></i></button></small>
					Pour remplacer le fichier du published. Ceci aura pour effet de déplacer le fichier du published précédent dans le dossier
					"Work in Progress" (voir "dossiers des scenes").<br />
					- <small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-disk"></i></button></small>
					Pour valider le published. Ceci aura pour effet de clore la discussion (aucun nouveau message ne pourra être posté), et de
					permettre l'envoi d'un nouveau published.<br />
				</p>
				<div class="fixFloat"></div>
				<p id="help_scenes_add_published">
					<img src="<?php echo $dirHelp ?>/help_shot_published_add.jpg" class="floatL marge5 shadowOut"/>
					Pour <b>ajouter</b> un published, il faut d'abord que le précédent soit <b>validé</b> (voir ci-dessus), ou bien qu'aucun published ne soit présent.<br />
					Ensuite, un bouton <small class="nano"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-plusthick"></i></button></small>
					apparaît dans la barre noire au dessus. Cliquez dessus pour faire apparaître la zone de dépose de fichier. Ensuite, vous pouvez
					glisser-déposer un fichier <b>image ou vidéo</b> sur la zone, puis une fois l'envoi terminé, n'oubliez pas de cliquer sur le bouton
					<small class="pico"><button class="bouton ui-state-error"><i class="ui-icon ui-icon-check"></i></button></small> pour valider l'ajout
					du published.<br />
					Si vous voulez annuler l'ajout, cliquez sur le bouton
					<small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-cancel"></i></button></small>.<br />
					<br />
				</p>
				<div class="fixFloat"></div>
			</div>
			<div class="margeTop10" id="help_scenes_messages">
				<img src="<?php echo $dirHelp ?>/help_shot_messages.jpg" class="floatL marge5 shadowOut"/>
				<p>
					Une discussion est liée à un published. Pour qu'une discussion soit possible, il faut donc qu'un published soit présent,
					et non validé. Une fois le published validé, la discussion est close (mais peut quand même être consultée en sélectionnant
					le published dans la liste à gauche).<br />
					La liste des messages est triée par date, du plus récent (en haut) au plus ancien (en bas).<br />
					Dans l'entête de chaque message se trouve le pseudo et l'avatar de l'utilisateur qui l'a posté, ainsi que la date et l'heure
					de sa création.
				</p>
				<p>
					Pour poster un message, vous pouvez cliquer sur le bouton
					<small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-mail-closed"></i></button></small>
					situé dans la barre noire au dessus, ou bien répondre à un message existant en cliquant sur ce même bouton dans l'entête
					du message auquel vous voulez répondre. Les réponses sont indentées vers la droite.
				</p>
				<p>
					Si vous êtes l'auteur d'un message, vous pouvez le supprimer grâce au bouton
					<small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-trash"></i></button></small>.
				</p>
				<div class="fixFloat"></div>
			</div>
		</li>
		<li>
			<h4>Associer une scène à des plans</h4>
			<div class="margeTop10">
				<p></p>
			</div>
		</li>
		<li>
			<h4>Associer des assets à une scène</h4>
			<div class="margeTop10">
				<p></p>
			</div>
		</li>
		<li>
			<h4>Gestion des caméras</h4>
			<div class="margeTop10">
				<p></p>
			</div>
		</li>
		<li>
			<h4>Vos scenes</h4>
			<div class="margeTop10" id="help_vos_scenes">
				<img src="<?php echo $dirHelp ?>/help_mes_scenes.jpg" class="floatL marge10r marge15bot shadowOut"/>
				<p>Il s'agit de la liste des scènes qui vous sont attribuées (scènes dont vous faites partie de l'équipe).</p>
				<p>La liste est rafraîchie toutes les 3 minutes.<br />Défilement vertical avec la molette souris possible.</p>
				<div class="fixFloat"></div>
				<p>Les attributions se font dans "scènes" , puis infos, <i>(des droits utilisateurs spéciaux sont nécessaires)</i></p>
			</div>
		</li>
	</ol>