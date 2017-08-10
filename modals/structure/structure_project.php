<?php
	require_once('dates.php');
	require_once('directories.php');
	require_once('vignettes_fcts.php');

	// IMPORTANT !!!!!!!!!!!
	if (!isset($dept) || empty($dept) || $dept == '')
		die('department undefined !');
	// !!!!!!!!!!!!!!!!!!!!!

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

	// Si on veut charger la page avec une séquence déroulée (ouverte)
	if (isset($_POST['seqID'])) $idSeq = $_POST['seqID'];
?>

<script>
	var project_ID  = '<?php echo $idProj; ?>'; var sequence_ID = '<?php echo @$idSeq; ?>';
	var departement	= "<?php echo $dept; ?>";
	var deptID		= "<?php echo $_POST['deptID']; ?>";
	var deptFile	= "<?php echo $deptFile; ?>";

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
		var idSeq	= localStorage['openSeq_'+project_ID];
		var idShot	= localStorage['openShot_'+project_ID];

		// Si une séquence est définie dans l'URL GET (il faut que le proj soit défini aussi) :
		if (projToShow != null && seqToShow != null) {
			// Si un plan est défini :
			if (shotToShow != null) {
				var params = { projectID: projToShow, sequenceID: seqToShow, shotID: shotToShow, dept: departement, deptID: deptID, template: deptFile };
				loadPageContentModal('structure/structure_shots', params);
				history.replaceState({}, 'SaAM', 'index.php');
			}
			// Sinon on déroule la séquence
			else {
				var params = { projectID: project_ID, sequenceID: seqToShow, dept: departement, deptID: deptID, template: deptFile };
				$('li[idSeq="'+seqToShow+'"]').find('.seqLine').addClass('ui-state-focusFake').next('tr').load('modals/structure/structure_project_shots.php', params).show();
			}
		}
		// Si non, et si une séquence ou un shot sont définis dans le localStorage
		else if (idSeq || idShot) {
			// Si un shot est défini, on l'ouvre
			if (idShot) {
				var params = { projectID: project_ID, sequenceID: idSeq, shotID: idShot, dept: departement, deptID: deptID, template: deptFile };
				loadPageContentModal('structure/structure_shots', params);
			}
			// Sinon, on déroule la séquence
			else {
				var params = { projectID: project_ID, sequenceID: idSeq, dept: departement, deptID: deptID, template: deptFile };
				$('li[idSeq="'+idSeq+'"]').find('.seqLine').addClass('ui-state-focusFake').next('tr').load('modals/structure/structure_project_shots.php', params).show();
			}
		}
		// Si non, et si une séquence est définie dans un POST
		else if (sequence_ID != '') {
			var params = { projectID: project_ID, sequenceID: sequence_ID, dept: departement, deptID: deptID, template: deptFile };
			$('li[idSeq="'+sequence_ID+'"]').find('.seqLine').addClass('ui-state-focusFake').next('tr').load('modals/structure/structure_project_shots.php', params).show();
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

		// Déroulage des infos des séquences
		$('.seqTable td:not(.nowrap)').click(function(){
			if (!$(this).parent().hasClass('ui-state-focusFake')) {
				$('tr').removeClass('ui-state-focusFake');
				$('.detailSeq').hide();
				var idSeq = $(this).parents('li').attr('idSeq');
				var params = { projectID: project_ID, sequenceID: idSeq, dept: departement, deptID: deptID };
				$(this).parent().addClass('ui-state-focusFake').next('tr').load('modals/structure/structure_project_shots.php', params).show();
				localStorage['openSeq_'+project_ID] = idSeq;
			}
			else {
				$('tr').removeClass('ui-state-focusFake');
				$('.detailSeq').hide();
				localStorage.removeItem('openSeq_'+project_ID);
			}
		});

	});

</script>

<script src="ajax/depts/dept_common.js"></script>
<script src="ajax/depts/dept_<?php echo $dept; ?>.js"></script>

