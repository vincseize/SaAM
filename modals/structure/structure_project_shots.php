<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	if (isset($_POST['projectID']))
		$idProj = $_POST['projectID'];
	else die('Pas de projet à charger...');
	if (isset($_POST['sequenceID']))
		$idSeq  = $_POST['sequenceID'];
	else die('Séquence indéfinie...');

	// IMPORTANT !!!!!!!!!!!
	if (!isset($_POST['dept']) || empty($_POST['dept']) || $_POST['dept'] == '')
		die('department undefined !');
	// !!!!!!!!!!!!!!!!!!!!!
	$dept = $_POST['dept'];

	require_once('dates.php');
	require_once('directories.php');
	require_once('vignettes_fcts.php');

try {
	$deptInfos = new Infos(TABLE_DEPTS);
	$deptInfos->loadInfos('label', $dept);
	$idDept = $deptInfos->getInfo('id');
	if (!$idDept) $idDept = $_POST['deptID'];
}
catch (Exception $e) { $idDept = $dept; }

try {
	$p = new Projects($idProj);
	$projTitle	= $p->getProjectInfos(Projects::PROJECT_TITLE);
	$seq		= new Sequences($idSeq);
	$shotsList  = $seq->getSequenceShots(false);
}
catch (Exception $e) {
	echo '<pre>'; var_dump($_POST); echo '</pre>';
	die($e->getMessage());
}

if (is_array($shotsList)) : ?>
	<td class="nowrap" colspan="9">
		<table class="sousTable shotsTable">
	<?php foreach($shotsList as $shot) :
		$sh = new Shots($shot['id']);
		$teamStr   = $sh->getShotTeam();
		$nbRetakes = count($sh->getRetakesList($idDept)); ?>
		<tr idShot="<?php echo $shot['id']; ?>"  idSeq="<?php echo $shot[Shots::SHOT_ID_SEQUENCE]; ?>">
				<td class="nowrap w50 doigt shotVignette openShot" title="<?php echo L_OPEN . ' '. L_SHOT;  ?>">
					<img src="<?php echo check_shot_vignette_ext($idProj, $seq->getSequenceInfos(Sequences::SEQUENCE_LABEL), $shot['label'], $idDept); ?>" width="50" />
				</td>
				<td class="nowrap gras shotTitle" style="padding-left:3px; width:140px;" title="<?php echo L_TITLE.' '.L_SHOT; ?>">
					<?php echo $shot['title']; ?>
				</td>
				<td class="nowrap colorSoft" style="width:63px" title="<?php echo L_LABEL.' '.L_SHOT; ?>">
					<?php echo $shot['label']; ?>
				</td>
				<td class="nowrap colorMid doigt openShot" style="width:30px;" title="Number of <?php echo L_RETAKES; ?>">
					<span class="inline mid ui-icon ui-icon-clipboard"></span><?php echo $nbRetakes; ?>
				</td>
				<td class="nowrap w100 doigt shotProgBar openShot">
					<div class="progBar miniProgBar" percent="<?php echo $shot['progress']; ?>">
						<span class="floatL marge10l margeTop1 petit colorMid"><?php echo $shot['progress']; ?>%</span>
					</div>
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
				<td class="nowrap" title="<?php echo L_ARTISTS; ?>">
					<span class="floatL ui-icon ui-icon-person"></span>
					<span class="shotArtists"><?php echo $teamStr; ?></span>
				</td>
				<td class="center nowrap nano actionBtns nano" style="width:162px;">
					<?php if ($dept == 'storyboard') : ?>
						<div class="bouton ui-state-activeFake shot_contactSheetActive" title="include/exclude to contact-sheet"><span class="ui-icon ui-icon-lightbulb"></span></div>
					<?php endif; ?>
				</td>
			</tr>
	<?php endforeach; ?>
		</table>
	</td>
<?php else : ?>
	<td class="nowrap pad5" colspan="9">No shot.</td>
<?php endif; ?>

<script>
	$(function(){
		$('.bouton').button();

		// boutons OPEN PLAN
		$('.shotsTable').off('click','.openShot');
		$('.shotsTable').on('click','.openShot', function(){
			var idSeq  = $(this).parent('tr').attr('idSeq');
			var idShot = $(this).parent('tr').attr('idShot');
			$('.arboItem[idSeq="'+idSeq+'"]').click();
			if (departement == 'dectech')
				loadPageContentModal('depts_shot_specific/dept_dectech_shots', {projectID: project_ID, sequenceID: idSeq, shotID: idShot});
			else if (departement == 'storyboard' || departement == 'final')
				loadPageContentModal('structure/structure_shots', {dept: departement, template: 'dept_'+departement, projectID: project_ID, sequenceID: idSeq, shotID: idShot});
			else
				loadPageContentModal('structure/structure_shots', {dept: departement, deptID: deptID, template: deptFile, projectID: project_ID, sequenceID: idSeq, shotID: idShot});
		});

		// init des progressBars
		$('.progBar').each(function() {
			var percent = parseInt($(this).attr('percent'));
			$(this).progressbar("destroy");
			$(this).progressbar({value: percent});
		});
	});
</script>