<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('dates.php');

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
	$projDepts = $proj->getDeptsProjectWithInfos();

	// Chargement des infos de la séquence
	$seq  = new Sequences($idSeq);
	$titleSeq = $seq->getSequenceInfos(Sequences::SEQUENCE_TITLE);
	$labelSeq = $seq->getSequenceInfos(Sequences::SEQUENCE_LABEL);

	// Chargement des infos du shot
	$shot = new Shots($idShot);
	$shot->recalc_shot_progress();
	$titleShot = $shot->getShotInfos(Shots::SHOT_TITLE);
	$labelShot = $shot->getShotInfos(Shots::SHOT_LABEL);
	if ($titleShot == '') $titleShot = $labelShot;
	$startShot		= SQLdateConvert($shot->getShotInfos(Shots::SHOT_DATE));
	$endShot		= SQLdateConvert($shot->getShotInfos(Shots::SHOT_DEADLINE));
	$dateEndTest	= SQLdateConvert($shot->getShotInfos(Shots::SHOT_DEADLINE), 'timeStamp');
	$classDeadLine	= ($dateEndTest < time()) ? "ui-state-error" : "";
	$nbDaysLeft		= (int)floor( - (time() - $dateEndTest) / (60*60*24) + 1);
	$daysLeftStr    = days_to_date($nbDaysLeft);
	$btnHideClass	= ($shot->getShotInfos(Shots::SHOT_HIDE) == 1) ? 'greyButton' : '';
	$btnLockClass	= ($shot->getShotInfos(Shots::SHOT_LOCK) == 1) ? 'greyButton' : '';
	$btnLockIcon	= ($shot->getShotInfos(Shots::SHOT_LOCK) == 1) ? 'ui-icon-locked' : 'ui-icon-unlocked';
	$lockedShot		= ($shot->isLocked()) ? 'true' : 'false';
	$lastUpUserID	= $shot->getShotInfos(Shots::SHOT_UPDATED_BY);
	$u = new Users((int)$lastUpUserID);
	$lastUpdateUser = $u->getUserInfos(Users::USERS_PSEUDO);
	$lastUpdate		= SQLdateConvert($shot->getShotInfos(Shots::SHOT_UPDATE), 'format', '<b>Y-m-d</b>, H:i');
	unset($u);

	// Récupère tous les départements actifs du shot
	$deptsInfos		= $shot->getDeptsInfos();
	$jsDeptsArray	= "[";
	foreach($deptsInfos as $deptExist => $void)
		$jsDeptsArray .= "'$deptExist',";
	$jsDeptsArray	= trim($jsDeptsArray, ',');
	$jsDeptsArray  .= "]";

	$vignetteShot = check_shot_vignette_ext($idProj, $labelSeq, $labelShot);

	// récupère la liste des utilisateurs lead (et au dessus)
	$l = new Liste();
	$l->getListe(TABLE_USERS, 'id,pseudo,status', 'pseudo', 'ASC', 'status', '>=', Users::USERS_STATUS_LEAD);
	$leadsList = $l->simplifyList(Users::USERS_ID);
	$autocompleteLeads = $autocompleteSups = '[';
	foreach($leadsList as $user) {
		$autocompleteLeads .= '"'.$user[Users::USERS_PSEUDO].'",';
		if ($user[Users::USERS_STATUS] >= Users::USERS_STATUS_SUPERVISOR)
			$autocompleteSups .= '"'.$user[Users::USERS_PSEUDO].'",';
	}
	$autocompleteLeads = substr($autocompleteLeads, 0, -1).']';
	$autocompleteSups = substr($autocompleteSups, 0, -1).']';

	// récupère la liste des utilisateurs qui sont assignés au projet (pour pouvoir les assigner au shot)
	$l->resetFiltre();
	$l->addFiltre(Users::USERS_STATUS, '>=', Users::USERS_STATUS_ARTIST, 'AND');
	$l->addFiltre(Users::USERS_MY_PROJECTS, 'LIKE', '%'.$idProj.'%');
	$l->getListe(TABLE_USERS, 'id,pseudo,status,my_tags', 'pseudo', 'ASC');
	$artistsList = $l->simplifyList(Users::USERS_ID);

	// Récupère les Tags users
	$autocompleteArtists = '[';
	$allTags	= Array();
	foreach($artistsList as $user) {
		$autocompleteArtists .= '"'.$user[Users::USERS_PSEUDO].'",';
		$userF = json_decode($user[Users::USERS_MY_TAGS]);
		if (is_array($userF)) {
			foreach ($userF as $f) {
				if (!in_array($f, $allTags)) $allTags[] = $f;
			}
		}
	}
	$autocompleteArtists = substr($autocompleteArtists, 0, -1).']';

	// Construit la liste des tags selon les users
	$usersTags = Array();
	foreach ($allTags as $tag) {
		foreach($artistsList as $user) {
			$userF = json_decode($user[Users::USERS_MY_TAGS]);
			if (is_array($userF) && in_array($tag, $userF))
				$usersTags[$tag][] = $user[Users::USERS_PSEUDO];
		}
	}
}
catch (Exception $e) {
	echo $e->getMessage();
	die();
}

