<?php
	$nbFrames = $shot->getNbFrames();

	if (!isset($startF))
		$startF = 1;
	if (!isset($endF) && $nbFrames >= 3)
		$endF = $nbFrames;

	if (preg_match('/_/', $dept)) { $dptarr = explode('_', $_POST['dept']); $dept = $dptarr[1]; }
?>


<!-- MODIF DU SHOT pour DEPT LAYOUT -->

<div class="hide" title="<?php echo L_MODIFICATION .' (dept '. $dept .') : '. $titleProj .' | '. $titleShot ; ?>" id="modShot_dialog">
	<div class="inline top">
		<div class="margeTop10" title="<?php echo L_FPS ?> (Frames per Second)">
			<div class="inline mid w100 marge30l"><?php echo L_FRAMERATE;?> : </div>
			<select class="w150 mini noPad modShotDeptDetail" id="fps">
				<?php
					$FPSlist = explode('|', LIST_FPS);
					foreach($FPSlist as $fpsVal) {
						$selected = ($fpsVal == $fps) ? 'selected="selected"' : '';
						echo "<option class='mini' value='$fpsVal' $selected>$fpsVal</option>";
					}
				?>
			</select>
		</div>
		<div class="margeTop10" title="<?php echo L_RANGE . ' ['.L_START.' -> '.L_END.']' ?>">
			<div class="inline mid w100 marge30l"><?php echo L_RANGE;?>  : </div>
			<input type="hidden" value="<?php echo $nbFrames; ?>" id="nbFrames"/>
			<?php if ($nbFrames >= 3) : ?>
			<input type="text" class="noBorder pad3 fondSect3 numericInput w50 modShotDeptDetail" value="<?php echo @$startF; ?>" title="<?php echo L_START; ?>" id="startF" /> ->
			<input type="text" class="noBorder pad3 fondSect3 numericInput w50 modShotDeptDetail" value="<?php echo @$endF; ?>" title="<?php echo L_END; ?>" id="endF" />
			<?php endif; ?>
		</div>
	</div>
	<div class="inline top demi ui-state-disabled">
		<div class="margeTop10 marge30l">
			(<?php echo $shot->getNbFrames() .' '.L_FRAMES ; ?>)
		</div>
		<div class="margeTop10 marge30l">
			<div class="inline top"><?php echo $shot->getShotInfos(Shots::SHOT_DESCRIPTION); ?></div>
		</div>
	</div>

</div>
