<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
?>

<script>
	$(function(){
		$('.bouton').button();
	});
</script>

<script src="ajax/admin_news.js"></script>

<div class="stageContent pad5">
	<h2>Ajout de news</h2>

	<div class="margeTop1">
		<div class="inline mid w100 marge10l">Titre : </div>
		<div class="inline mid ui-state-error noBG noBorder"><span class="ui-icon ui-icon-notice"></span></div>
		<input type="text" id="news_title" class="noBorder pad3 ui-corner-top w500 fondSect3 addNews" title="Titre de la nouvelle" />
	</div>
	<div>
		<div class="inline top w100 marge10l margeTop10">Contenu : </div>
		<div class="inline top  margeTop10 ui-state-error noBG noBorder"><span class="ui-icon ui-icon-notice"></span></div>
		<textarea id="news_text" rows="20" class="inline top noBorder pad3 ui-corner-bottom w500 fondSect3 addNews"  title="Texte de la nouvelle" ></textarea>
	</div>

	<div class="floatR margeTop10 marge30r addNewBtns hide">
		<?php if ( !$_SESSION['user']->isDemo() ) : ?>
			<button class="bouton addNewBtn_savePublish" title="sauvegarder la nouvelle et la publier tout de suite">Ajouter et publier</button>
			<button class="bouton addNewBtn_save" title="sauvegarder la nouvelle pour la publier plus tard">Sauvegarder</button>
			<button class="bouton addNewBtn_cancel" title="annuler et revenir à la liste des nouvelles">Annuler</button>
		<?php else: ?>
			<div class="marge30r"><button class="bouton marge30r"><span class="colorErreur"><?php echo @L_DEMO_MODE; ?></span></button></div>
		<?php endif; ?>
	</div>
</div>