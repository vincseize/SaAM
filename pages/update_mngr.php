<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	try {
		$SI = new Infos(TABLE_CONFIG);
		$SI->loadInfos('version', SAAM_VERSION);
		$version = $SI->getInfo('version');
		$version_previous = $SI->getInfo('oldversion');
	}
	catch (Exception $e) {
		$version = false;
		$lastVersion = Liste::getMax(TABLE_CONFIG, 'version');
	}
?>

<div class="pageContent">

	<div class="stageContent pad5">
		<h2>UPDATE MANAGER</h2>

		<?php if ($version) : ?>
			<p class="marge30l">Version SaAM actuelle : <b><?php echo SAAM_VERSION; ?></b></p>
			<p class="marge30l green">Le SaAM est à jour !</p>
		<?php else: ?>

			<script>
				$(function(){
					$('.bouton').button();

					$('#createVersionBDD').click(function(){
						var lastV = $(this).attr('lastVersion');
						var ajaxReq = 'action=copySaAMVersion&lastV='+lastV;
						AjaxJson(ajaxReq, "admin/admin_config_actions", retourAjaxMsg, 'all');
					});
				});
			</script>

			<div class="inline ui-widget-content ui-corner-all pad5 marge30l">
				<p class="red">ATTENTION : La version <b><?php echo SAAM_VERSION; ?></b> est disponible !</p>
				<p class="ui-state-disabled">Version SaAM actuelle : <b><?php echo SAAM_PREV_VERSION; ?></b></p>
				<p>
					Créer l'enregistrement en BDD<br />(récup de la version <?php echo $lastVersion; ?>) : <button class="bouton marge30l" id="createVersionBDD" lastVersion="<?php echo $lastVersion; ?>">Créer</button>
				</p>
			</div>

		<?php endif; ?>
	</div>
</div>