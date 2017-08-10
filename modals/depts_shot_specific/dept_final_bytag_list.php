<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once('vignettes_fcts.php');
	extract($_POST);

if (!isset($tagName))		die('<div class="ui-state-error ui-corner-all pad10">Tag name is missing.</div>');

$shotList = Shots::getShotsByTag($tagName);
if (!is_array($shotList))	die('<script>selection = { seq: {}, shot: {} }; refreshWatchesSelection(); modifDeptSelection("shot", "all", 0);</script>
	<div class="ui-corner-all fondSect4 pad10">No shot tagged with "'.preg_replace('/^#FT_/', '', $tagName).'".</div>');

$shotListJS = '{';
foreach($shotList as $shot) {
	$shotListJS .= '"'.$shot[Shots::SHOT_ID_SHOT].'":"0",';
}
$shotListJS = trim($shotListJS, ',').'}';

$p = new Projects($projectID);
$titleProj = $p->getTitleProject();
$projDepts = $p->getDeptsProject(false, 'label', 'shots');
?>

<script>
	selection = { seq: {}, shot: <?php echo $shotListJS; ?> };
	$(function(){
		$('#forceDeptAll').selectmenu({style: 'dropdown'});
		refreshWatchesSelection();
		modifDeptSelection('shot', 'all', 0);
	});
</script>

<div class="ui-widget ui-widget-content pad3 center noBorder">
	<div class="floatR mini" style="margin-right:77px;" title="Force department selection">
		<select class="w150" id="forceDeptAll">
			<option value="0">AUTO</option>
			<option disabled>-------</option>
			<?php foreach($projDepts as $deptID => $deptName): ?>
				<option value="<?php echo $deptID; ?>"><?php echo strtoupper($deptName); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="fixFloat"></div>
</div>
<div class="margeTop10">
	<?php foreach($shotList as $shot):
		$seq = new Sequences($shot[Shots::SHOT_ID_SEQUENCE]);
		$seqShot	= $seq->getSequenceInfos(Sequences::SEQUENCE_TITLE);
		$seqShotL	= $seq->getSequenceInfos(Sequences::SEQUENCE_LABEL);
		$shotVignette = check_shot_vignette_ext($projectID, $seqShotL, $shot[Shots::SHOT_LABEL]); ?>
		<div class="inline top w180 noPad marge5 center colorSoft" title="<?php echo $seqShotL.'/'.$shot[Shots::SHOT_LABEL]; ?>">
			<img src="<?php echo $shotVignette; ?>" width="150" /><br />
			<?php echo $seqShot.' | '.$shot[Shots::SHOT_TITLE]; ?>
		</div>
	<?php endforeach; ?>
</div>