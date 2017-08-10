
<a id="sequences"></a>
<h2 class="ui-state-default ui-corner-top pad3">Séquences<div class="floatR marge30r doigt btnTop"><span class="ui-icon ui-icon-arrowreturn-1-n"></span></div></h2>
	<ol>
		<li>
			<h4>Définition d'une séquence</h4>
			<div class="margeTop10">
				Une "séquence" est une suite de plans.
			</div>
		</li>
		<li>
			<h4>Les départements des séquences</h4>
			<div class="margeTop10">
				<img src="<?php echo $dirHelp ?>/help_shot_menu.jpg" class="marge5bot shadowOut" />
				<p>Les départements des séquences sont les mêmes que ceux des ><a class="helpBtn noBorder" content="shots">plans</a><.</p>

			</div>
		</li>
		<li>
			<h4>Ajouter une séquence</h4>
			<div class="margeTop10">
				<p>Pour ajouter une séquence, vous devez aller dans le département "STRUCTURE PLANS" (il faut donc que vous y ayez accès).</p>
			</div>
			<div id="help_add_sequence">
				<p>
					Dans l'entête de la fenêtre des séquences, à droite, vous trouverez un bouton <small class="micro"><button class="bouton"><i class="ui-icon ui-icon-plusthick"></i></button></small>.
					Cliquez sur celui-ci, et une ligne s'ajoute en haut de la liste des séquences (figure ci-dessous).<br /><br />
					<img src="<?php echo $dirHelp ?>/help_seq_add.jpg" class="marge5 shadowOut" />
				</p>
				<p>
					Vous pouvez donner un titre à la séquence (facultatif). Si vous ommettez le titre, le label sera utilisé comme titre.
					Spécifiez Ensuite le <b>nombre de plans</b> que contiendra la séquence (très pratique pour créer la structure de votre projet rapidement).
					Indiquez ensuite une <b>date de début</b> et une <b>date de fin</b> pour la séquence. Ces dates serviront
					au département "Calendrier" de la section "racine", mais aussi vous permettront de savoir le temps qu'il reste avant la date de livraison prévue ("deadline").
				</p>
				<p>
					Une fois terminé, vous pouvez cliquer sur le bouton <small class="micro"><button class="bouton ui-state-highlight"><i class="ui-icon ui-icon-check"></i></button></small>
					à droite de la ligne. La séquence s'ajoute en bas de la liste.
				</p>
				<p>
					Si vous le souhaitez, vous pouvez lui ajouter une description, une fois qu'elle a été ajoutée, en cliquant sur le bouton
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-pencil"></i></button></small>.
				</p>
				<div class="fixFloat"></div>
			</div>
		</li>
		<li>
			<h4>Modifier une séquence</h4>
			<div class="margeTop10" id="help_modify_sequence">
				<p>
					Chaque ligne de séquence contient les informations relatives à cette séquence. D'abord son <b>titre</b>, puis son <b>label</b>,
					et le nombre de <b>plans</b> qu'elle contient : le premier chiffre pour le nombre de plans visibles, le second pour le nombre total de plans de la séquence.<br />
					Ensuite, vous voyez une jauge qui montre la progression globale de la séquence (moyenne de la progression des plans).<br />
					Enfin, la <b>date de début</b>, la <b>date de fin</b>, et la liste des <b>utilisateurs assignés</b> à cette séquence.
				</p>
				<p>
					En <b>cliquant</b> sur une ligne de séquence, vous pourrez faire apparaître la <b>liste des plans</b> qu'elle contient.<br />
					Vous pouvez aussi <b>réorganiser</b> l'ordre des séquences, en les déplaçant (cliquer-déplacer) verticalement.
				</p>
				<p>
					Tout à droite de la ligne de séquence, se trouvent 5 boutons :
				</p>
				<p>
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-plusthick"></i></button></small>
					Pour <b>Ajouter des plans</b> à la séquence. (voir <a class="helpBtn noBorder" content="shots">Ajouter des plans</a>).<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-lightbulb"></i></button></small>
					Pour <b>cacher</b> la séquence. Elle ne sera alors visible plus que dans le département "Structure plans".<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-unlocked"></i></button></small>
					Pour <b>bloquer</b> la séquence. Elle sera toujours visible partout, mais ne sera plus modifiable.<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-pencil"></i></button></small>
					Pour <b>modifier les informations</b> de la séquence. En cliquant sur ce bouton, la ligne de la séquence sera transformée pour
					afficher des champs texte et autre sélecteurs permettant de modifier chaque information. Les boutons annuler et valider
					se trouvent à droite de la ligne.<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-trash"></i></button></small>
					Pour <b>archiver</b> la séquence. Une fois archivée, un séquence sera considérée comme supprimée, donc invisible et non modifiable.
					Cependant vous pourrez la restaurer à tout moment grâce au bouton
					<small class="micro"><button class="bouton ui-state-error"><i class="ui-icon ui-icon-refresh"></i></button></small> qui apparaîtra ensuite
					à la place des 5 boutons.
				</p>
			</div>
		</li>
	</ol>