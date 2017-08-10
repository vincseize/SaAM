<?php
	$deptSteps = json_decode($deptInfos->getInfo('etapes'));

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

		// INTERFACE DES ETAPES
		$('.etapeChooser')
			.hover(
				function() { if (! $(this).hasClass('ui-state-active')) $(this).addClass('ui-state-hover'); },
				function() { $(this).removeClass('ui-state-hover'); }
			)
			.click(
				function() {
					if (isLocked) {
						$('#retourAjax').html('This shot is locked.').addClass('ui-state-error').show(transition);
						setTimeout(function(){$('#retourAjax').fadeOut(transition, function(){$('#retourAjax').html('');});}, 1000);
						return;
					}
					$('.etapeChooser').removeClass('ui-state-active').addClass('ui-state-default');
					$(this).removeClass('ui-state-default').addClass('ui-state-active');
					var idStep = $(this).attr('idStep');
					var ajaxReq = 'action=modShotDeptInfos&IDshot='+shot_ID+'&dept='+id_dept+'&shotDeptInfos={"shotStep":'+idStep+'}';
					console.log(ajaxReq);
					AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, 'reloadShot');
				}
			);



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
					$(this).parents('.tagLine').addClass('ui-state-active');
			});
	});
</script>


<div id="etapesContainer" title="<?php echo L_ETAPES; ?>" help="shot_steps">
	<?php foreach($deptSteps as $step) :
		$nS++; $showStepClass = 'ui-state-default'; $showStepValidClass = 'ui-state-default';
		if ((int)$shotStep == 0) $showStepValidClass = 'ui-state-active';
		if ((int)$shotStep == $nS) $showStepClass = 'ui-state-active';
		?>
		<div class="<?php echo $showStepClass; ?> ui-corner-right etapeChooser" idStep="<?php echo $nS; ?>"><?php echo $nS.' - '.$step; ?></div>
	<?php endforeach; ?>
	<div class="<?php echo $showStepValidClass; ?> ui-corner-right etapeChooser margeTop10" idStep="0"><?php echo ($nS+1) .' - ' . L_APPROVED;?></div>
</div>



<div class="ui-corner-br fondSect1 pad5 hide" style="position: absolute; top:-10px;" id="tagsContainer" help="shot_tags">
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