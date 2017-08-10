<?php
	try {
		$SaAMinfo = new Infos(TABLE_CONFIG);
		$SaAMinfo->loadInfos('version', SAAM_VERSION);
		$globalTags = json_decode($SaAMinfo->getInfo('global_tags'));
		$userTags	 = $_SESSION['user']->getUserTags();
		$shotTags   = json_decode($shot->getShotInfos(Shots::SHOT_TAGS));

		if (!isset($shotStep))
			$shotStep = 1;
		$nS = 0;
?>
<script>
	$(function(){
		// INTERFACE DES TAGS
		$('.tagLine').hover(
			function() { $(this).addClass('ui-state-focus'); },
			function() { $(this).removeClass('ui-state-focus'); }
		);
		$('#tagsContainer').find('input').each(function(i,e){
			if ($(this).attr('checked'))
				$(this).parents('.tagLine').addClass('ui-state-active');
		});
		$('.chooseTagLine').click(function() {
			$(this).parents('.tagLine').find('input').click();
		});
	});
</script>

<div class="ui-corner-br fondSect1 pad5" style="position: absolute; top:-10px;" id="tagsContainer" help="shot_tags">
	<?php if (is_array($globalTags)) :
		foreach($globalTags as $gTag) :
		$checked = (@in_array($gTag, $shotTags)) ? 'checked' : '';
		?>
		<div class="inline marge10r margeTop5 ui-corner-all bordFin bordColInv1 doigt tagLine" title="global <?php echo L_TAGS; ?>">
			<div class="inline mid"><input type="checkbox" value="<?php echo $gTag; ?>" <?php echo $checked; ?> /></div><div class="inline mid pad5 chooseTagLine"><?php echo $gTag; ?></div>
		</div><br />
	<?php endforeach;
	endif;  ?>
	<div class="bordPage" style=""> </div>
	<?php if (is_array($userTags)) :
		foreach($userTags as $uTag) :
		$checked = (@in_array($uTag, $shotTags)) ? 'checked' : ''; ?>
		<div class="inline marge10r margeTop5 ui-corner-all bordFin bordColInv1 doigt tagLine" title="user <?php echo L_TAGS; ?>">
			<div class="inline mid"><input type="checkbox" value="<?php echo $uTag; ?>" <?php echo $checked; ?> /></div><div class="inline mid pad5 chooseTagLine"><?php echo $uTag; ?></div>
		</div><br />
	<?php endforeach;
	endif; ?>
</div>

<?php
}
catch (Exception $e) { echo '<div class="inline ui-state-error ui-corner-all pad3">WARNING : version problem !</div>'; }
?>