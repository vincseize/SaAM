<?php
@session_start();

if (!isset($_SESSION["user"])) die('No user connected.');

$dontTouchSSID = true;

require_once ($_SESSION['INSTALL_PATH'].'inc/checkConnect.php' );
require_once ('vignettes_fcts.php');
require_once ('dates.php');

$mySceneList =  $_SESSION['user']->getUserScenes();
Users::purge_user_scenes($userID);

if (is_array($mySceneList) && count($mySceneList) > 0) :

	sort($mySceneList);

	$l = new Liste();
	$l->addFiltre(Scenes::HIDE, "=", 0);
	$l->addFiltre(Scenes::ARCHIVE, "=", 0);
	$l->getListe(TABLE_SCENES, '*', Scenes::UPDATE, 'DESC');			// Récup values de tous les shots qui ne sont ni caché ni archivés
	$allScenes = $l->simplifyList(Scenes::ID_SCENE);

	foreach ($allScenes as $idScene => $scene) :
		if (!in_array($idScene, $mySceneList))			// My scene ?
			continue;
		if (@$_SESSION['active_project_id'] && @$_SESSION['active_project_id'] != $scene[Scenes::ID_PROJECT])	// Active project ?
			continue;
		// Récup infos projet
		$p = new Projects($scene[Scenes::ID_PROJECT]);
		$title_project = $p->getProjectInfos(Projects::PROJECT_TITLE);
		// Récup vignettes
		$path_vignette = check_scene_vignette($scene[Scenes::ID_SCENE]);
		// Checks lock
		$iconLock = $iconWarning = "";
		if ($scene[Scenes::LOCK] == 1)
			$iconLock	 = '<span class="ui-icon ui-icon-locked"></span>';
		// Check deadline
		if (time() >= SQLdateConvert($scene[Scenes::DEADLINE], 'timeStamp'))
			$iconWarning = '<span class="ui-icon ui-icon-clock"></span>';
		?>
		<div class="inline gray-layer bordFin bordColInv2 ui-corner-all w9p margeTop5 pad3 doigt myScene" help="vos_scenes"
			 idProj="<?php echo $scene[Scenes::ID_PROJECT]; ?>"
			 idScene="<?php echo $scene[Scenes::ID_SCENE]; ?>"
			 title="<?php echo L_PROJECT.' \''.$title_project."'\n".$scene[Scenes::TITLE]; echo (($scene[Scenes::MASTER] == 0)) ? ' ('.mb_strtoupper(L_MASTER).')' : ' ('.L_DERIVATIVE.')'; ?>">

			<?php if ($scene[Scenes::MASTER] == 0): ?>
				<div class="fondError padV5 ui-corner-all" style="position:absolute; right:42px; margin-top: 2px;">
				   <span class="gros gras">M</span>
				</div>
			<?php endif; ?>

			<div class="center" style="position:relative; margin-bottom:-50px; padding-right:8px; height:50px;">
				 <div class="inline top" style="width:10px; padding-right:58px; margin-top:0px;"
					  title="<?php echo L_LOCKED; ?>">
					<?php echo $iconLock; ?>
				 </div>
				 <div class="inline top ui-state-error-text" style="width:10px; padding:0px; margin-top:32px;"
					  title="<?php echo L_OUTDATE_SINCE .' : '. $scene[Scenes::DEADLINE]; ?>">
					<?php echo $iconWarning; ?>
				 </div>
			</div>

			<img src="<?php echo $path_vignette; ?>" width="90" height="50" />

		</div>
		<?php
	endforeach;
else: ?>
	<br /><span class="ui-state-disabled"><?php echo L_NOTHING . ' ' . L_ASSIGNED_TO . ' ' . $_SESSION['user']->getUserInfos(Users::USERS_PSEUDO); ?>.</span>
<?php
endif; ?>

<script>
	$(function(){
		reCalcScrollMyMenu();
		$('.myScene').hover(
			function(){$(this).addClass('ui-state-active').removeClass('gray-layer'); },
			function(){$(this).removeClass('gray-layer ui-state-active').addClass('gray-layer');  }
		);

		var firstDept = $('#scenes_depts').find('.deptBtn').first().attr('label');
		if (!localStorage['lastDeptMyScene'])
			localStorage['lastDeptMyScene'] = firstDept;

		// Clic sur un MyShot
		$('#myMenu').off('click', '.myScene');
		$('#myMenu').on('click', '.myScene', function() {
			var idProj	= $(this).attr('idProj');
			scene_ID	= $(this).attr('idScene');
			localStorage['lastGroupDepts_'+idProj] = "scenes";
			localStorage['lastDept_'+idProj+'_GRP_scenes'] = localStorage['lastDeptMyScene'];
			localStorage['activeBtn_'+idProj]	= localStorage['lastTplMyScene'];
			localStorage['lastDept_'+idProj]	= localStorage['lastDeptMyScene'];
			localStorage['openScene_'+idProj]	= scene_ID;
			if (localStorage['activeContent'] == idProj) {
				$('#selectDeptsList').val('scenes').change();
				$('#scenes_depts').find('.deptBtn[label="'+localStorage['lastDeptMyScene']+'"]').click();
			}
			else
				openProjectTab (idProj);
		});

	});
</script>