<div class="stageContent">
	<div class="colorSoft" id="deptNameBG"><?php echo strtoupper($dept); ?></div>

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

			<div class="margeTop5 padH5 padV5 colorHard toHide" id="proj_Descr" title="description du projet">
				<?php echo stripslashes($projInfos[Projects::PROJECT_DESCRIPTION]); ?>
				<br />
				<br />
			</div>
		</div>

		<div class="topActionStage" idProj="<?php echo $idProj; ?>">
			<?php if ($dept == 'storyboard' || $dept == 'dectech'): ?>
				<button class="bouton marge10l" title="Make and display <?php echo $dept; ?> global project's contact-sheet (new window)" id="global_contactSheet"><span class="ui-icon ui-icon-clipboard"></span></button>
			<?php endif; ?>
		</div>

		<div class="bottomActionStage" idProj="<?php echo $idProj; ?>">
		</div>

	</div>


	<div class="ui-widget-content noBorder">
		<table class="seqTableHead">
			<tr>
				<th class="w200 seqLabel">Titre de séquence</th>
				<th class="w50">Label</th>
				<th class="w50"></th>
				<th class="w100">Progress</th>
				<th class="w20"></th>
				<th class="w100">Date debut</th>
				<th class="w100">Date fin</th>
				<th>Lead(s) / Artist(s)</th>
				<th class="w180 center">Actions</th>
			</tr>
		</table>
	</div>

	<ul id="liste">

		<?php // @TODO : filtrage ACL sur shot depts, own sequences
		if (is_array($seqsList)) :
			foreach($seqsList as $seq) :
				if ($seq['hide'] == 1 || $seq['archive'] == 1) continue;
				$idSeq	 = $seq['id'];
				$nbShots = $p->getNbShots($idSeq, 'actifs');
				$classDescr = '';
				if ($seq['description'] == '') $classDescr = 'unshowable'; ?>

				<div class="hide ui-state-focusFake ui-corner-all seqDescr <?php echo $classDescr; ?>" id="descr_<?php echo $idSeq; ?>"><?php echo $seq['description']; ?></div>

				<li class="ui-state-default" idSeq="<?php echo $idSeq; ?>" labelSeq="<?php echo $seq['label']; ?>">
					<table class="seqTable">
						<tr class="seqLine">
							<td class="w200 seqTitle" style="padding-left:3px;"><?php echo $seq['title']; ?></td>
							<td class="w50 colorSoft"><?php echo $seq['label']; ?></td>
							<td class="w50"><span class="inline mid ui-icon ui-icon-copy"></span><b><?php echo $nbShots; ?></b></td>
							<td class="w100">
								<div class="progBar miniProgBar" percent="<?php echo $seq['progress']; ?>">
									<span class="floatL marge10l margeTop1 petit colorMid"><?php echo $seq['progress']; ?>%</span>
								</div>
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
							<td class="w180 center padV5 nowrap nano actionBtns">
								<?php if ($dept == 'storyboard' || $dept == 'dectech') : ?>
									<button class="bouton seq_contactSheet" title="Display this sequence's contact-sheet">
										<span class="ui-icon ui-icon-clipboard"></span>
									</button>
									<div class="bouton ui-state-activeFake seq_contactSheetActive" title="Include / exclude this sequence for global contact-sheet">
										<span class="ui-icon ui-icon-lightbulb"></span>
									</div>
								<?php else: ?>
									<button class="bouton seq_SB_contactSheet" title="Display this sequence's STORYBOARD contact-sheet">
										<span class="ui-icon ui-icon-calculator"></span>
									</button>
								<?php endif; ?>
							</td>
						</tr>

						<tr class="fondSect3 colorPage detailSeq hide">
							<td class="nowrap pad10" colspan="9"><img src="gfx/ajax-loader.gif" /> please wait...</td> <!-- LISTE DES SHOTS (call Ajax)-->
						</tr>
					</table>
				</li>
			<?php endforeach;
		else : ?>
			<div class="ui-state-default pad5">No sequence.</div>
		<?php
		endif; ?>

	</ul>

</div>