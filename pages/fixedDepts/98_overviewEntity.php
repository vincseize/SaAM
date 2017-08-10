<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once('directories.php');

// OBLIGATOIRE, id du projet à charger
if (isset($_POST['projectID']))
	$idProj = $_POST['projectID'];
else die('Pas de projet à charger...');

require_once('dates.php');

try {
	$p = new Projects($idProj);
    $projInfos = $p->getProjectInfos();
    $titleProj = $projInfos[Projects::PROJECT_TITLE];
	$vignette		= check_proj_vignette_ext($idProj, $projInfos[Projects::PROJECT_TITLE]);
	$dateStart		= SQLdateConvert($projInfos[Projects::PROJECT_DATE]);
	$dateEnd		= SQLdateConvert($projInfos[Projects::PROJECT_DEADLINE]);
	$dateEndTest	= SQLdateConvert($projInfos[Projects::PROJECT_DEADLINE], 'timeStamp');
	$classDeadLine	= ($dateEndTest < time()) ? "ui-state-error" : "";
	$nbDaysLeft		= (int)floor( - (time() - $dateEndTest) / (60*60*24));
	$daysLeftStr    = days_to_date($nbDaysLeft);
	$equipe			= $p->getEquipe('str');
	if ($idProj == 1)
		$equipe = 'Demo, woman1, man1, man2, woman2, woman3, man3';
	$nbSeqs			= $p->getNbSequences();
	$nbShots		= $p->getNbShots();
	$format			= '16/9';

	$assets = $p->getAssets();
	$dl = new Liste();
	$dl->getListe(TABLE_DEPTS, 'id,label,etapes', 'position', 'ASC', 'type', '=', $_POST['typeArbo']);
	$depts = $dl->simplifyList('label');
}
catch (Exception $e) {
	echo 'ERREUR : '.$e->getMessage();
	die();
}
?>

<script type="text/javascript" src="js/jquery.metadata.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
<script>
	var project_ID	= <?php echo $idProj ?>;
	var nbAssets	= <?php echo (is_array($assets)) ? count($assets) : 0; ?>;

	$(function(){
		if(nbAssets == 0)
			$('#assets_depts').find('.deptBtn')[1].click();

		$('.bouton').button();

		// toggle du topInfos
		$(document).off('click', '.projTitle');
		$(document).on('click', '.projTitle', function(){
			if (topInfosHidden == false) hideTopInfosStage(transition);
			else showTopInfosStage(transition);
		});

		var maxListHeight = stageHeight - 60;
		$('.stageContent').height(maxListHeight);
		$('#assetsList').slimScroll({
			position: 'right',
			height: (maxListHeight)+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});

		$('#assetsList .tableListe').tablesorter({textExtraction: sortByAttr, widthFixed: false});
		$('thead th').click(function(){
			$('thead th').removeClass('colorHard');
			$(this).addClass('colorHard');
		});

		$('.OVopenAsset').click(function(){
			var dept = $(this).attr('dept');
			localStorage['lastDept_'+project_ID+'_GRP_assets'] = dept;
			localStorage['openAsset_'+idProj]		= $(this).parents('tr').attr('nameAsset');
			localStorage['openAssetPath_'+idProj]	= $(this).parents('tr').attr('pathAsset');
			$('#assets_depts').find('.deptBtn[label="'+dept+'"]').click();
		});
	});

	function sortByAttr(node) {
		var realVal = $(node).attr('realVal');
		return realVal;
	}
</script>
<!--<script src="ajax/depts/dept_overviewEntity.js"></script>-->

<div class="colorSoft hide" id="deptNameBG"><?php echo mb_strtoupper($_POST['typeArbo']); ?></div>

