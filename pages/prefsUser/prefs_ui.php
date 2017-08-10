<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	// Récupère les infos de l'utilisateur
	$infosUser = $_SESSION['user']->getUserInfos();
?>

<script>
	$(function(){
		$('.bouton').button();
	});
</script>

<?php if (!$_SESSION['user']->isDemo()): ?>
	<script src="ajax/prefs_ui.js"></script>
<?php endif; ?>

<div class="inline bot big gras pad5">Préférences de l'interface</div>
<div class="inline bot petit pad5 colorMid"><i>(<?php echo $infosUser['pseudo']; ?>)</i></div>
<br />
<br />
<div class="inline top gros" userID="<?php echo $infosUser['id']; ?>">
	<div class="marge15bot marge10l">
		<div class="inline mid w300 colorBtnFake">Temps avant déconnexion automatique</div>
		<div class="inline mid">
			<input type="text" class="noBorder ui-corner-all fondSect4 pad3 w80" id="modifDeconxTime" value="<?php echo $infosUser['deconx_time']; ?>" />
		</div>
		<div class="inline mid w80">minutes</div>
		<div class="inline mid pico" style="display:none;" id="modifDeconxTimeBtns">
			<button class="bouton" id="modifDeconxTimeValid"><span class="ui-icon ui-icon-check"></span></button> &nbsp;
			<button class="bouton ui-state-error" id="modifDeconxTimeCancel"><span class="ui-icon ui-icon-cancel"></span></button>
		</div>
	</div>
</div>