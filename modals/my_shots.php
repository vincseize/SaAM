<?php
@session_start();

if (!isset($_SESSION["user"])) die('No user connected.');

$dontTouchSSID = true;

require_once ($_SESSION['INSTALL_PATH'].'inc/checkConnect.php' );
require_once ('vignettes_fcts.php');
require_once ('dates.php');

$myShotList =  $_SESSION['user']->getUserShots();
Users::purge_user_shots($userID);

if (is_array($myShotList) && count($myShotList) > 0) :

	sort($myShotList);

	$l = new Liste();
	$l->addFiltre(Shots::SHOT_HIDE, "=", 0);
	$l->addFiltre(Shots::SHOT_ARCHIVE, "=", 0);
	$l->getListe(TABLE_SHOTS, '*', Shots::SHOT_UPDATE, 'DESC');			// Récup values de tous les shots qui ne sont ni caché ni archivés
	$allShots = $l->simplifyList(Shots::SHOT_ID_SHOT);

	foreach ($allShots as $idShot => $shot) :
		if (!in_array($idShot, $myShotList))			// My shot ?
			continue;
		if (@$_SESSION['active_project_id'] && @$_SESSION['active_project_id'] != $shot[Shots::SHOT_ID_PROJECT])	// Active project ?
			continue;
		// Récup infos projet
		$p = new Projects($shot[Shots::SHOT_ID_PROJECT]);
		$title_project = $p->getProjectInfos(Projects::PROJECT_TITLE);
		// Récup infos sequence
		$ps = new Sequences($shot[Shots::SHOT_ID_SEQUENCE]);
		$title_sequence = $ps->getSequenceInfos(Sequences::SEQUENCE_TITLE);
		$label_sequence = $ps->getSequenceInfos(Sequences::SEQUENCE_LABEL);
		// Récup vignettes
		$path_vignette = check_shot_vignette_ext($shot[Shots::SHOT_ID_PROJECT], $label_sequence, $shot[Shots::SHOT_LABEL]);
		// Checks lock
		$iconLock = $iconWarning = "";
		if ($shot[Shots::SHOT_LOCK] == 1)
			$iconLock	 = '<span class="ui-icon ui-icon-locked"></span>';
		// Check deadline
		if (time() >= SQLdateConvert($shot[Shots::SHOT_DEADLINE], 'timeStamp'))
			$iconWarning = '<span class="ui-icon ui-icon-clock"></span>';
		?>
		<div class="inline gray-layer bordFin bordColInv2 ui-corner-all w9p margeTop5 pad3 doigt myShot" help="vos_plans"
			 idProj="<?php echo $shot[Shots::SHOT_ID_PROJECT]; ?>"
			 idSeq="<?php echo $shot[Shots::SHOT_ID_SEQUENCE]; ?>"
			 idShot="<?php echo $shot[Shots::SHOT_ID_SHOT]; ?>"
			 title="<?php echo L_PROJECT.' \''.$title_project."'\n".$title_sequence.' -> '.$shot[Shots::SHOT_TITLE]; ?>">

			<div class="center" style="position:relative; margin-bottom:-50px; padding-right:8px; height:50px;">
				 <div class="inline top" style="width:10px; padding-right:58px; margin-top:0px;"
					  title="<?php echo L_LOCKED; ?>">
					<?php echo $iconLock; ?>
				 </div>
				 <div class="inline top ui-state-error-text" style="width:10px; padding:0px; margin-top:32px;"
					  title="<?php echo L_OUTDATE_SINCE .' : '. $shot[Shots::SHOT_DEADLINE]; ?>">
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
		$('.myShot').hover(
			function(){$(this).addClass('ui-state-active').removeClass('gray-layer');},
			function(){$(this).removeClass('ui-state-active').addClass('gray-layer');}
		);

		var firstDept = $('#shots_depts').find('.deptBtn').first().attr('label');
		if (!localStorage['lastDeptMyShot'])
			localStorage['lastDeptMyShot'] = firstDept;

		// Clic sur un MyShot
		$('#myMenu').off('click', '.myShot');
		$('#myMenu').on('click', '.myShot', function() {
			var idProj	= $(this).attr('idProj');
			seq_ID		= $(this).attr('idSeq');
			shot_ID		= $(this).attr('idShot');
			localStorage['lastGroupDepts_'+idProj] = "shots";
			localStorage['lastDept_'+idProj+'_GRP_shots'] = localStorage['lastDeptMyShot'];
			localStorage['activeBtn_'+idProj]	= localStorage['lastTplMyShot'];
			localStorage['lastDept_'+idProj]	= localStorage['lastDeptMyShot'];
			localStorage['openSeq_'+idProj]	= seq_ID;
			localStorage['openShot_'+idProj]	= shot_ID;
			if (localStorage['activeContent'] == idProj) {
				$('#selectDeptsList').val('shots').change();
				$('#shots_depts').find('.deptBtn[label="'+localStorage['lastDeptMyShot']+'"]').click();
			}
			else
				openProjectTab (idProj);
		});

	});
</script>