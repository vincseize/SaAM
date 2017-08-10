<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once('dates.php');
	require_once('directories.php');
	require_once('vignettes_fcts.php');

	// @TODO : voir si $dept est utile pour dept structure
	$dept = 'structure';

	// OBLIGATOIRE, id du projet à charger
	if (isset($_POST['projectID']))
		$idProj = $_POST['projectID'];
	else die('Pas de projet à charger...');

	if (isset($_POST['shotID'])) {
		require_once('depts_shot_specific/dept_structure_shots.php');
		die();
	}

	// Chargement des infos du projet
	$p = new Projects($idProj);
	$p->recalc_project_progress();
	$projInfos		= $p->getProjectInfos();
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
//	$format			= $p->getFormat();
	$format			= '16/9';

	$seqsList = $p->getSequences();

	// récupère la liste des utilisateurs lead (et au dessus)
	$l = new Liste();
	$l->addFiltre('status', '>=', Users::USERS_STATUS_LEAD, 'AND');
	$l->addFiltre('my_projects', 'LIKE', '%'.$idProj.'%', 'AND');
	$l->getListe(TABLE_USERS, 'id,pseudo,status', 'pseudo', 'ASC');
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
			$autocompleteArtists .= '{"pseudo":"'.$user['pseudo'].'","id":"'.$user['id'].'"},';
	}
	$autocompleteArtists = substr($autocompleteArtists, 0, -1).']';

	// Si on veut charger la page avec une séquence déroulée (ouverte)
	if (isset($_POST['seqID'])) $idSeq = $_POST['seqID'];
?>

<script>
	var project_ID = '<?php echo $idProj; ?>'; var sequence_ID = '<?php echo @$idSeq; ?>';
	var addSeqWIP = false; var modSeqWIP = false; var modPlanWIP = false;
	var autocompleteLeads = <?php echo $autocompleteLeads; ?>;
	var autocompleteSups = <?php echo $autocompleteSups; ?>;
	var autocompleteArtists = <?php echo $autocompleteArtists; ?>;
	var departement	= "<?php echo $dept; ?>";

	$(function(){

		var view = stageHeight - 28;
		$('.stageContent').slimScroll({
			position: 'right',
			height: view+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});

		var projToShow = $(document).getUrlParam('proj');
		var seqToShow  = $(document).getUrlParam('seq');
		var shotToShow = $(document).getUrlParam('shot');
		var idS	= localStorage['openSeq_'+project_ID];

		// Si une séquence est choisie dans l'url GET (il faut que le proj soit défini aussi) :
		if (projToShow != null && seqToShow != null && shotToShow == null) {
			var params = { projectID: project_ID, sequenceID: seqToShow, dept: departement };
			$('li[idSeq="'+seqToShow+'"]').find('.seqLine').addClass('ui-state-focusFake').next('tr').load('modals/depts_shot_specific/dept_structure_project_shots.php', params).show();
		}
		// Si un plan est choisi dans l'url GET (il faut que le proj et la séquence soient définis aussi) :
		if (projToShow != null && seqToShow != null && shotToShow != null)
			loadPageContentModal('structure/structure_shots', {projectID: projToShow, sequenceID: seqToShow, shotID: shotToShow, dept: '00_structure', template: 'dept_structure'});
		// Si non, et si une séquence est enregistrée dans le localStorage
		else if (idS) {
			var params = { projectID: project_ID, sequenceID: idS, dept: departement, template: deptFile };
			$('li[idSeq="'+idS+'"]').find('.seqLine').addClass('ui-state-focusFake').next('tr').load('modals/depts_shot_specific/dept_structure_project_shots.php', params).show();
		}
		// Si non, et si une séquence est définie dans un POST
		else if (sequence_ID != '') {
			var params = { projectID: project_ID, sequenceID: sequence_ID };
			$('li[idSeq="'+sequence_ID+'"]').find('.seqLine').addClass('ui-state-focusFake').next('tr').load('modals/depts_shot_specific/dept_structure_project_shots.php', params).show();
		}

		$(".inputCal").datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true, onSelect: function(){$(this).keyup().blur();}});		// Calendrier sur focus d'input

		$('.bouton').button();
		$('.greyButton').addClass('ui-state-disabled doigt');
		if (topInfosHidden) hideTopInfosStage();

		// init de la scroll de description en haut
		$('#proj_Descr').slimScroll({
			position: 'right',
			height: '115px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});

	});