try {
	// Récupère les Tags globaux
	$SaAMinfo = new Infos(TABLE_CONFIG);
	$SaAMinfo->loadInfos('version', SAAM_VERSION);
	$globalTags = json_decode($SaAMinfo->getInfo('global_tags'));
	$shotTags   = json_decode($shot->getShotInfos(Shots::SHOT_TAGS));
}
catch (Exception $e) { $errTags = '<div class="inline ui-state-error ui-corner-all pad3">WARNING : version problem !</div>'; }

?>

<script>
	var project_ID = '<?php echo $idProj; ?>'; var seq_ID = '<?php echo $idSeq; ?>'; var shot_ID = '<?php echo $idShot; ?>';
	localStorage['openSeq_'+project_ID] = seq_ID;
	var autocompleteLeads   = <?php echo $autocompleteLeads; ?>;
	var autocompleteSups    = <?php echo $autocompleteSups; ?>;
	var autocompleteArtists = <?php echo $autocompleteArtists; ?>;
	var departement	= "structure";
	var shotAllDepts = <?php echo $jsDeptsArray; ?>;
	var isLocked	= <?php echo $lockedShot; ?>;

	$(function(){
		$('.bouton').button();
		$('.greyButton').addClass('ui-state-disabled doigt');

		var view = stageHeight - 28;
		$('.stageContent').slimScroll({
			position: 'right',
			height: view+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});

		$('#supervisor').autocomplete({source: autocompleteSups});
		$('#lead').autocomplete({source: autocompleteLeads});
		$('#date').datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true, onSelect: function(){checkAllFilled();}});		// Calendrier sur focus d'input
		$('#deadline').datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true, onSelect: function(){checkAllFilled();}});		// Calendrier sur focus d'input

		$('#addArtistToShotInput').multiselect({height: '340px', selectedList: 4, noneSelectedText: 'Aucun', selectedText: '# artists', checkAllText: ' ', uncheckAllText: ' '});

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

		// INTERFACE DES TAGS
		$('.tagLine').hover(
			function() { $(this).addClass('ui-state-focus'); },
			function() { $(this).removeClass('ui-state-focus'); }
		);
		$('.chooseTagLine').click(function() {
				$(this).parents('.tagLine').find('input').click();
		});
		$('#tagsContainer').find('input')
			.each(function(i,e){
				if ($(this).attr('checked'))
					$(this).parents('.tagLine').addClass('ui-state-error');
				if ($(this).attr('disabled'))
					$(this).parents('.tagLine').addClass('ui-state-disabled');
			});

	});

	function gotoDept (deptLbl) {
		$('#shots_depts').find('.deptBtn[label="'+deptLbl+'"]').click();
	}
</script>
<script src="ajax/depts/dept_common.js"></script>
<script src="ajax/depts/dept_structure_shot.js"></script>


