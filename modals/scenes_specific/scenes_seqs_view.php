<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	extract($_POST);
	/////////// SEQUENCES VIEW ///////////////

	$pr = new Projects($idProj);
	$pSeqs = $pr->getSequences(true);

	$l = new Liste();
	$l->addFiltre(Scenes::ID_PROJECT, '=', $idProj);
	$l->addFiltre(Scenes::MASTER, '=', "0");
	$l->addFiltre(Scenes::ARCHIVE, '=', '0');
	$l->getListe(TABLE_SCENES);
	$scenesMList = $l->simplifyList();
	$scMTotal = count($scenesMList);

	if (!is_array($scenesMList)) die('<div class="ui-state-disabled pad5 gros">'.L_NO_SCENE.'</div>');
?>

<script>
	var nbScenes = <?php echo $scMTotal ?>;
	$(function() {
		$('.bouton').button();

		openArboScenes();

		// Click sur scene folder
		$('.sceneFolder').click(function(){
			if ($(this).attr('opened') == 'opened') {
				$(this).removeAttr('opened').css('border-bottom','none').next('.sceneFolderContent').hide(150);
			}
			else {
				$('.sceneFolder').removeAttr('opened').next('.sceneFolderContent').hide(150);
				$(this).attr('opened', 'opened').next('.sceneFolderContent').show(150);
			}
		});

	});
</script>

<div id="scenesList" help="scenes_list_sequences"><?php
foreach($pSeqs as $se): ?>
	<div>
		<div class="gros colorSoft sceneFolder" idSeq="<?php echo $se[Sequences::SEQUENCE_ID_SEQUENCE]; ?>"><?php echo $se[Sequences::SEQUENCE_TITLE]; ?></div>
		<div class="gros sceneFolderContent"><?php
		foreach($scenesMList as $scene):
			$scSeqs = json_decode($scene[Scenes::SEQUENCES], true);
			if (!is_array($scSeqs)) continue;
//			echo '<pre>'; var_dump($scSeqs); echo '</pre>';
			if (!in_array_r($se[Sequences::SEQUENCE_ID_SEQUENCE], $scSeqs)) continue;
			$filles = json_decode($scene[Scenes::DERIVATIVES]); ?>
			<div class="colorBtnFake sceneItem sceneMasterItem" title="<?php echo $scene[Scenes::TITLE]; ?>" sceneID="<?php echo $scene[Scenes::ID_SCENE]; ?>"><?php echo $scene[Scenes::TITLE]; ?></div>
			<div class="sceneMasterContent" sceneID="<?php echo $scene[Scenes::ID_SCENE]; ?>"><?php
			if (is_array($filles)):
				foreach($filles as $filleID):
					$scF = new Scenes($filleID);
					if (!$scF->isActive()) continue;
					if (!in_array($se[Sequences::SEQUENCE_ID_SEQUENCE], $scF->getSequences())) continue;
					$filleTitle = $scF->getSceneInfos(Scenes::TITLE); ?>
					<div class="colorDark sceneItem sceneFilleItem" title="<?php echo $filleTitle; ?>" sceneID="<?php echo $filleID; ?>"><?php echo $filleTitle; ?></div><?php
				endforeach;
			endif; ?>
			</div><?php
		endforeach; ?>
		</div>
	</div><?php
endforeach; ?>
	<p>&nbsp;</p>
</div>
