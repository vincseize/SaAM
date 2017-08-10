<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once('dates.php');
	require_once('vignettes_fcts.php');
	require_once('url_fcts.php');

	// OBLIGATOIRE, id du projet à charger
	if (isset($_POST['projectID']))
		$idProj = $_POST['projectID'];
	else die('Pas de projet à charger...');

	// Chargement des infos du projet
	$p = new Projects($idProj);
	$projInfos		= $p->getProjectInfos();
	$vignette		= check_proj_vignette_ext($idProj, $projInfos[Projects::PROJECT_TITLE]);
	$dateStart		= SQLdateConvert($projInfos[Projects::PROJECT_DATE]);
	$dateEnd		= SQLdateConvert($projInfos[Projects::PROJECT_DEADLINE]);
	$dateEndTest	= SQLdateConvert($projInfos[Projects::PROJECT_DEADLINE], 'timeStamp');
	$classDeadLine	= ($dateEndTest < time()) ? "ui-state-error" : "";
	$nbDaysLeft		= floor( - (time() - $dateEndTest) / (60*60*24));
	$plurielJours	= ($nbDaysLeft > 1) ? "s" : "";
	$daysLeftStr	= ($nbDaysLeft < 0) ? "deadline dépassée de ".abs($nbDaysLeft)." jour$plurielJours" : "reste $nbDaysLeft jour$plurielJours";
	$projectDir		= $p->getDirProject();

	$fileScenario = $_SESSION['INSTALL_PATH'].FOLDER_DATA_PROJ.$projectDir.'/scenario.htm';
	$urlScenario  = FOLDER_DATA_PROJ.$projectDir.'/scenario.htm';
	if (file_exists($fileScenario)) {
		$scenarioContent = file_get_contents($fileScenario);
		$scenarioContent .= '<br /><br /><br /><br />';
	}
//	else $scenarioContent = '<b style="color:red;">'.$fileScenario.'</b> :: <b>not found !</b>';
?>

<link rel="stylesheet" type="text/css" href="css/jquery.cleditor.css">
<script type="text/javascript" src="js/jquery.cleditor.min.js"></script>

<script>
	var idProj = <?php echo $idProj; ?>;
	$(function(){
		$('.bouton').button();

		// WYSIWYG editor
		var editor = $('#writer').cleditor({
			width: '100%',
			height: 500
		})[0];

		setTimeout(function(){editor.disable(false).refresh();}, 500);

	});
</script>

<script src="ajax/depts/dept_common.js"></script>
<script src="ajax/depts/dept_scenario.js"></script>


<div class="topInfosStage">

	<div class="projTitle fondSemiTransp doigt" help="project_infos" id="proj_title" title="click to show/hide infos"><?php echo $projInfos[Projects::PROJECT_TITLE]; ?></div>

	<div class="vignetteTopInfos" help="project_infos">
		<img class="toHide" src="<?php echo $vignette; ?>" />
	</div>

	<div class="fleche" help="project_infos" id="detailsInfosCenter">
		<div class="progBar" help="project_progressBar" percent="<?php echo $projInfos[Projects::PROJECT_PROGRESS]; ?>">
			<span class="floatL marge5 colorMid"><?php echo L_PROJECT;?> : <?php echo $projInfos[Projects::PROJECT_PROGRESS]; ?>%</span>
		</div>

		<div class="inline top colorHard toHide" id="proj_InfosPeople">
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
		<div class="inline top marge30l colorHard toHide" id="proj_InfosDates">
			<div>
				<span class="inline mid ui-icon ui-icon-clock" title="<?php echo L_START; ?>"></span>
				<span class="inline mid gras" title="<?php echo L_START; ?>"><?php echo $dateStart; ?></span>
			</div>
			<div class="ui-state-error-text colorHard">
				<span class="inline mid ui-icon ui-icon-clock" title="<?php echo L_END; ?>"></span>
				<span class="inline mid gras <?php echo $classDeadLine; ?>" title="<?php echo L_END; ?>"><?php echo $dateEnd; ?></span>
			</div>
		</div>

	</div>

	<div class="fleche" id="detailsInfosRight">

		<div class="colorSoft" id="deptNameBG">SCENARIO</div>

		<div class="pad5 ui-state-error-text colorHard">
			<span class="inline mid ui-icon ui-icon-arrowthickstop-1-e" title="end until"></span>
			<span class="inline mid" title="<?php echo L_REM_TIME; ?>"><?php echo $daysLeftStr; ?></span>
		</div>

		<div class="margeTop5 padH5 colorMid toHide" id="proj_Descr" title="description du projet">
			<?php echo stripslashes($projInfos[Projects::PROJECT_DESCRIPTION]); ?>
			<br />
			<br />
		</div>
	</div>

	<div class="topActionStage" idProj="<?php echo $idProj; ?>">

	</div>

	<div class="bottomActionStage" idProj="<?php echo $idProj; ?>">

	</div>


</div>


<div class="stageContent">

	<div class="ui-widget-content noBorder">
		<table class="seqTableHead">
			<tr>
				<th class="center" style="width:270px;">Scenario</th>
				<th></th>
				<th style="width:50%;"></th>
			</tr>
		</table>
	</div>

	<input type="hidden" id="scenarPath" value="<?php echo $fileScenario; ?>" />

	<div class="inline top" style="width:65%;">
		<?php if ($_SESSION['user']->isSupervisor()) : ?>
		<textarea name="writer" id="writer"><?php echo @$scenarioContent; ?></textarea>
		<?php else:
			echo '<div class="w100p pad5 gros fondBlanc" style="height: 500px; overflow:auto;">'.@$scenarioContent.'</div>';
		endif; ?>

	</div>
	<div class="inline top pad5" id="editorMenu">
		<?php if (($idProj == 1 && $_SESSION['user']->isDev()) || ($idProj != 1 && $_SESSION['user']->isSupervisor())) : ?>
			<button class="bouton" id="saveScenario">SAUVEGARDER</button>
			<button class="bouton" id="undoScenario">ANNULER</button>
			<button class="bouton" id="deleteScenario">TOUT EFFACER</button>
			<br /><br />
		<?php endif; ?>
			<a href="fct/downloader.php?type=scenario&file=<?php echo $urlScenario ?>" target="_blank"><button class="bouton" id="downloadScenario">TÉLÉCHARGER</button></a>
	</div>
	<br /><br /><br />

</div>