<div class="topInfosStage" style="height: 34px;">

	<div class="projTitle fondSemiTransp doigt noBG" help="project_infos" id="proj_title" title="click to show/hide infos"><?php echo $projInfos[Projects::PROJECT_TITLE]; ?></div>

	<div class="vignetteTopInfos" help="project_infos">
		<img class="toHide hide" src="<?php echo $vignette; ?>" />
		<div class="leftText fondSemiTransp toHide hide colorMid" style="position: absolute; top:134px; width:270px;">
			<span class="inline mid marge10l ui-icon ui-icon-video" title="<?php echo L_SEQUENCES;?>"></span>
			<span class="inline mid gras colorHard"><?php echo $nbSeqs; ?></span>
			<span class="inline mid marge10l ui-icon ui-icon-copy" title="<?php echo L_SHOTS;?>"></span>
			<span class="inline mid gras colorHard marge10r"><?php echo $nbShots; ?></span>
			<span class="inline mid marge10l ui-icon ui-icon-signal" title="<?php echo L_FPS;?>"></span>
			<span class="inline mid gras colorHard"><?php echo $projInfos[Projects::PROJECT_FPS].' fps'; ?></span>
			<span class="inline mid marge10l ui-icon ui-icon-extlink" title="<?php echo L_FORMAT;?>"></span>
			<span class="inline mid gras colorHard marge10r"><?php echo $format; ?></span>
		</div>
	</div>

	<div class="fleche" help="project_infos" id="detailsInfosCenter">
		<div class="progBar" help="project_progressBar" percent="<?php echo $projInfos[Projects::PROJECT_PROGRESS]; ?>">
			<span class="floatL marge5 colorMid"><?php echo L_PROJECT;?> : <?php echo $projInfos[Projects::PROJECT_PROGRESS]; ?>%</span>
		</div>

		<div class="inline top colorHard toHide" style="display:none;" id="proj_InfosPeople">
			<div>
				<span class="inline mid ui-icon ui-icon-home" title="production"></span>
				<span class="inline mid gras" title="production"><?php echo $projInfos[Projects::PROJECT_COMPANY]; ?></span>
			</div>
			<div>
				<span class="inline mid ui-icon ui-icon-volume-off" title="réalisateur"></span>
				<span class="inline mid gras" title="réalisateur"><?php echo $projInfos[Projects::PROJECT_DIRECTOR]; ?></span>
			</div>
			<div>
				<span class="inline mid ui-icon ui-icon-person" title="<?php echo L_SUPERVISOR; ?>"></span>
				<span class="inline mid gras" title="<?php echo L_SUPERVISOR; ?>"><?php echo $projInfos[Projects::PROJECT_SUPERVISOR]; ?></span>
			</div>
		</div>
		<div class="inline top marge30l colorHard toHide" style="display:none;" id="proj_InfosDates">
			<div>
				<span class="inline mid ui-icon ui-icon-clock" title="<?php echo L_START; ?>"></span>
				<span class="inline mid gras" title="<?php echo L_START; ?>"><?php echo $dateStart; ?></span>
			</div>
			<div class="ui-state-error-text colorHard">
				<span class="inline mid ui-icon ui-icon-clock" title="<?php echo L_END; ?>"></span>
				<span class="inline mid gras <?php echo $classDeadLine; ?>" title="<?php echo L_END; ?>"><?php echo $dateEnd; ?></span>
			</div>
		</div>

		<div class="ui-state-disabled toHide hide" id="proj_Team">
			<div class="inline top ui-icon ui-icon-person" title="<?php echo L_TEAM; ?>"></div>
			<div class="inline top w9p padH5" title="<?php echo L_TEAM; ?>"><?php echo $equipe; ?></div>
		</div>
	</div>

	<div class="fleche" id="detailsInfosRight">
		<div class="pad5 ui-state-error-text colorHard">
			<span class="inline mid ui-icon ui-icon-arrowthickstop-1-e" title="end until"></span>
			<span class="inline mid" title="<?php echo L_REM_TIME; ?>"><?php echo $daysLeftStr; ?></span>
		</div>

		<div class="margeTop5 padH5 padV5 colorHard toHide hide" id="proj_Descr" title="description du projet">
			<?php echo stripslashes($projInfos[Projects::PROJECT_DESCRIPTION]); ?>
			<br />
			<br />
		</div>
	</div>

	<div class="topActionStage" idProj="<?php echo $idProj; ?>">
	</div>

	<div class="bottomActionStage nano" style="bottom:7px;" idProj="<?php echo $idProj; ?>">
	</div>

</div>

<div class="stageContent noPad">

	<div class="noBorder" id="assetsList">
		<table class="tableListe w100p">
			<thead class="colorDiscret fondSect1 doigt">
				<tr>
					<th rowspan="2" class="bot"><?php echo L_NAME; ?></th>
					<th rowspan="2" class="bot w100"><?php echo L_START; ?></th>
					<th rowspan="2" class="bot w100"><?php echo L_END; ?></th>
					<th rowspan="2" class="bot w150"><?php echo L_MODIFICATION; ?></th>
					<th rowspan="2" class="bot w200"><?php echo L_TEAM; ?></th>
					<th rowspan="2" class="bot w100"><?php echo L_ASSET_HUNG_BY; ?></th>
					<th colspan="<?php echo count($depts); ?>" class="center noPad"><?php echo L_STATUS; ?></th>
				</tr>
				<tr><?php
					foreach($depts as $d): ?>
						<th class="w80"><?php echo $d['label']; ?></th><?php
					endforeach; ?>
				</tr>
			</thead>
			<tbody><?php
				if (is_array($assets)):
					$deptsNames = array_keys($depts);
