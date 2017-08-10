<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	if (isset($_POST['projectID']))
		$idProj = $_POST['projectID'];
	else die('Pas de projet à charger...');
	if (isset($_POST['sequenceID']))
		$idSeq  = $_POST['sequenceID'];
	else die('Séquence indéfinie...');
	if (isset($_POST['shotID']))
		$idShot = $_POST['shotID'];
	else die('Plan indéfini...');

	$dept = 'dectech';

	require_once('xml_fcts.php');

	// @TODO : remplacer par les vraies datas de format et nbre d'assets
	$format = '16/9';
	$nbAssets = '0';

try {
	$ACL = new ACL($_SESSION['user']);
	$adminDecTech = $ACL->check('SHOTS_ADMIN');
	// Chargement des infos du projet
	$proj = new Projects($idProj);
	$titleProj = $proj->getTitleProject();

	// Chargement des infos de la séquence
	$seq  = new Sequences($idSeq);
	$titleSeq = $seq->getSequenceInfos(Sequences::SEQUENCE_TITLE);
	$labelSeq = $seq->getSequenceInfos(Sequences::SEQUENCE_LABEL);

	// Chargement des infos du shot
	$shot = new Shots($idShot);
	$titleShot = $shot->getShotInfos(Shots::SHOT_TITLE);
	$labelShot = $shot->getShotInfos(Shots::SHOT_LABEL);
	if ($titleShot == '') $titleShot = $labelShot;
	$startShot		= SQLdateConvert($shot->getShotInfos(Shots::SHOT_DATE));
	$endShot		= SQLdateConvert($shot->getShotInfos(Shots::SHOT_DEADLINE));
	$dateEndTest	= SQLdateConvert($shot->getShotInfos(Shots::SHOT_DEADLINE), 'timeStamp');
	$classDeadLine	= ($dateEndTest < time()) ? "ui-state-error" : "";
	$nbDaysLeft		= (int)floor( - (time() - $dateEndTest) / (60*60*24) + 1);
	$daysLeftStr    = days_to_date($nbDaysLeft);

	$vignetteShot = check_shot_vignette_ext($idProj, $labelSeq, $labelShot, $dept);

	$dectechData = get_dectech_data($idProj, $titleProj, $labelSeq, $labelShot);

}
catch (Exception $e) {
	echo $e->getMessage();
	die();
}

?>

<script>
	var project_ID = '<?php echo $idProj; ?>'; var seq_ID = '<?php echo $idSeq; ?>'; var shot_ID = '<?php echo $idShot; ?>';
	localStorage['openSeq_'+project_ID]  = seq_ID;
	localStorage['openShot_'+project_ID] = shot_ID;
	var dir_shot   = '<?php echo $shot->getDirShot(); ?>';
	var departement	= "dectech";
	var modDecTechWIP = false;

	$(function(){
		$('.bouton').button();
		$('.greyButton').addClass('ui-state-disabled doigt');

		$('.dectechItem').each(function(i,e){
//			var cR = (i*20)+30; var cV = (i*15)+5; var cB = (i*8)+1;
//			$(e).css('background-color', 'rgba('+cR+', '+cV+', '+cB+', 0.3)');
			var hVal = (360/((i+2)/10));
			$(e).css('background-color', 'hsla('+hVal+', 90%, 10%, 0.3)')
		});

		$('.dectechItemHead').each(function(i,e){
//			var cR = (i*20)+30; var cV = (i*15)+5; var cB = (i*8)+1;
//			$(e).css('background-color', 'rgba('+cR+', '+cV+', '+cB+', 0.18)');
			var hVal = (360/((i+2)/10));
			$(e).css('background-color', 'hsla('+hVal+', 90%, 10%, 0.15)')
		});

	});
</script>

<script src="js/blueimp_uploader/jquery.iframe-transport.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload-fp.js"></script>
<script src="ajax/depts/dept_common.js"></script>
<script src="ajax/depts/dept_dectech_shot.js"></script>


