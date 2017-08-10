
<a id="shots"></a>
<h2 class="ui-state-default ui-corner-top pad3">Plans<div class="floatR marge30r doigt btnTop"><span class="ui-icon ui-icon-arrowreturn-1-n"></span></div></h2>
	<ol>
		<li>
			<h4>Définition d'un plan</h4>
			<div class="margeTop10">
				<p>
					Un "plan" est une partie de séquence, délimité dans le temps par un début et une fin, constitué d'une suite continue d'images enregistrées par la caméra au cours d'une même prise.<br />
					Chaque plan a une cadence (en FPS, images par secondes) qui peut être différente selon le département.
				</p>
			</div>
		</li>
		<li>
			<h4>Les départements des plans</h4>
			<div class="margeTop10">
				<img src="<?php echo $dirHelp ?>/help_shot_menu.jpg" class="marge5bot shadowOut" />
				<p>Les départements des plans sont divisés en deux types :</p>
				<ul>
					<li id="help_shots_departments">
						<b>Départements variables</b> : Ces départements ne sont présents qu'une fois qu'ils ont été spécifiquement choisis dans la configuration du
						projet. Il est possible de les renommer et d'en ajouter dans le panneau des outils, bouton "SaAM administration" (pour ceux qui y ont accès).
					</li>
					<li>
						<b>Départements fixes</b> : Ils sont présents dans tout les projets, vous ne pouvez pas les cacher ni les renommer. Ces départements sont :
						<ul>
							<li id="help_dept_shots_structure">
								<b>STRUCTURE PLANS</b> : C'est le département de départ, celui qui vous permet de créer des séquences, des plans, gérer les
								équipes assignées aux plans, et d'avoir un aperçu de l'avancement global des plans au sein du projet.
							</li>
							<li id="help_dept_scenario">
								<b>SCÉNARIO</b> : Dans ce département vous pouvez écrire le scénario, avec une mise en page, et l'exporter en PDF. Attention
								cependant, ce département est visible par tout les utilisateurs ayant accès au projet.
							</li>
							<li id="help_dept_tech_script">
								<b>DEC. TECH.</b> (pour "découpage technique") : Il s'agit du département qui vous servira à définir les valeurs de plans, le
								son, et tout ce qui peut servir à la scripte lors d'un tournage. Il est possible d'imprimer des planches-contact directement à partir de là.
							</li>
							<li id="help_dept_storyboard">
								<b>STORYBOARD</b> : Rien de secret, dans ce département vous pourrez y mettre les images du storyboard, plan par plan, et imprimer
								des planches-contact des séquences entières pour faciliter la production.
							</li>
							<li id="help_dept_final">
								<b>FINAL</b> : Ce département est un peu spécial, car il est présent dans toutes les sections (plans, assets, scenes...), et situé tout
								à fait à droite de la barre des départements.
								C'est ici que vous pourrez avoir un aperçu complet du film (ou d'une séquence particulière), en l'état de sa production, au jour
								le jour, et suivre l'évolution de tout le projet de manière visuelle.
							</li>
						</ul>
					</li>
				</ul>
				<div class="margeTop10">
					Dans tout les départements, la fenêtre d'un plan se compose de 4 parties :
					<ul>
						<li id="help_shot_header">
							<b>L'entête</b> : Dans cette partie se trouve toutes les informations générales du plan.<br />
							<span id="help_shot_department_name">En arrière plan, écrit en gros, le nom du département actuellement ouvert.</span>
							De gauche à droite, vous trouverez :
							<p id="help_shot_vignette">
								<img src="<?php echo $dirHelp ?>/help_shot_vignette.jpg" class="floatL marge5 shadowOut" /><br />
								La <b>vignette</b> du plan, avec au dessus son titre et le nom de sa séquence.<br />
								<br />
								Cette vignette est une miniature du dernier published. S'il n'y a aucun published pour le département, une image vide est affichée.<br />
								Le nombre affiché en bas à gauche de la vignette est le nombre d'assets qui sont utilisés dans le plan.
							</p>
							<div class="fixFloat"></div>
							<p id="help_shot_informations">
								<img src="<?php echo $dirHelp ?>/help_shot_infos.jpg" class="floatL marge5 shadowOut" /><br />
								Les informations générales du plan. Sa progression, l'équipe du plan (superviseur, lead, artistes), les dates de début et de fin,
								son format, sa cadence (en fps), sa durée (en nombre d'images), un rappel du nombre de jours restant avant la date de fin, et la description du plan.
							</p>
							<div class="fixFloat"></div>
							<p>
								<img src="<?php echo $dirHelp ?>/help_shot_depts_infos.jpg" class="marge5 shadowOut" /><br />
								Des boutons d'action relatives au département. Ces boutons peuvent être :
							</p>
							<p id="help_shot_action_buttons">
								- <small class="micro"><button class="bouton"><i class="ui-icon ui-icon-bookmark"></i></button></small> :
								Pour assigner des tâches à des utilisateurs concernant ce plan,<br />
								- <small class="micro"><button class="bouton"><i class="ui-icon ui-icon-arrowrefresh-1-s"></i></button></small> :
								Pour rafraîchir la fenêtre du plan dans le département actuel,<br />
								- <small class="micro"><button class="bouton"><i class="ui-icon ui-icon-pencil"></i></button></small> :
								Pour modifier les informations spécifiques à ce département pour ce plan.
							</p>
						</li>
						<li>
							<p>La partie de gauche affiche les published du plan pour le département.</p>
							<p>Voir ci-dessous pour plus d'informations sur les published.</p>
						</li>
						<li id="help_shot_center">
							<p>Dans la partie centrale se trouvent les <b>étapes</b> pour le département, et les <b>tags</b> du plan.</p>
							<div id="help_shot_steps">
								<img src="<?php echo $dirHelp ?>/help_shot_steps.jpg" class="floatL marge10 shadowOut" />
								<p>
									<b>Les étapes</b> sont définies pour chaque département par l'administrateur du SaAM (voir ><a class="helpBtn noBorder" content="departments">gestion des départements</a><).
									Seule l'étape <b>"Validé"</b> est obligatoire.
								</p>
								<p>
									Les étapes servent à <b>calculer la progression</b> du plan, de la séquence et donc du projet. Par exemple, si tout les
									départements d'un plan ont l'étape "Validé", la progression de ce plan sera de 100%. Si aucune étape n'est définie, il sera à 0%.
									Chaque étape intermédiaire défini le pourcentage de la progression du plan.
								</p>
							</div>
							<div class="fixFloat"></div>
							<p>
								Pour de l'aide à propos des tags, voir le chapitre ><a class="helpBtn noBorder" content="tags">assignation des tags</a>< (ou 'H' en survolant le bouton "Tags").
							</p>
						</li>
						<li>
							<p>La partie de droite affiche les messages associés au published du plan pour le département.</p>
							<p>Voir ci-dessous pour plus d'informations sur les messages.</p>
						</li>
					</ul>
				</div>
				<div class="margeTop10">
					<p>
						Dans le département <b>"STRUCTURE PLANS"</b>, vous pouvez avoir un aperçu de l'état et les informations de chaque département pour un plan.<br />
						Tout d'abord, ouvrez un plan en cliquant sur une vignette ou un label dans la liste des plans, ou en utilisant le menu des raccourcis, en haut à gauche.
					</p>
					<div id='help_shot_all_depts_infos'>
						<p>
							<img src="<?php echo $dirHelp ?>/help_shot_depts_assign.jpg" class="marge5 shadowOut" /><br />
							Sur la droite se trouve un sélecteur qui permet d'<b>assigner des départements</b> rapidement au plan.<br />
							Tout à fait à droite se trouve la <b>date de dernière modification</b>, ainsi que le pseudo de l'utilisateur ayant fait cette dernière modification.
						</p>
						<p>
							<img src="<?php echo $dirHelp ?>/help_shot_all_depts_infos.jpg" class="floatL marge10 shadowOut" />
							Dessous, une liste des départements dans lesquels se trouvent déjà des informations relatives à ce plan : le <b>nombre de published</b>,
							la <b>cadence</b>, l'<b>étape</b> actuelle. Notez que le nombre de published est <b>en vert</b> si le dernier published est validé.<br />
							Vous pouvez ouvrir directement un département en cliquant sur le petit bouton
							<small class="nano"><button class="bouton"><i class="ui-icon ui-icon-arrowthickstop-1-e"></i></button></small>.<br />
							<br />
						</p>
					</div>
					<div class="fixFloat"></div>
				</div>
			</div>
		</li>
		<li>
			<h4>Ajouter des plans</h4>
			<div class="margeTop10" id="help_add_shot">
				<img src="<?php echo $dirHelp ?>/help_shot_add.jpg" class="floatL marge10 shadowOut" />
				<p>Pour ajouter un ou plusieurs plans, vous devez vous rendre dans le département "STRUCTURE PLANS" (il faut donc que vous y ayez accès).</p>
				<p>
					Sur chaque <b>ligne de séquence</b>, vous trouverez un bouton <small class="micro"><button class="bouton"><i class="ui-icon ui-icon-plusthick"></i></button></small>.
					Cliquez sur celui de la séquence concernée, et une fenêtre s'ouvre (figure ci-contre).
				</p>
				<p>
					Tout d'abord, spécifiez le <b>nombre de plans</b> que vous voulez ajouter. Pour chaque plan qui est apparu dans la fenêtre, indiquez
					un <b>titre</b>, une <b>date de début</b> et une <b>date de fin</b>. Ces dates serviront au département "Calendrier" de la section
					"racine", mais aussi vous permettront de savoir le temps qu'il reste avant la date de rendu prévue ("deadline").
				</p>
				<p>
					Une fois terminé, vous pouvez cliquer sur le bouton <small class="petit"><button class="bouton">Valider</button></small> en bas de la fenêtre. Les plans s'ajoutent à
					la séquence ouverte dans la fenêtre principale.
				</p>
				<div class="fixFloat"></div>
			</div>
		</li>
		<li>
			<h4>Modifier un plan</h4>
			<div class="margeTop10">
				<p>
					Vous pouvez modifier les informations d'un plan en vous rendant dans le département "STRUCTURE PLANS" (il faut donc que vous y ayez accès).
					<br />
					Deux manières possibles :
				</p>
				<ul>
					<li>Depuis la fenêtre des séquences</li>
					<li>Dans la fenêtre de plan, après l'avoir sélectionné dans la fenêtre des séquences</li>
				</ul>
				<h5>Depuis la fenêtre des séquences</h5>
				<p id="help_modify_shots_from_sequence_window">
					Chaque ligne de plan contient les informations relatives à ce plan. Pour ouvrir le plan dans sa propre fenêtre et voir plus
					de détails le concernant, Il suffit de cliquer sur sa vignette ou son label. La <b>vignette</b> affichée ici est celle présente dans le département
					le plus à droite possible. Ensuite on peut voir son <b>titre</b>, son <b>label</b>, et le <b>nombre de published</b> qu'il contient.<br />
					Le sélecteur de département vous permet d'<b>assigner le plan à des départements</b> de manière rapide.<br />
					Puis, la <b>date de début</b>, la <b>date de fin</b>, et la liste des <b>utilisateurs assignés</b> à ce plan.<br />
					<br />
					Enfin, tout à droite de la ligne de plan, se trouvent 4 boutons :<br />
					<br />
					<small class="micro"><button class="bouton ui-state-highlight"><i class="ui-icon ui-icon-lightbulb"></i></button></small>
					Pour <b>cacher</b> le plan. Il ne sera alors visible plus que dans le département "Structure plans".<br />
					<small class="micro"><button class="bouton ui-state-highlight"><i class="ui-icon ui-icon-unlocked"></i></button></small>
					Pour <b>bloquer</b> le plan. Il sera toujours visible partout, mais ne sera plus modifiable.<br />
					<small class="micro"><button class="bouton ui-state-highlight"><i class="ui-icon ui-icon-pencil"></i></button></small>
					Pour <b>modifier les informations</b> du plan. En cliquant sur ce bouton, la ligne du plan sera transformée pour
					afficher des champs texte et autre sélecteurs permettant de modifier chaque information. Les boutons annuler et valider
					se trouvent à droite de la ligne.<br />
					<small class="micro"><button class="bouton ui-state-highlight"><i class="ui-icon ui-icon-trash"></i></button></small>
					Pour <b>archiver</b> le plan. Une fois archivé, un plan sera considéré comme supprimé, donc invisible et non modifiable.
					Cependant vous pourrez le restaurer à tout moment grâce au bouton
					<small class="micro"><button class="bouton ui-state-error"><i class="ui-icon ui-icon-refresh"></i></button></small> qui apparaîtra ensuite
					à la place des 4 boutons.<br />
					<br />
					Vous pouvez aussi <b>réorganiser</b> l'ordre des plans, en les déplaçant (cliquer-déplacer) les uns au dessus des autres.<br />
					<br />
				</p>
				<h5>Dans la fenêtre du plan</h5>
				<p>
					Ouvrez le plan en cliquant sur sa vignette dans la liste des plans. Sur la partie gauche de la fenêtre ainsi ouverte, se trouve
					un formulaire permettant de modifier les informations du plan.
				</p>
				<p id="help_modify_shot">
					<img src="<?php echo $dirHelp ?>/help_shot_modify.jpg" class="floatL marge10 shadowOut" /><br />
					Ici vous pouvez modifier le <b>titre</b> du plan, ses <b>dates</b> de début et fin, choisir un utilisateur qui sera <b>superviseur</b> du plan
					(une popup vous donnera la liste des utilisateurs disponibles), et un utilisateur qui sera <b>"Lead"</b>.<br />
					Vous pouvez aussi spécifier la <b>durée du plan</b> en images (frames). Cette valeur sera utilisée dans tout les autres départements. Il
					s'agit de la durée donnée pour le montage final.<br />
					Enfin, une <b>description</b> peut être définie, pour être affichée dans l'entête des fenêtres du plan de chaque départements.<br />
					<br />
					Une fois les modifications apportées, <b>n'oubliez pas de cliquer sur le bouton</b> <small class="petit"><button class="bouton ui-state-default">Valider</button></small>.<br />
					Vous pouvez aussi annuler les modifications et revenir à celles qui étaient présentes.<br />
					<br />
				</p>
				<div class="fixFloat"></div>
				<p id="help_modify_shot_team">
					<img src="<?php echo $dirHelp ?>/help_shot_modify_shot_team.jpg" class="floatL marge10 shadowOut" />
					Pour modifier la liste des utilisateurs assignés au plan, utilisez le bouton "<b class="big gras">+</b>" situé dans l'entête de la fenêtre de plan.<br />
					<br />
					Un sélecteur apparaît, dans lequel se trouve la liste des utilisateurs assignés au projet. Faites votre choix, puis cliquez sur le petit bouton
					<small class="nano"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-check"></i></button></small><br />
					Pour annuler, cliquez sur le bouton "<b class="big gras">-</b>" à gauche.<br />
				</p>
				<div class="fixFloat"></div>
				<p id="help_modify_shot_buttons">
					<img src="<?php echo $dirHelp ?>/help_shot_modify_shot_btns.jpg" class="floatL marge5 shadowOut" /><br />
					Tout en haut à droite de l'entête de la fenêtre de plan se trouvent 4 boutons :<br />
					<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-arrowrefresh-1-s"></i></button></small>
					Pour <b>rafraîchir</b> la fenêtre de plan.<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-lightbulb"></i></button></small>
					Pour <b>cacher</b> le plan. Il ne sera alors visible plus que dans le département "Structure plans".<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-unlocked"></i></button></small>
					Pour <b>bloquer</b> le plan. Il sera toujours visible partout, mais ne sera plus modifiable.<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-trash"></i></button></small>
					Pour <b>archiver</b> le plan. Une fois archivé, un plan sera considéré comme supprimé, donc invisible et non modifiable.
					Cependant vous pourrez le restaurer à tout moment grâce au bouton
					<small class="micro"><button class="bouton ui-state-highlight"><i class="ui-icon ui-icon-refresh"></i></button></small> qui apparaîtra ensuite
					à la place du bouton "supprimer".<br />
					<br />
				</p>
				<div class="fixFloat"></div>
				<p id="help_shot_back_to_sequences">
					Pour revenir à la <b>liste des séquences</b>, utilisez le bouton <small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-arrowthickstop-1-n"></i></button></small>
					situé en haut à gauche de l'entête de la fenêtre de plan (sur la vignette en haut).<br />
					<br />
				</p>
			</div>
		</li>
		<li>
			<h4>"Published" d'un plan</h4>
			<div class="margeTop10" id="help_shot_published">
				<p>
					Un "published" correspond à une étape clé dans la réalisation d'un plan. C'est une image ou une vidéo qui montre l'avancement
					de la réalisation, autour de laquelle les personnes concernées (équipe du plan, lead, superviseur) peuvent entammer une discussion.
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
					"Work in Progress" (voir "dossiers des plans").<br />
					- <small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-disk"></i></button></small>
					Pour valider le published. Ceci aura pour effet de clore la discussion (aucun nouveau message ne pourra être posté), et de
					permettre l'envoi d'un nouveau published.<br />
				</p>
				<div class="fixFloat"></div>
				<p id="help_shot_add_published">
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
		</li>
		<li>
			<h4>Discussions autour d'un plan</h4>
			<div class="margeTop10" id="help_shot_messages">
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
			</div>
			<div class="fixFloat"></div>
		</li>
		<li>
			<h4>Dossiers des plans</h4>
			<div class="margeTop10" id="help_shot_folders">
				<p>
					Les dossiers des plans servent à y déposer des fichiers divers, comme des références, des essais, de l'inspiration...<br />
					Ils sont <b>liés aux départements</b>, c'est à dire que chaque département a sa propre liste de dossiers.
				</p>
				<img src="<?php echo $dirHelp ?>/help_shot_folders.jpg" class="marge5 shadowOut"/>
				<p>
					Par défaut, il existe 2 dossiers (non modifiables, non supprimables) : <b>Bank shot</b>, et <b>Work in progress</b>. Ce dossier "WIP"
					est celui dans lequel les fichiers des published modifiés se retrouvent, afin de garder une trace de l'évolution des published.<br />
					<br />
					En survolant un dossier avec la souris, vous pourrez lire le <b>nombre de fichiers</b> qu'il contient, et verrez un bouton
					<small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-trash"></i></button></small> pour <b>supprimer</b> le
					dossier entier (seulement pour les dossiers que vous avez créé).<br />
					<br />
					En cliquant sur un dossier, vous l'ouvrez et affichez à droite la <b>liste des fichiers</b> qu'il contient.<br />
				</p>
				<p>
					Vous pouvez <b>créer un dossier</b> à tout moment en utilisant le bouton
					<small class="nano"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-plusthick"></i></button></small> situé en haut
					à droite des dossiers.
				</p>
				<p>
					Pour <b>ajouter un fichier</b> dans un dossier, ouvrez d'abord le dossier, puis glissez-déposez un fichier de votre machine sur la
					zone de droite (celle-ci devient bleue au survol de la souris avec un fichier). Aucune validation n'est nécessaire après l'envoi, le
					fichier apparaît simplement dans la liste.
				</p>
				<p>
					Dans la liste des fichiers, au survol de la souris, vous pouvez lire le <b>numéro du fichier</b>, et (si vous avez les droits nécessaire) un bouton
					<small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-trash"></i></button></small> pour <b>supprimer</b>
					le fichier.<br />
					<br />
					En haut à droite de la liste des fichiers se trouvent <b>2 boutons</b> :
				</p>
				<p id="help_shot_folders_btns">
					- <small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-clipboard"></i></button></small>
					Pour imprimer une planche contact de tout les fichiers images du dossier,<br />
					- <small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-suitcase"></i></button></small>
					Pour télécharger le contenu du dossier au format ZIP sur votre machine.<br />
					<br />
				</p>
			</div>
		</li>
		<li>
			<h4>Lien avec les scènes</h4>
			<div class="margeTop10">
				Chaque plan peut être assigné à une "scène dérivée", par l'intermédiaire d'une caméra.
				(voir ><a class="helpBtn noBorder" content="scenes">associer une scène à des plans</a><)<br />
			</div>
		</li>
		<li>
			<h4>Vos plans</h4>
			<div class="margeTop10" id="help_vos_plans">
				<img src="<?php echo $dirHelp ?>/help_mes_plans.jpg" class="floatL marge10r marge15bot shadowOut"/>
				<p>Il s'agit de la liste des plans qui vous sont attribués (plans dont vous faites partie de l'équipe).</p>
				<p>La liste est rafraîchie toutes les 3 minutes. Scroll vertical molette souris possible.</p>
				<div class="fixFloat"></div>
				<p>Les attributions se font dans le département "Structure Plans" <i>(des droits utilisateurs spéciaux sont nécessaires)</i> :</p>
				<img src="<?php echo $dirHelp ?>/help_mes_plans2.jpg" class="shadowOut"/>
			</div>
		</li>
	</ol>