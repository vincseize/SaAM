<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	if (isset($_POST['projectID']))
		$idProj = $_POST['projectID'];
	else die('Pas de projet à charger...');
	if (isset($_POST['sequenceID']))
		$idSeq  = $_POST['sequenceID'];
	else die('Séquence indéfinie...');

	require_once('dates.php');
	require_once('directories.php');
	require_once('vignettes_fcts.php');


try {
	$p = new Projects($idProj);
	$projDepts	= $p->getDeptsProjectWithInfos();
	$projTitle	= $p->getProjectInfos(Projects::PROJECT_TITLE);
	$seq		= new Sequences($idSeq);
	$shotsList  = $seq->getSequenceShots(true, true);
}
catch (Exception $e) {
	echo '<pre>'; var_dump($_POST); echo '</pre>';
	die($e->getMessage());
}

if (is_array($shotsList)) : ?>
	<td class="nowrap" colspan="9">
		<ul class="listeShots">
			<?php
			foreach($shotsList as $shot) :
				$sh	= new Shots($shot['id']);
				$teamStr    = $sh->getShotTeam();
				$nbrePub	= 0;
				foreach ($sh->getShotDepts() as $d) {
					$nbrePub += count($sh->getRetakesList($d));
				}
				$hideShot	= $shot[Shots::SHOT_HIDE];
				$lockShot	= $shot[Shots::SHOT_LOCK];
				$btnHideShotClass = ($hideShot == '1') ? 'greyButton' : '';
				$btnLockShotClass = ($lockShot == '1') ? 'greyButton' : '';
				$btnLockShotIcon  = ($lockShot == '1') ? 'ui-icon-locked' : 'ui-icon-unlocked';
				$archivedShot	  = ($shot[Shots::SHOT_ARCHIVE] == '1') ? true : false ;
				?>
			<li idShot="<?php echo $shot['id']; ?>" labelShot="<?php echo $shot['label']; ?>">
				<table class="sousTable shotsTable">
					<tr idShot="<?php echo $shot['id']; ?>" idSeq="<?php echo $shot[Shots::SHOT_ID_SEQUENCE]; ?>">
						<td class="nowrap w50 doigt shotVignette doigt openShot" title="click to open">
							<img src="<?php echo check_shot_vignette_ext($idProj, $seq->getSequenceInfos(Sequences::SEQUENCE_LABEL), $shot['label']); ?>" width="50" />
						</td>
						<td class="nowrap gras shotTitle curMove openShot" style="padding-left:3px; width:140px;" title="Drag to reorder"><?php echo $shot['title']; ?></td>
						<td class="nowrap colorSoft w50 curMove openShot" title="drag to reorder"><?php echo $shot['label']; ?></td>
						<td class="nowrap colorMid doigt openShot" style="width:30px;" title="Number of <?php echo L_RETAKES; ?>">
							<span class="inline mid ui-icon ui-icon-clipboard"></span> <?php echo $nbrePub; ?>
						</td>
						<td class="nowrap w100 doigt shotProgBar">
							<select class="mini noPad selectShotDepts" title="<?php echo L_DEPTS; ?>" multiple>
							<?php foreach ($projDepts as $Pdept) :
								$selected = '';
								if (in_array($Pdept['id'], $sh->getShotDepts('id'))) $selected = 'selected'; ?>
									<option class="mini" value="<?php echo $Pdept['id']; ?>" <?php echo $selected; ?>><?php echo $Pdept['label']; ?></option>
							<?php endforeach; ?>
							</select>
						</td>
						<td class="nowrap w20"></td>
						<td class="nowrap w100" title="<?php echo L_START; ?>">
							<span class="inline mid ui-icon ui-icon-clock"></span>
							<span class="inline mid shotStart"><?php echo SQLdateConvert($shot['date']); ?></span>
						</td>
						<td class="nowrap w100 ui-state-error-text" title="<?php echo L_END; ?>">
							<span class="inline mid ui-icon ui-icon-clock"></span>
							<span class="inline mid shotEnd"><?php echo SQLdateConvert($shot['deadline']); ?></span>
						</td>
						<td class="nowrap"  title="<?php echo L_ARTISTS; ?>">
							<span class="floatL ui-icon ui-icon-person"></span>
							<span class="shotArtists"><?php echo $teamStr; ?></span>
						</td>
						<td class="w150 center nowrap nano actionBtns">
				<?php if ($archivedShot) : ?>
							<button class="bouton plan_restore ui-state-error" title="restaurer"><span class="ui-icon ui-icon-refresh"></span></button>
				<?php else : ?>
							<button class="bouton ui-state-highlight plan_hide <?php echo $btnHideShotClass; ?>" title="<?php echo L_SHOW.'/'.L_HIDE; ?>"><span class="ui-icon ui-icon-lightbulb"></span></button>
							<button class="bouton ui-state-highlight plan_lock <?php echo $btnLockShotClass; ?>" title="<?php echo L_LOCK; ?>"><span class="ui-icon <?php echo $btnLockShotIcon; ?>"></span></button>
							<button class="bouton ui-state-highlight plan_mod marge10l" title="<?php echo L_MODIFY; ?>"><span class="ui-icon ui-icon-pencil"></span></button>
							<button class="bouton ui-state-highlight plan_archive" title="<?php echo L_ARCHIVE; ?>"><span class="ui-icon ui-icon-trash"></span></button>
				<?php endif; ?>
						</td>
					</tr>
				</table>
			</li>
		<?php endforeach; ?>
		</ul>
	</td>