<div class="topInfosStage">

	<div class="nano" id="backToProj" title="Retour à la vue du projet">
		<button class="bouton"><span class="inline mid ui-icon ui-icon-arrowthickstop-1-n"></span></button>
	</div>

	<div class="projTitle fondSemiTransp doigt" id="shot_title" title="click to show/hide infos">
		<span class="mini"><?php echo $titleSeq; ?> /</span> <?php echo $titleShot; ?>
	</div>

	<div class="vignetteTopInfos">
		<img class="toHide" src="<?php echo $vignetteShot; ?>" id="vignetteShot_img" />
		<input class="hide" type="file" name="files[]" id="vignetteShot_upload" />
		<div class="leftText fondSemiTransp toHide colorMid" style="position: absolute; top:134px;">
			<span class="inline mid marge10l ui-icon ui-icon-image" title="<?php echo L_ASSETS;?>"></span>
			<span class="inline mid gras colorHard marge10r"><?php echo $nbAssets; ?></span>
		</div>
	</div>

	<div class="fleche" id="detailsInfosCenter">
		<div class="progBar" percent="<?php echo $shot->getShotInfos(Shots::SHOT_PROGRESS); ?>" id="showShot_progress">
			<span class="floatL marge5 colorMid"><?php echo $labelShot; ?> : <?php echo $shot->getShotInfos(Shots::SHOT_PROGRESS); ?>%</span>
		</div>

		<div class="inline top colorHard toHide" id="shot_InfosPeople">
			<div>
				<span class="inline mid ui-icon ui-icon-lightbulb" title="<?php echo L_SUPERVISOR; ?>"></span> <span class="inline mid gras" title="<?php echo L_SUPERVISOR; ?>">
					<?php echo $shot->getShotInfos(Shots::SHOT_SUPERVISOR); ?>
				</span>
			</div>
			<div>
				<span class="inline mid ui-icon ui-icon-person" title="<?php echo L_LEAD; ?>"></span> <span class="inline mid gras" title="<?php echo L_LEAD; ?>">
					<?php echo $shot->getShotInfos(Shots::SHOT_LEAD); ?>
				</span>
			</div>
		</div>
		<div class="inline top marge30l colorHard toHide" id="shot_InfosDates">
			<div>
				<span class="inline mid ui-icon ui-icon-clock" title="<?php echo L_START; ?>"></span> <span class="inline mid gras" title="<?php echo L_START; ?>">
					<?php echo $startShot; ?>
				</span>
			</div>
			<div class="ui-state-error-text colorHard">
				<span class="inline mid ui-icon ui-icon-clock" title="<?php echo L_END; ?>"></span> <span class="inline mid gras <?php echo $classDeadLine; ?>" title="<?php echo L_END; ?>">
					<?php echo $endShot; ?>
				</span>
			</div>
		</div>

		<div class="toHide" id="shot_Team">
			<div class="inline top ui-state-disabled padH5" title="<?php echo L_TEAM; ?>" id="shot_teamList">
				<div class="inline top ui-state-disabled ui-icon ui-icon-person" title="<?php echo L_TEAM; ?>"></div>
				<?php echo $shot->getShotTeam(); ?>
			</div>
			<div class="margeTop10 colorMid leftText">
				<span class="inline mid ui-icon ui-icon-extlink" title="<?php echo L_FORMAT;?>"></span><?php echo $format; ?>
				<span class="inline mid marge10l ui-icon ui-icon-signal"  title="<?php echo L_FPS;?>"></span><?php echo $proj->getProjectInfos(Projects::PROJECT_FPS); ?> <?php echo L_FPS; ?>
				<br />
				<?php echo $shot->getNbFrames(); ?> <?php echo L_FRAMES; ?>
			</div>
		</div>

	</div>

	<div class="fleche" id="detailsInfosRight">

		<div class="colorBtnFake" id="deptNameBG">DECTECH</div>

		<div class="inline mid pad5 ui-state-error-text colorHard">
			<span class="inline mid ui-icon ui-icon-arrowthickstop-1-e" title="end until"></span>
			<span class="inline mid" title="<?php echo L_REM_TIME; ?>"><?php echo $daysLeftStr; ?></span>
		</div>

		<div class="margeTop5 padH5 colorMid toHide" id="shot_Descr" title="description du plan">
			<?php echo $shot->getShotInfos(Shots::SHOT_DESCRIPTION); ?>
		<br />
		<br />
		</div>

		<div class="topActionStage micro" idShot="<?php echo $idShot; ?>">
			<button class="bouton" id="refreshShot" title="rafraîchir la vue du plan"><span class="ui-icon ui-icon-arrowrefresh-1-s"></span></button>
		</div>

		<div class="bottomActionStage" idProj="<?php echo $idProj; ?>">

		</div>
	</div>

</div>


<div class="leftText" id="shotView" style="height:100%; margin-top:25px;">
	<div class="ui-widget-content noBorder">
		<table class="seqTableHead">
			<tr>
				<th class="center" style="width:270px; padding-top:0px;">Plan au sol <span class="inline bot ui-icon ui-icon-arrowthick-1-n"</th>
				<th></th>
				<th></th>
			</tr>
		</table>
	</div>


	<?php
	foreach($dectechData as $categName=>$categTech) :
		echo '<div class="ui-state-disabled giant pad5">'.strtoupper($categName).'</div>';
		ksort($categTech);
	?>
		<table class="tableListe w100p noMarge" style="margin-bottom: 20px;">
			<tr>
				<?php
				foreach($categTech as $posTech=>$itemTech) {
					$typeTech = preg_replace('/_/', ' ', $itemTech[0]);
					echo '<th class="dectechItemHead">
							<span class="inline bot ui-state-disabled">'.$typeTech.'</span>
							<div class="inline bot pico rightText noMarge marge10l">';
							if ($adminDecTech) echo
								'<button class="bouton modDectechItem" categTech="'.$categName.'" typeTech="'.$itemTech[0].'" posTech="'.$posTech.'" title="modifier le champ">
									<span class="ui-icon ui-icon-pencil"></span>
								</button>';
							echo '
							</div>
						</th>';
				}
				?>
			</tr>
			<tr>
				<?php
				foreach($categTech as $posTech=>$itemTech) {
					echo '<td class="dectechItem" id="item_'.$itemTech[0].'">'.$itemTech[1].'</td>';
				}
				?>
			</tr>
		</table>
	<?php endforeach; ?>
</div>




<!--<div  class="fondSect1" id="shotViewFooter">
	<?php // include('structure/structure_folders_shots.php'); ?>

	<div class="inline top tiers">
		<div class="pad10" id="listSoftwares">
			<i class="ui-state-disabled">Aucun logiciel défini pour ce plan.</i>
		</div>
	</div>

</div>-->