<div class="stageContent fondHigh">
	<div class="topInfosStage" help="shot_header">

		<div class="nano" id="backToProj" title="Back to sequences list" help="shot_back_to_sequences">
			<button class="bouton"><span class="inline mid ui-icon ui-icon-arrowthickstop-1-n"></span></button>
		</div>

		<div class="projTitle fondSemiTransp doigt" id="shot_title" title="click to show/hide infos" help="shot_vignette">
			<span class="mini"><?php echo $titleSeq; ?> /</span> <?php echo $titleShot; ?>
		</div>

		<div class="vignetteTopInfos" help="shot_vignette">
			<img class="toHide" src="<?php echo $vignetteShot; ?>" id="vignetteShot_img" />
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
				<div class="inline top ui-state-disabled padH5" title="<?php echo L_TEAM; ?>" id="shot_teamList">
					<div class="inline top ui-state-disabled ui-icon ui-icon-person" title="<?php echo L_TEAM; ?>"></div>
					<?php echo $shot->getShotTeam('str', 18); ?>
				</div>

				<div class="inline top doigt" id="showAddArtistToShot" title="Modify shot's team" help='modify_shot_team'>
					<span class="ui-icon ui-icon-plus"></span>
				</div>
				<div class="inline top" style="display:none;" id="addArtistToShotDiv" help='modify_shot_team'>
					<div class="inline mid">
						<select class="mini noPad modSeq" title="Artists" id="addArtistToShotInput" multiple>
							<?php
							foreach ($artistsList as $artist) {
								$selected = '';
								if (in_array($artist['id'], $shot->getShotTeam('arrayIDs')))
									$selected = 'selected';
								echo '<option class="mini" value="'.$artist['id'].'" '.$selected.'>'.$artist['pseudo'].'</option>';
							}
							?>
						</select>
					</div>
					<div class="inline mid nano"><button class="bouton" id="addArtistToShotBtn"><span class="ui-icon ui-icon-check"></span></button></div>
				</div>
				<div class="margeTop10 colorMid leftText">
					<span class="inline mid ui-icon ui-icon-extlink" title="<?php echo L_FORMAT;?>"></span><?php echo $format; ?>
					<span class="inline mid marge10l ui-icon ui-icon-signal"  title="<?php echo L_FPS;?>"></span><?php echo $shot->getShotFPS(); ?> <?php echo L_FPS; ?>
					<br />
					<?php echo $shot->getNbFrames(); ?> <?php echo L_FRAMES; ?>
				</div>
			</div>

		</div>

		<div class="fleche" id="detailsInfosRight">
			<div class="inline mid pad5 ui-state-error-text colorHard" help="shot_informations">
				<span class="inline mid ui-icon ui-icon-arrowthickstop-1-e" title="end until"></span>
				<span class="inline mid" title="<?php echo L_REM_TIME; ?>"><?php echo $daysLeftStr; ?></span>
			</div>

			<div class="margeTop5 padH5 colorMid toHide" id="shot_Descr" title="shot description" help="shot_informations">
				<?php echo $shot->getShotInfos(Shots::SHOT_DESCRIPTION); ?>
				<br />
				<br />
			</div>

			<div class="topActionStage" idShot="<?php echo $idShot; ?>" help="modify_shot_buttons">
				<button class="bouton" id="refreshShot" title="Refresh shot window"><span class="ui-icon ui-icon-arrowrefresh-1-s"></span></button>
				<button class="bouton marge10l <?php echo $btnHideClass; ?>" title="show/hide" id="btn_hideShot"><span class="ui-icon ui-icon-lightbulb"></span></button>
				<button class="bouton <?php echo $btnLockClass; ?>" title="lock/unlock" id="btn_lockShot"><span class="ui-icon <?php echo $btnLockIcon; ?>"></span></button>
				<?php if ($shot->getShotInfos(Shots::SHOT_ARCHIVE) == 0): ?>
				<button class="bouton" title="archiver" id="btn_archiveShot"><span class="ui-icon ui-icon-trash"></span></button>
				<?php else : ?>
				<button class="bouton seq_restore ui-state-highlight" title="restore"><span class="ui-icon ui-icon-refresh"></span></button>
				<?php endif; ?>
			</div>

			<div class="bottomActionStage" idProj="<?php echo $idProj; ?>">

			</div>
		</div>

	</div>


	<div class="leftText fondHigh" id="shotView" style="height:100%;">
		<div class="ui-widget-content noBorder">
			<table class="seqTableHead">
				<tr>
					<th class="center" style="width:270px;"><?php echo L_MODIFY; ?></th>
					<th help="shot_tags"><span class="marge10l activeShotCenter"><?php echo L_TAGS; ?></span></th>
					<th style="width:50%;" title="shot #<?php echo $idShot; ?>">Departments informations for this shot</th>
				</tr>
			</table>
		</div>

		<div class="shadowOut" id="shotViewLeft" help="modify_shot">
			<div class="margeTop10">
				<div class="inline mid w80">&nbsp;<?php echo L_TITLE;?></div>
				<input type="text" class="noBorder pad3 ui-corner-top w150 fondSect3 modShotDetail requiredField" value="<?php echo $titleShot; ?>" title="Shot title" id="title" onkeypress="return checkChar(event,null,true,null)" />
				<div class="inline mid noBG noBorder"><span class="ui-icon ui-icon-check"></span></div>
			</div>
			<div class="margeTop1">
				<div class="inline mid w80">&nbsp;<?php echo L_START;?></div>
				<input type="text" class="noBorder pad3 fondSect3 inputDate modShotDetail requiredField" style="width: 150px;" value="<?php echo $startShot; ?>" title="<?php echo L_START; ?>" id="date" />
				<div class="inline mid noBG noBorder"><span class="ui-icon ui-icon-check"></span></div>
			</div>
			<div class="margeTop1">
				<div class="inline mid w80">&nbsp;<?php echo L_END;?></div>
				<input type="text" class="noBorder pad3 fondSect3 inputDeadline modShotDetail requiredField" style="width: 150px;" value="<?php echo $endShot; ?>" title="<?php echo L_END; ?>" id="deadline" />
				<div class="inline mid noBG noBorder"><span class="ui-icon ui-icon-check"></span></div>
			</div>
			<div class="margeTop1">
				<div class="inline mid w80">&nbsp;<?php echo L_SUPERVISOR;?></div>
				<input type="text" class="noBorder pad3 w150 fondSect3 modShotDetail" value="<?php echo $shot->getShotInfos(Shots::SHOT_SUPERVISOR); ?>" title="<?php echo L_SUPERVISOR; ?>" id="supervisor" />
			</div>
			<div class="margeTop1">
				<div class="inline mid w80">&nbsp;<?php echo L_LEAD;?></div>
				<input type="text" class="noBorder pad3 w150 fondSect3 modShotDetail" value="<?php echo $shot->getShotInfos(Shots::SHOT_LEAD); ?>" title="<?php echo L_LEAD; ?>" id="lead" />
			</div>
			<div class="margeTop1">
				<div class="inline mid w80">&nbsp;<?php echo L_FRAMES;?></div>
				<input type="text" class="noBorder pad3 w150 fondSect3 modShotDetail numericInput" value="<?php echo $shot->getNbFrames(); ?>" title="Frames" id="nbframes" />
			</div>
			<div class="margeTop1">
				<div class="inline top w80 margeTop5">&nbsp;<?php echo L_DESCRIPTION;?></div>
				<textarea class="noBorder pad3 ui-corner-bottom w150 fondSect3 modShotDetail" rows="10" title="<?php echo L_DESCRIPTION;?>" id="description"><?php
					echo preg_replace('/<br>/', "\n", $shot->getShotInfos(Shots::SHOT_DESCRIPTION));
				?></textarea>
			</div>
			<div class="margeTop10 marge30l center">
				<button class="bouton" id="btn_modShot"><?php echo L_BTN_VALID; ?></button>
				<button class="bouton" id="btn_annulModShot"><?php echo L_BTN_CANCEL; ?></button>
			</div>
			<br /><br />
		</div>


		<div id="shotViewCenter" help="shot_tags">
			<div class="ui-corner-br fondSect1 pad5" style="position: absolute; top:-10px;" id="tagsContainer">
				<?php if (is_array(@$globalTags)) :
					foreach($globalTags as $gTag) :
					$checked = (@in_array($gTag, $shotTags)) ? 'checked' : '';
					?>
					<div class="inline marge10r margeTop1 ui-corner-all bordFin bordColInv1 doigt tagLine" title="global <?php echo L_TAGS; ?>">
						<div class="inline mid"><input type="checkbox" value="<?php echo $gTag; ?>" <?php echo $checked; ?> /></div><div class="inline mid pad5 chooseTagLine"><?php echo $gTag; ?></div>
					</div><br />
				<?php endforeach;
				else : echo @$errTags;
				endif;  ?>
				<div class="bordPage" style=""> </div>
				<?php if (is_array($usersTags)) :
					foreach($usersTags as $uTag => $uPseudos) :
						$disabled = (@in_array($_SESSION['user']->getUserInfos(Users::USERS_PSEUDO), $uPseudos)) ?  '' : 'disabled';
						$checked  = (@in_array($uTag, $shotTags)) ? 'checked' : ''; ?>
						<div class="inline marge10r margeTop1 ui-corner-all bordFin bordColInv1 doigt tagLine" title="<?php echo L_TAGS.' de '.implode(', ', $uPseudos); ?>">
							<div class="inline mid"><input type="checkbox" value="<?php echo $uTag; ?>" <?php echo $checked.' '.$disabled; ?> /></div><div class="inline mid pad5 chooseTagLine"><?php echo $uTag; ?></div>
						</div><br />
					<?php endforeach;
				endif; ?>
			</div>
		</div>


		<div id="shotViewRight" help="shot_all_depts_infos">
			<div class=" margeTop10 marge10r petit floatR colorDark">Last update by <b><?php echo $lastUpdateUser; ?></b>, <?php echo $lastUpdate; ?></div>
			<div class="margeTop5" style="margin-bottom: 8px;">
				<select class="mini noPad selectShotDepts" title="<?php echo L_DEPTS.' '.L_ASSIGNMENTS; ?>" multiple>
					<option class="mini" value="dectech" selected disabled>dec.tech.</option>
					<option class="mini" value="storyboard" selected disabled>storyboard</option>
				<?php foreach ($projDepts as $Pdept) :
					$selected = '';
					if (in_array($Pdept['id'], $shot->getShotDepts('id'))) $selected = 'selected'; ?>
						<option class="mini" value="<?php echo $Pdept['id']; ?>" <?php echo $selected; ?>><?php echo $Pdept['label']; ?></option>
				<?php endforeach; ?>
				</select>
			</div>

			<div class="ui-state-default pad5">
				<div class="inline mid w100"><?php echo strtoupper(L_DECTECH); ?></div>
				<div class="inline mid nano">
					<button class="bouton" onClick="gotoDept('dectech');" title="Open department '<?php echo L_DECTECH; ?>'">
						<span class="ui-icon ui-icon-arrowthickstop-1-e" deptName="dectech"</span>
					</button>
				</div>
			</div><?php
			if (!isset($deptsInfos['storyboard'])) : ?>
				<div class="ui-state-default pad5">
					<div class="inline mid w100"><?php echo strtoupper(L_STORYBOARD); ?></div>
					<div class="inline mid nano">
						<button class="bouton" onClick="gotoDept('storyboard');" title="Open department '<?php echo L_STORYBOARD; ?>'">
							<span class="ui-icon ui-icon-arrowthickstop-1-e" deptName="storyboard"></span>
						</button>
					</div>
					<div class="inline mid">
						<div class="inline mid marge10l">
							<span class="ui-state-disabled"><i>No pub.</i></span>
						</div>
					</div>
				</div><?php
			endif;
			if (is_array($deptsInfos) && count($deptsInfos) > 0) :
				foreach($deptsInfos as $deptName => $deptInf) :
					try {
						$theDeptInfos = new Infos(TABLE_DEPTS);
						$theDeptInfos->loadInfos('label', $deptName);
						$labelsSteps = json_decode($theDeptInfos->getInfo('etapes'));
					}
					catch(Exception $e) { $labelsSteps[0] = ''; }
					if (isset($deptInf['shotStep'])) {
						$stepName = ((int)$deptInf['shotStep'] > 0) ? $stepName = '<b>'.$labelsSteps[$deptInf['shotStep']-1].'</b> ' : '<b>'.L_APPROVED.'</b>';
					}
					else $stepName = '<b>'.$labelsSteps[0].'</b>';

					$classNretake = ''; $isValidLastR = 'No published';
					if (isset($deptInf['nRetake'])) {
						$isValidLastR = 'Last published NOT validated';
						if (isset($deptInf['retake']) && @$deptInf['retake'] === true) {
							$classNretake = 'colorOk';
							$isValidLastR = 'Last published VALIDATED';
						}
					}
					?>
					<div class="ui-state-default pad5">
						<div class="inline mid w100"><?php echo strtoupper($deptName); ?></div>
						<div class="inline mid nano">
							<button class="bouton" onClick="gotoDept('<?php echo $deptName; ?>');" title="Open department '<?php echo $deptName; ?>'">
								<span class="ui-icon ui-icon-arrowthickstop-1-e" deptName="<?php echo $deptName; ?>"></span>
							</button>
						</div>
						<div class="inline mid marge10l w80 <?php echo $classNretake; ?>" title="<?php echo $isValidLastR; ?>">
							<?php echo (isset($deptInf['nRetake'])) ? '<b>'.$deptInf['nRetake'].'</b> pub(s)' : '<span class="ui-state-disabled"><i>No pub.</i></span>'; ?>
						</div>
						<div class="inline mid w50" title="<?php echo L_FPS; ?>">
							<?php echo (isset($deptInf['fps'])) ? '<b>'.$deptInf['fps'].'</b> '.L_FPS : ''; ?>
						</div>
						<div class="inline mid w100" title="<?php echo L_ETAPES; ?>">
							<?php echo $stepName; ?>
						</div>
						<div class="inline mid">
							<?php echo (isset($deptInf['startF'])) ? L_RANGE.' : <b>'.$deptInf['startF'].'</b> -> <b>'.$deptInf['endF'].'</b>' : ''; ?>
							<?php echo (isset($deptInf['clock'])) ? '<b>'.$deptInf['clock'].'</b> KHz' : ''; ?>
						</div>
					</div>
				<?php endforeach;
				else : ?>
					<div class="ui-state-default pad5">No data.</div>
			<?php endif;?>
		</div>
	</div>
</div>
