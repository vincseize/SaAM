<?php
if (@$_POST['dept'] == 'dectech') {
	require_once('../depts_shot_specific/dept_dectech_shots.php');
	die();
}
@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once('dates.php');
	require_once('directories.php');
	require_once('vignettes_fcts.php');

	//@TODO : check ACL pour l'accès

	// OBLIGATOIRE, departement, IDs du projet, de la sequence et du shot à charger
	if (isset($_POST['dept'])) {
		$dept = $_POST['dept'];
		if (preg_match('/_/', $_POST['dept'])) {
			$dptarr = explode('_', $_POST['dept']);
			$dept = $dptarr[1];
		}
	}
	else die('Département indéfini...');
	if (isset($_POST['template']))
		$deptFile = $_POST['template'];
	if ($dept == 'storyboard')
		$deptFile = 'dept_storyboard';

	if (isset($_POST['projectID']))
		$idProj = $_POST['projectID'];
	else die('Pas de projet à charger...');
	if (isset($_POST['sequenceID']))
		$idSeq  = $_POST['sequenceID'];
	else die('Séquence indéfinie...');
	if (isset($_POST['shotID']))
		$idShot = $_POST['shotID'];
	else die('Plan indéfini...');

	// @TODO : remplacer par les vraies datas de format et nbre d'assets
	$format = '16/9';
	$nbAssets = '0';

