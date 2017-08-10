<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once('vignettes_fcts.php');
	extract($_POST);

if (!isset($tagName)) die('<div class="ui-state-error ui-corner-all pad10">tag name is missing.</div>');
if (!isset($type))	  die('<div class="ui-state-error ui-corner-all pad10">tag type is missing.</div>');


$shotList = Shots::getShotsByTag($tagName);
$categTag = (preg_match('/^#FT_/', $tagName)) ? '<span class="colorHard">'.L_FINAL.'</span>' : '';
if (!is_array($shotList)) die('<div class="ui-corner-all fondSect4 pad10 margeTop10 colorMid w600">No shot with '.$categTag.' TAG "<span class="gras colorHard">'.preg_replace('/^#FT_/', '', $tagName).'</span>".</div>');

?>
<script>
$(function(){
	$('.bouton').button();
	var shlH = stageHeight - 115;
	$('#shotsList').slimScroll({
		position: 'right',
		height: shlH+'px',
		width: '600px',
		size: '10px',
		wheelStep: 10,
		railVisible: true
	});
});
</script>


<div class="ui-corner-all fondSect4 margeTop10 colorMid">
	<div class="floatR marge5">
		<button class="bouton" onclick="window.open('modals/sheets/contact_sheet.php?tagName=<?php echo urlencode($tagName); ?>', 'ContactSheet', 'menubar=yes, scrollbars=yes, width=1024');" title="view list to print">
			<img src="gfx/printer.png" width="10" /> PDF
		</button>
		<a href="modals/download_export_list_from_tag.php?tagName=<?php echo urlencode($tagName); ?>&type=xml" target="new">
			<button class="bouton" title="export list to XML and download"><span class="inline bot ui-icon ui-icon-calculator"></span> xml</button>
		</a>
		<a href="modals/download_export_list_from_tag.php?tagName=<?php echo urlencode($tagName); ?>&type=txt" target="new">
			<button class="bouton" title="export list to text and download"><span class="inline bot ui-icon ui-icon-note"></span> txt</button>
		</a>
	</div>
	<div class="pad10">
		Shots with <?php echo $categTag; ?> TAG "<span class="gras colorHard"><?php echo preg_replace('/^#FT_/', '', $tagName); ?></span>"
	</div>
</div>

<div class="margeTop10" style="padding: 0px 10px 0px 3px" id="shotsList">
	<table class="tableListe" style="width: 580px;">
		<tr class="fondSect4">
			<th class="ui-state-disabled"></th>
			<th class="w80 ui-state-disabled"></th>
			<th class="w150 ui-state-disabled"><?php echo L_PROJECT; ?></th>
			<th class="w150 ui-state-disabled"><?php echo L_SEQUENCE; ?></th>
			<th class="ui-state-disabled"><?php echo L_SHOT; ?></th>
		</tr>
	<?php
		$nShots = 0;
		foreach ($shotList as $shot) :
			$nShots++;
			$pr = new Projects($shot[Shots::SHOT_ID_PROJECT]);
			if (!$pr->isVisible()) continue;
			$projShot	= $pr->getTitleProject();
			$seq = new Sequences($shot[Shots::SHOT_ID_SEQUENCE]);
			$seqShot	= $seq->getSequenceInfos(Sequences::SEQUENCE_TITLE);
			$seqShotL	= $seq->getSequenceInfos(Sequences::SEQUENCE_LABEL);
			$shotVignette = check_shot_vignette_ext($shot[Shots::SHOT_ID_PROJECT], $seqShotL, $shot[Shots::SHOT_LABEL]); ?>
			<tr class="">
				<td class="ui-state-disabled"><?php echo $nShots; ?></td>
				<td class="doigt gotoShotFromTag"
							idProj="<?php echo $shot[Shots::SHOT_ID_PROJECT]; ?>"
							idSeq="<?php echo $shot[Shots::SHOT_ID_SEQUENCE]; ?>"
							idShot="<?php echo $shot[Shots::SHOT_ID_SHOT]; ?>" title="Goto Shot">
					<img src="<?php echo $shotVignette; ?>" width="50" />
				</td>
				<td class="pad3 gras"><?php echo $projShot; ?></td>
				<td title="label : <?php echo $seqShotL; ?>"><?php echo $seqShot; ?></td>
				<td title="label : <?php echo $shot[Shots::SHOT_LABEL]; ?>"><?php echo $shot[Shots::SHOT_TITLE]; ?></td>
			</tr>
	<?php endforeach; ?>
	</table>
	<p>&nbsp;</p>
</div>