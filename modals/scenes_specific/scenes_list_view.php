<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	extract($_POST);
	/////////// LIST VIEW ///////////////

	$l = new Liste();
	$l->addFiltre(Scenes::ID_PROJECT, '=', $idProj);
	$l->addFiltre(Scenes::MASTER, '=', "0");
	$l->getListe(TABLE_SCENES);
	$scenesMList = $l->simplifyList();
	$scMTotal = count($scenesMList);
	$scMCount = 0;

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
				if (nbScenes > 10 )
					$(this).removeAttr('opened').css('border-bottom','none').next('.sceneFolderContent').hide(150);
			}
			else {
				$('.sceneFolder').removeAttr('opened').next('.sceneFolderContent').hide(150);
				$(this).attr('opened', 'opened').next('.sceneFolderContent').show(150);
			}
		});

	});
</script>


<div id="scenesList" help="scenes_list_all">
	<div>
<?php
foreach($scenesMList as $scene):
	$color = '';
	if ($scene[Scenes::ARCHIVE] == '1') {
		if ($_SESSION['user']->isSupervisor())
			$color = 'gray-layer';
		else continue;
	}
	$filles = json_decode($scene[Scenes::DERIVATIVES]);
	if ($scMCount%10 == 0): ?>
	</div>
	<div class="gros colorSoft sceneFolder"><?php echo L_SCENES.' '.($scMCount+1); ?><span class="inline ui-state-disabled bot ui-icon ui-icon-arrow-1-e"></span><?php echo $scMCount+10; ?></div>
	<div class="gros sceneFolderContent">
	<?php endif; ?>
		<div class="<?php echo $color; ?> colorBtnFake sceneItem sceneMasterItem" title="<?php echo $scene[Scenes::TITLE]; ?>" sceneID="<?php echo $scene[Scenes::ID_SCENE]; ?>"><?php echo $scene[Scenes::TITLE]; ?></div>
		<div class="sceneMasterContent" sceneID="<?php echo $scene[Scenes::ID_SCENE]; ?>">
		<?php if (is_array($filles)):
			foreach($filles as $filleID):
				$scF = new Scenes($filleID);
				$colorF = '';
				if (!$scF->isActive()) {
					if ($_SESSION['user']->isSupervisor())
						$colorF = 'gray-layer';
					else continue;
				}
				$filleTitle = $scF->getSceneInfos(Scenes::TITLE); ?>
				<div class="<?php echo $colorF; ?> colorDark sceneItem sceneFilleItem" title="<?php echo $filleTitle; ?>" sceneID="<?php echo $filleID; ?>"><?php echo $filleTitle; ?></div>
		<?php endforeach;
		endif; ?>
		</div>
<?php $scMCount++;
endforeach; ?>
	</div>
	<p>&nbsp;</p>
</div>