try {
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
	$lockedShot		= ($shot->isLocked()) ? 'true' : 'false';

	// Chargement des infos du shot, spécifiques au département
	try {
		$deptInfos = new Infos(TABLE_DEPTS);
		$deptInfos->loadInfos('label', $dept);
		$idDept = $deptInfos->getInfo('id');
		if (!$idDept) {
			$idDept = $_POST['deptID'];
			$deptInfos->loadInfos('id', $idDept);
		}
	}
	catch (Exception $e) { $idDept = $dept; }

	// Récupère tous les départements actifs du shot (pour griser les boutons depts)
	$shotAllDepts = $shot->getDeptsInfos();
	$jsDeptsArray = "[";
	foreach($shotAllDepts as $deptExist => $void)
		$jsDeptsArray .= "'$deptExist',";
	$jsDeptsArray = trim($jsDeptsArray, ',');
	$jsDeptsArray .= "]";

	// Récupère les infos du shot pour le département ACTUEL
	$shotDeptInfos	= $shot->getDeptsInfos($idDept);
	if ($shotDeptInfos != null && is_array($shotDeptInfos)) {
		foreach($shotDeptInfos as $key=>$val) {
			$$key = $val;
		}
	}
	if (!isset($fps)) $fps = $shot->getShotFPS();

	// recherche de la vignette pour ce dept.
	$vignetteShot = check_shot_vignette_ext($idProj, $labelSeq, $labelShot, $idDept);


	// récupère la liste des utilisateurs lead (et au dessus)
	$l = new Liste();
	$l->getListe(TABLE_USERS, 'id,pseudo,status', 'pseudo', 'ASC', 'status', '>=', Users::USERS_STATUS_LEAD);
	$leadsList = $l->simplifyList('id');
	$autocompleteLeads = $autocompleteSups = '[';
	foreach($leadsList as $user) {
		$autocompleteLeads .= '"'.$user['pseudo'].'",';
		if ($user['status'] >= Users::USERS_STATUS_SUPERVISOR)
			$autocompleteSups .= '"'.$user['pseudo'].'",';
	}
	$autocompleteLeads = substr($autocompleteLeads, 0, -1).']';
	$autocompleteSups = substr($autocompleteSups, 0, -1).']';

	// récupère la liste des utilisateurs qui sont assignés au projet (pour pouvoir les assigner au shot)
	$l->resetFiltre();
	$l->addFiltre('status', '>=', Users::USERS_STATUS_ARTIST, 'AND');
	$l->addFiltre('my_projects', 'LIKE', '%'.$idProj.'%');
	$l->getListe(TABLE_USERS, 'id,pseudo,status', 'pseudo', 'ASC');
	$artistsList = $l->simplifyList('id');
	$autocompleteArtists = '[';
	foreach($artistsList as $user) {
			$autocompleteArtists .= '"'.$user['pseudo'].'",';
	}
	$autocompleteArtists = substr($autocompleteArtists, 0, -1).']';
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
	var autocompleteLeads   = <?php echo $autocompleteLeads; ?>;
	var autocompleteSups    = <?php echo $autocompleteSups; ?>;
	var autocompleteArtists = <?php echo $autocompleteArtists; ?>;
	var departement	= "<?php echo $dept; ?>";
	var id_dept		= "<?php echo $idDept; ?>";
	var shotAllDepts = <?php echo $jsDeptsArray; ?>;
	var isLocked	= <?php echo $lockedShot; ?>;

	$(function(){

		$('.stageContent').height(stageHeight);

		setTimeout(function(){
			if (isLocked)
				$('#btn_addRetake, #btn_addMessage, .etapeChooser').addClass('ui-state-disabled');
			$('.deptBtn').removeClass('ui-state-active colorHard').removeAttr('active');
			$('#shots_depts').find('.deptBtn[label="<?php echo $dept; ?>"]').addClass('ui-state-active colorHard').attr('active', 'active');
			localStorage.setItem('activeBtn_<?php echo $idProj; ?>', '<?php echo ($deptFile == 'dept_storyboard') ? '20_storyboard' : $deptFile; ?>');
			localStorage.setItem('lastDept_<?php echo $idProj; ?>', '<?php echo $dept; ?>');
		}, 600);

		$('.bouton').button();
		$('.greyButton').addClass('ui-state-disabled doigt');

		if (departement == "storyboard")
			$('#middleShotHeader').html('<span class="marge10l activeShotCenter"><?php echo L_TAGS; ?></span>');

		var footH = $('#footerPage').height();
		$('#shotView').css({bottom: footH+140});
		$('#shotViewFooter').css({bottom: footH+'px'});
		// init de la scroll de description en haut
		$('#shot_Descr').slimScroll({
			position: 'right',
			height: '115px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});
		$('#shot_Team').slimScroll({
			position: 'right',
			height: '75px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});

		$('#btnEtapesShot').click(function(){
			$('#tagsContainer').hide(transition);
			$('#etapesContainer').show(transition);
			$('.seqTableHead').find('span:not(#numRetakeMessages)').removeClass('activeShotCenter').addClass('inactiveShotCenter');
			$(this).removeClass('inactiveShotCenter').addClass('activeShotCenter');
		});

		$('#btnTagsShot').click(function(){
			$('#etapesContainer').hide(transition);
			$('#tagsContainer').show(transition);
			$('.seqTableHead').find('span:not(#numRetakeMessages)').removeClass('activeShotCenter').addClass('inactiveShotCenter');
			$(this).removeClass('inactiveShotCenter').addClass('activeShotCenter');
		});

	});

</script>

<script src="js/blueimp_uploader/jquery.iframe-transport.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload-fp.js"></script>
<script src="ajax/depts/dept_common.js"></script>
<script src="ajax/depts/<?php echo $deptFile; ?>_shot.js"></script>

<div class="colorBtnFake" id="deptNameBG"><?php echo strtoupper($dept); ?></div>

<div class="stageContent">
	<div class="topInfosStage" help="shot_department_name">

		<div class="nano" id="backToProj" title="Back to sequences list" help="shot_back_to_sequences">
			<button class="bouton"><span class="inline mid ui-icon ui-icon-arrowthickstop-1-n"></span></button>
		</div>

		<div class="projTitle fondSemiTransp doigt" id="shot_title" title="click to show/hide infos" help="shot_vignette">
			<span class="mini"><?php echo $titleSeq; ?> /</span> <?php echo $titleShot; ?>
		</div>

		<div class="vignetteTopInfos" help="shot_vignette">
			<img class="toHide" src="<?php echo $vignetteShot; ?>" id="vignetteShot_img" />
			<input class="hide" type="file" name="files[]" id="vignetteShot_upload" />
			<div class="leftText fondSemiTransp toHide colorMid" style="position: absolute; top:134px;">
				<span class="inline mid marge10l ui-icon ui-icon-image" title="<?php echo L_ASSETS;?>"></span>
				<span class="inline mid gras colorHard marge10r"><?php echo $nbAssets; ?></span>
			</div>
		</div>

		<div class="fleche" id="detailsInfosCenter" help="shot_informations">
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
				<div class="inline top ui-state-disabled padH5" title="<?php echo L_TEAM.' : '.$shot->getShotTeam(); ?>" id="shot_teamList">
					<div class="inline top ui-state-disabled ui-icon ui-icon-person"></div>
					<?php echo $shot->getShotTeam(); ?>
				</div>

				<div class="margeTop10 colorMid leftText">
					<div class="inline mid w80" title="<?php echo L_FORMAT;?>">
						<span class="inline mid ui-icon ui-icon-extlink"></span><?php echo $format; ?>
					</div>
					<div class="inline mid">
						<span class="inline mid marge10l"><?php echo $shot->getNbFrames(); ?> <?php echo L_FRAMES; ?></span>
					</div>
					<br />
					<?php if (isset($fps)): ?>
					<div class="inline mid w80" title="<?php echo L_FPS;?>">
						<span class="inline mid ui-icon ui-icon-signal"></span><?php echo @$fps; ?> <?php echo L_FPS; ?>
					</div>
					<?php endif; ?>
					<?php if (isset($startF)): ?>
					<div class="inline mid" title="<?php echo L_RANGE;?>">
						<span class="inline mid marge10l">[ <?php echo @$startF; ?> -> <?php echo @$endF; ?> ]</span>
					</div>
					<?php endif; ?>
				</div>
			</div>

		</div>

		<div class="fleche" id="detailsInfosRight" help="shot_informations">

			<div class="inline mid pad5 ui-state-error-text colorHard">
				<span class="inline mid ui-icon ui-icon-arrowthickstop-1-e" title="end until"></span>
				<span class="inline mid" title="<?php echo L_REM_TIME; ?>"><?php echo $daysLeftStr; ?></span>
			</div>

			<div class="margeTop5 padH5 colorHard toHide" id="shot_Descr" title="<?php echo L_DESCRIPTION; ?>">
				<?php echo $shot->getShotInfos(Shots::SHOT_DESCRIPTION); ?>
				<br />
				<br />
			</div>
		</div>

		<div class="topActionStage" idShot="<?php echo $idShot; ?>" help="shot_action_buttons">
			<?php if ($shot->isLocked()) : ?>
				<div class="inline top big ui-state-error ui-corner-all noBG colorHard padV5 margeTop5 marge10r"><span class="inline mid ui-icon ui-icon-locked"></span> <span class="inline">Locked</span></div>
			<?php endif; ?>
			<button class="bouton showTasks" section="<?php echo Tasks::SECTION_SHOTS; ?>" entity="<?php echo $idShot; ?>" title="<?php echo L_TASKS; ?>"><span class="ui-icon ui-icon-bookmark"></span></button>
			<button class="bouton" id="refreshShot" title="Refresh shot's view"><span class="ui-icon ui-icon-arrowrefresh-1-s"></span></button>
			<?php if ($dept != 'storyboard'): ?>
			<button class="bouton marge10l" title="Modify department informations of this shot" id="btn_modShot"><span class="ui-icon ui-icon-pencil"></span></button>
			<?php endif; ?>
		</div>

		<div class="bottomActionStage" idProj="<?php echo $idProj; ?>">

		</div>
	</div>


	<div class="leftText" id="shotView">
		<div class="ui-widget-content noBorder">
			<table class="seqTableHead">
				<tr>
					<th class="activeShotCenter" style="width:270px;" idProj="<?php echo $idProj; ?>" help="shot_published">
						<div class="floatR marge10r ui-corner-all doigt" title="<?php echo L_ADD_RETAKE; ?>" id="btn_addRetake" help="shot_add_published">
							<span class="ui-icon ui-icon-plusthick"></span>
						</div>
						<div class="center" id="activeRetakeNumber"></div>
					</th>
					<th class="colorDiscret" id="middleShotInfos" idProj="<?php echo $idProj; ?>" help="shot_center">
						<div class="floatR marge10r ui-corner-all doigt" title="<?php echo L_ADD_MESSAGE; ?>" id="btn_addMessage" help="shot_messages">
							<span class="ui-icon ui-icon-mail-closed"></span>
						</div>
						<div class="inline mid" id="middleShotHeader">
							<span class="marge10l activeShotCenter" id="btnEtapesShot" help="shot_steps"><?php echo L_ETAPES; ?></span>
							<span class="marge10l"> | </span>
							<span class="marge10l inactiveShotCenter" id="btnTagsShot" help="shot_tags"><?php echo L_TAGS; ?></span>
						</div>
					</th>
					<th class="colorDiscret" style="width:50%;" help="shot_messages">
						<div class="floatR marge10r doigt showTasks" section="<?php echo Tasks::SECTION_SHOTS; ?>" entity="<?php echo $idShot; ?>" title="<?php echo L_TASKS; ?>">Tasks</div>
						<?php echo L_RETAKE_MESSAGES; ?> <span class="activeShotCenter" id="numRetakeMessages"></span>
					</th>
				</tr>
			</table>
		</div>
		<div class="shadowOut" id="shotViewLeft">
			<?php include('structure/structure_retakes.php'); ?>
		</div>


		<div id="shotViewCenter" help="shot_center">
			<?php include('depts_shot_specific/'.$deptFile.'_shot_center.php'); ?>
		</div>


		<div id="shotViewRight" help="shot_messages">
			<?php include('structure/structure_messages.php'); ?>
		</div>
	</div>


	<div class="fondSect1" id="shotViewFooter" help="shot_folders">
		<?php include('structure/structure_folders_shots.php'); ?>

	<!--	<div class="inline top tiers">
			<div class="pad10" id="listSoftwares">
				<i class="ui-state-disabled">Aucun logiciel défini pour ce plan.</i>
			</div>
		</div>-->

	</div>
</div>

	<?php include('depts_shot_specific/'.$deptFile.'_shot_modif.php'); ?>