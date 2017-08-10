<?php
	$nbFrames = $shot->getNbFrames();
?>


<!-- MODIF DU SHOT pour DEPT STORYBOARD -->

<div class="hide" title="<?php echo L_MODIFICATION .' (dept '. $dept .') : '. $titleProj .' | '. $titleShot ; ?>" id="modShot_dialog">
	<div class="inline top">
		<div class="margeTop10">
			<div class="inline mid w100 marge30l"><?php echo L_FRAMERATE;?> : </div>
			<select class="w150 mini noPad modShotDeptDetail" title="FPS (Frames per Second)" id="fps">
				<?php
					$FPSlist = explode('|', LIST_FPS);
					foreach($FPSlist as $fpsVal) {
						$selected = ($fpsVal == $fps) ? 'selected="selected"' : '';
						echo "<option class='mini' value='$fpsVal' $selected>$fpsVal</option>";
					}
				?>
			</select>
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