</script>
<script src="ajax/depts/dept_common.js"></script>
<script src="ajax/depts/dept_structure.js"></script>

<div class="stageContent fondHigh">

	<div class="topInfosStage">

		<div class="projTitle fondSemiTransp doigt" help="project_infos" id="proj_title" title="click to show/hide infos"><?php echo $projInfos[Projects::PROJECT_TITLE]; ?></div>

		<div class="vignetteTopInfos" help="project_infos">
			<img class="toHide" src="<?php echo $vignette; ?>" />
			<div class="leftText fondSemiTransp toHide colorMid" style="position: absolute; top:134px; width:270px;">
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
			<div class="progBar doigt" help="project_progressBar" percent="<?php echo $projInfos[Projects::PROJECT_PROGRESS]; ?>" title="Click to recalc progress" id="recalcProgress">
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

			<div class="ui-state-disabled toHide" id="proj_Team">
				<div class="inline top ui-icon ui-icon-person" title="<?php echo L_TEAM; ?>"></div>
				<div class="inline top w9p padH5" title="<?php echo L_TEAM; ?>"><?php echo $equipe; ?></div>
			</div>
		</div>

		<div class="fleche" id="detailsInfosRight">
			<div class="pad5 ui-state-error-text colorHard">
				<span class="inline mid ui-icon ui-icon-arrowthickstop-1-e" title="end until"></span>
				<span class="inline mid" title="<?php echo L_REM_TIME; ?>"><?php echo $daysLeftStr; ?></span>
			</div>

			<div class="margeTop5 padH5 padV5 colorMid toHide" id="proj_Descr" title="description du projet">
				<?php echo stripslashes($projInfos[Projects::PROJECT_DESCRIPTION]); ?>
				<br />
				<br />
			</div>
		</div>

		<div class="topActionStage" idProj="<?php echo $idProj; ?>">

		</div>

		<div class="bottomActionStage" idProj="<?php echo $idProj; ?>">
			<button class="bouton" title="<?php echo L_ADD.' '.L_SEQUENCE; ?>" id="btn_addSeq" help="add_sequence"><span class="ui-icon ui-icon-plusthick"></span></button>
		</div>

	</div>

	<div class="ui-widget-content noBorder">
		<table class="seqTableHead">
			<tr>
				<th class="w200 seqLabel"><?php echo L_TITLE; ?></th>
				<th class="w50"><?php echo L_LABEL; ?></th>
				<th style="width:56px;"><?php echo L_SHOTS; ?></th>
				<th class="w100"><?php echo L_PROGRESS; ?></th>
				<th class="w20"></th>
				<th class="w100"><?php echo L_DATE.' '.L_START; ?></th>
				<th class="w100"><?php echo L_DATE.' '.L_END; ?></th>
				<th>Lead(s) / Artist(s)</th>
				<th class="w180 center">Actions</th>
			</tr>
		</table>
	</div>

	<ul id="liste">

	<?php if (is_array($seqsList)) :
			foreach($seqsList as $seq) :
				$idSeq = $seq['id'];
				$nbShotsAct	= $p->getNbShots($idSeq, 'actifs');
				$nbShotsAll	= $p->getNbShots($idSeq, 'all');
				$hideSeq	= $seq[Sequences::SEQUENCE_HIDE];
				$lockSeq	= $seq[Sequences::SEQUENCE_LOCK];
				$btnHideClass = ($hideSeq == '1') ? 'greyButton' : '';
				$btnLockClass = ($lockSeq == '1') ? 'greyButton' : '';
				$btnLockIcon  = ($lockSeq == '1') ? 'ui-icon-locked' : 'ui-icon-unlocked';
				$archived	  = ($seq[Sequences::SEQUENCE_ARCHIVE] == '1') ? true : false ;

				$classDescr = ($seq['description'] == '') ? 'unshowable' : ''; ?>
				<div class="hide ui-state-focusFake ui-corner-all seqDescr <?php echo $classDescr; ?>" id="descr_<?php echo $idSeq; ?>"><?php echo $seq['description']; ?></div>

				<li class="ui-state-default" idSeq="<?php echo $idSeq; ?>" labelSeq="<?php echo $seq['label']; ?>">
					<table class="seqTable">
						<tr class="seqLine" help="modify_sequence">
							<td class="w200 seqTitle" style="padding-left:3px;"><?php echo $seq['title']; ?></td>
							<td class="w50 colorSoft"><?php echo $seq['label']; ?></td>
							<td  style="width:56px;"><span class="inline mid ui-icon ui-icon-copy"></span><b><?php echo $nbShotsAct; ?><span class="ui-state-disabled">/<?php echo $nbShotsAll; ?></span></b></td>
							<td class="w100">
								<div class="progBar miniProgBar" percent="<?php echo $seq['progress']; ?>"><span class="floatL marge10l margeTop1 petit colorMid"><?php echo $seq['progress']; ?>%</span></div>
							</td>
							<td class="w20"></td>
							<td class="w100">
								<span class="inline mid ui-icon ui-icon-clock" title="start date"></span>
								<span class="inline mid seqStart"><?php echo SQLdateConvert($seq['date']); ?></span>
							</td>
							<td class="w100 ui-state-error-text colorHard">
								<span class="inline mid ui-icon ui-icon-clock" title="deadline"></span>
								<span class="inline mid seqEnd"><?php echo SQLdateConvert($seq['deadline']); ?></span>
							</td>
							<td class="tdLead">
								<span class="inline mid ui-icon ui-icon-person" title="<?php echo L_LEAD; ?>"></span>
								<span class="inline mid seqLead" title="<?php echo L_LEAD; ?>"><?php echo $seq['lead']; ?></span>
							</td>
							<td class="w180 rightText padV5 nowrap nano actionBtns">
						<?php if ($archived) : ?>
								<button class="bouton seq_restore ui-state-highlight" title="restaurer"><span class="ui-icon ui-icon-refresh"></span></button>
						<?php else : ?>
								<button class="bouton seq_addShot" title="ajouter un plan" help="add_shot"><span class="ui-icon ui-icon-plus"></span></button>
								<button class="bouton seq_hide marge10l <?php echo $btnHideClass; ?>" title="montrer/cacher"><span class="ui-icon ui-icon-lightbulb"></span></button>
								<button class="bouton seq_lock <?php echo $btnLockClass; ?>" title="bloquer/débloquer"><span class="ui-icon <?php echo $btnLockIcon; ?>"></span></button>
								<button class="bouton seq_mod marge10l" title="modifier"><span class="ui-icon ui-icon-pencil"></span></button>
								<button class="bouton seq_del" title="archiver"><span class="ui-icon ui-icon-trash"></span></button>
						<?php endif; ?>
							</td>
						</tr>

						<tr class="fondSect3 colorPage detailSeq hide" help="modify_shots_from_sequence_window">
							<td class="nowrap pad10" colspan="9"><img src="gfx/ajax-loader.gif" /> please wait...</td>
							<!--CONTENU SHOTS APPELÉ EN AJAX-->
						</tr>
					</table>
				</li>
		<?php endforeach;
			else : ?><div class="ui-state-default pad5">No sequence.</div>
	<?php	endif; ?>
	</ul>