//					for ($i=0;$i<20;$i++):
					foreach($assets as $asset):
						$a = new Assets((int)$idProj, $asset[Assets::ASSET_NAME], $asset[Assets::ASSET_PATH_REL]);
						$asTeamNames = $a->getTeamAsset('str');
						$categ = $a->getCategory();
						$dateEndTestA	= SQLdateConvert($asset[Assets::ASSET_DEADLINE], 'timeStamp');
						$classDeadLineA	= ($dateEndTestA < time()) ? "colorErreur" : "colorDiscret3";
						$handler = $a->getHandler(Users::USERS_PSEUDO);
						?>
					<tr nameAsset="<?php echo $asset[Assets::ASSET_NAME]; ?>" pathAsset="<?php echo $asset[Assets::ASSET_PATH_REL]; ?>">
						<td style="padding: 6px 5px;" class="colorBtnFake doigt gras OVopenAsset" dept="<?php echo $deptsNames[0]; ?>" title="<?php echo $categ."\npath: ".$asset[Assets::ASSET_PATH_REL]; ?>" realVal="<?php echo $asset[Assets::ASSET_NAME]; ?>">
							<?php echo $asset[Assets::ASSET_NAME]; ?>
						</td>
						<td class="ui-state-disabled" realVal="<?php echo SQLdateConvert($asset[Assets::ASSET_DATE], 'timeStamp'); ?>">
							<?php echo SQLdateConvert($asset[Assets::ASSET_DATE]); ?>
						</td>
						<td class="<?php echo $classDeadLineA; ?> gras" realVal="<?php echo SQLdateConvert($asset[Assets::ASSET_DEADLINE], 'timeStamp'); ?>">
							<?php echo SQLdateConvert($asset[Assets::ASSET_DEADLINE]); ?>
						</td>
						<td class="colorDiscret1" realVal="<?php echo SQLdateConvert($asset[Assets::ASSET_UPDATE], 'timeStamp'); ?>">
							<?php echo SQLdateConvert($asset[Assets::ASSET_UPDATE]); ?> <small>(<?php echo Users::getUserName((int)$asset[Assets::ASSET_UPDATED_BY]) ?>)</small>
						</td>
						<td realVal="<?php echo trim($asTeamNames, ', '); ?>"><?php echo trim($asTeamNames, ', '); ?></td>
						<td class="colorDiscret2 gras" realVal="<?php echo $handler; ?>"><?php echo $handler; ?></td><?php
						$di = $a->getDeptsInfos((int)$idProj);
						foreach($depts as $d):
							$a->getDirRetakes((int)$idProj, (int)$d['id']);
							$pubs = count($a->getRetakesList());
//							echo '<pre>'; var_dump($pubs); echo '</pre>';
							$steps = json_decode($d['etapes']);
							$step = (isset($di[$d['label']]['assetStep'])) ? (string)$di[$d['label']]['assetStep'] : 'zzznoStep';
							switch ($step) {
								case 'zzznoStep':
									$classDpt = 'colorDiscret'; $stepTxt = '---'; break;
								case '0':
									$classDpt = 'colorDiscret2'; $stepTxt = mb_strtoupper($steps[(int)$step]); break;
								case '99':
									$classDpt = 'colorDiscret3';  $stepTxt = 'DONE'; break;
								default:
									$classDpt = ''; $stepTxt = $steps[(int)$step];
							} ?>
							<td class="<?php echo $classDpt; ?> doigt OVopenAsset" title="click to open asset in this department" dept="<?php echo $d['label']; ?>" realVal="<?php echo (string)$step ?>">
								<?php echo $stepTxt; echo ($pubs > 0) ? " <sup style='font-size:0.7em;' title='published'>($pubs)</sup>" : ''; ?>
							</td>
							<?php
						endforeach; ?>
					</tr><?php
					endforeach;
//					endfor;
				else: ?>
					<tr><td style="padding: 6px 5px;" class="ui-state-disabled" colspan="7"><?php echo L_NOTHING.' '.L_ASSET; ?></td></tr>
					<?php
				endif; ?>
			</tbody>
		</table>
	</div>
</div>