<?php else : ?>
	<td class="nowrap pad5" colspan="9">No shot.</td>
<?php endif; ?>


<script>
	var changeDepts = false;
	$(function(){
		$('.bouton').button();
		$('.greyButton').addClass('ui-state-disabled doigt');
		$(".inputCal").datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true, onSelect: function(){$(this).keyup().blur();}});		// Calendrier sur focus d'input

		// selects des depts
		$('.selectShotDepts').multiselect({height: 200, minWidth: 130, selectedList: 1, noneSelectedText: '<i>No dept</i>', selectedText: '# departments', checkAllText: ' ', uncheckAllText: ' ',
			click: function(){
				changeDepts = true;
			},
			close: function(){
				if (changeDepts == false) return;
				var newShotDepts = JSON.encode($(this).val());
				var shotID = $(this).parents('tr').attr('idShot');
				var ajaxReq = 'action=modShotDepts&shotID='+shotID+'&newDepts='+newShotDepts;
				changeDepts = false;
				AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure);
			}
		});

		$('.listeShots').sortable({
			placeholder: 'ui-state-highlight',
			forcePlaceholderSize: true,
			axis: 'y',
			helper : 'clone',
			update: function(e, ui) {
				var posArr = {}; var i = 1;
				$('li[idShot]').each(function(){
					posArr[$(this).attr('idShot')] = i;
					i++;
				});
				ajaxUpdateShotPos(posArr);
			}
		});

		// boutons OPEN PLAN
		$('.shotsTable').off('click','.openShot');
		$('.shotsTable').on('click','.openShot', function(){
			var idSeq  = $(this).parent('tr').attr('idSeq');
			var idShot = $(this).parent('tr').attr('idShot');
			$('.arboItem[idSeq="'+idSeq+'"]').click();
			loadPageContentModal('depts_shot_specific/dept_structure_shots', {projectID: project_ID, sequenceID: idSeq, shotID: idShot});
		});
	});

	function ajaxUpdateShotPos (newPosArr) {
		var newPosJson = encodeURIComponent(JSON.encode(newPosArr));
		var activeGroupDepts = $('#selectDeptsList').val();
		var strAjax = 'action=modShotPos&newPos='+newPosJson;
		AjaxJson(strAjax, 'admin/admin_shots_actions', retourAjaxStructure, false);
		$('#arboMenu').load('modals/menuArbo.php', {projectID: project_ID, dept: departement, deptID: deptID, template: 'structure', typeArbo: activeGroupDepts}).show();
	}
</script>