</div>

<!-- ------------------------------------------------------ MODELES -------------------------------------------------- -->

<!--AJOUT DE SÉQUENCE-->

<div class="hide" id="addSeq_modele">
	<li class="ui-state-active" id="newSeq">
		<table class="seqTable">
			<tr>
				<td class="w200" title="titre de la séquence"><input type="text" class="noBorder ui-corner-all pad3 w150 fondPage" onkeypress="return checkChar(event,null,true,null)" id="addSeq_title" /></td>
				<td class="w50"><?php echo $DEF_NOMENCLATURE_SEQ.sprintf('%03d', $nbSeqs+1); ?></td>
				<td style="width: 46px;" title="nombre de plans"><input type="text" class="noBorder ui-corner-all w20 pad3 fondPage" value="1" id="addSeq_nbShots" /></td>
				<td class="w100 ui-state-disabled"><div class="progBar miniProgBar" percent="0"><span class="floatL marge10l margeTop1 petit colorMid">0%</span></div></td>
				<td class="w20"></td>
				<td class="w100">
					<span class="inline mid ui-icon ui-icon-clock" title="<?php echo L_START; ?>"></span>
					<span class="inline mid">
						<input type="text" class="noBorder ui-corner-all pad3 fondPage" style="width: 70px;" value="<?php echo date('d/m/Y'); ?>" title="<?php echo L_START; ?>" id="addSeq_start" />
					</span>
				</td>
				<td class="w100 ui-state-error-text colorHard">
					<span class="inline mid ui-icon ui-icon-clock" title="deadline"></span>
					<span class="inline mid">
						<input type="text" class="noBorder ui-corner-all pad3 fondPage" style="width: 70px;" value="<?php echo SQLdateConvert($projInfos[Projects::PROJECT_DEADLINE]); ?>" title="deadline" id="addSeq_end" />
					</span>
				</td>
				<td>
					<span class="inline mid ui-icon ui-icon-person" title="<?php echo L_LEAD; ?>s"></span>
					<select class="mini noPad" title="<?php echo L_LEAD; ?>s" id="addSeq_leads"><?php
						foreach($leadsList as $lead) {
							echo '<option class="mini" value="'.$lead["pseudo"].'">'.$lead["pseudo"].'</option>';
						} ?>
					</select>
				</td>
				<td class="rightText nano">
					<button class="bouton marge10r ui-state-highlight" title="sauvegarder" id="addSeq_valid"><span class="ui-icon ui-icon-check"></span></button>
					<button class="bouton marge10r ui-state-error" title="annuler" id="addSeq_annul"><span class="ui-icon ui-icon-closethick"></span></button>
				</td>
			</tr>
		</table>
	</li>

</div>


<!--DIALOG D'AJOUT DE SHOTS-->

<div class="hide" id="addShot_dialog">
	<div class="">
		<?php echo L_ADD; ?> <input type="text" class="numericInput noBorder pad3 ui-corner-all fondSect3 center" size="2" id="nbShotAdd" /> <?php echo L_SHOT; ?><span id="addShotPluriel"></span>
	</div>
	<table class="margeTop10 petit">
		<tr>
			<th style="width:80px;"><?php echo L_LABEL; ?></th>
			<th class="w200"><?php echo L_TITLE; ?></th>
			<th class="w100"><span class="inline mid ui-icon ui-icon-clock"></span> <?php echo L_START; ?></th>
			<th class="w100 ui-state-error-text"><span class="inline mid ui-icon ui-icon-clock"></span> <span class="colorHard"><?php echo L_END; ?></span></th>
		</tr>
	</table>
	<table class="petit" id="newShotsList">

	</table>
</div>


<!--MODELE D'AJOUT DE SHOT (LINE)-->

<div class="hide">
	<table id="addShotModele">
		<tr class="shotLine">
			<td style="width:80px;" id="label"></td>
			<td id="aStitle">
				<input type="text" class="noBorder pad3 ui-corner-left w200 fondSect3 requiredField" title="<?php echo L_TITLE; ?>" onkeypress="return checkChar(event,null,true,null)" id="title" />
			</td>
			<td class="addShotDetail" id="aSdate">
				<input type="text" class="noBorder pad3 fondSect3 w100 inputDate requiredField" title="<?php echo L_START; ?>" id="" />
			</td>
			<td class="addShotDetail" id="aSdeadline">
				<input type="text" class="noBorder pad3 ui-corner-right fondSect3 w100 inputDeadline requiredField" title="<?php echo L_END; ?>" id="" />
			</td>
		</tr>
	</table>
</div>
