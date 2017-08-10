<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once('directories.php');

if (isset($_POST['projectID']))
	$idProj = $_POST['projectID'];
else die('Pas de projet à charger...');

try {
	$p = new Projects($idProj);
	$projDepts = $p->getDeptsProject(false, 'label', 'shots');
	$projSequences = $p->getSequences();
}
catch (Exception $e) {
	echo 'ERREUR : '.$e->getMessage();
	die();
}

?>
<script>
	seqDeroulees	= [];
	selection		= { seq: {}, shot: {} };
	$(function(){
		$('.bouton').button();
		$('.watchSelector').buttonset();
		$('#watchSelectAll').buttonset();
		$('.forceDept').selectmenu({style: 'dropdown'});
		$('#forceDeptAll').selectmenu({style: 'dropdown'});

		listenChanges();
		refreshWatchesSelection();

		// Déroulage des infos des séquences
		$('.seqTable td:not(.nowrap)').off('click');
		$('.seqTable td:not(.nowrap)').on('click', function(){
			if (!$(this).parent().hasClass('ui-state-focusFake')) {
				if (!isShift) {
					$('.detailSeq').hide();
					$('tr').removeClass('ui-state-focusFake');
				}
				var idSeq = $(this).parents('tr').attr('idSeq');
				if (seqDeroulees.indexOf(idSeq) == -1) {
					var forceDept = $(this).parents('tr').find('.forceDept').val();
					var selectAll = $(this).parents('tr').find(':radio:checked').val() == 'watch';
					var params = { projectID: project_ID, sequenceID: idSeq, forceDept: forceDept, selectAll: selectAll };
					if (selectAll)
						$(this).parents('tr').find('.watchSelector').addClass('bordHi2 ui-corner-all');
					removeWatchToSelection('seq', idSeq);
					$(this).parent().addClass('ui-state-focusFake').next('tr').load('modals/depts_shot_specific/dept_final_shot_list.php', params, function(){
						seqDeroulees.push(idSeq);
						listenChanges();
						refreshWatchesSelection();
					});
				}
				$(this).parent().addClass('ui-state-focusFake').next('tr').show();
			}
			else {
				$(this).parent().removeClass('ui-state-focusFake').next('tr').hide();
			}
		});

		// watch / unwatch ALL
		$('#watchSelectAll').off('change');
		$('#watchSelectAll').on('change', function(){
			var state = $(this).find(':radio:checked').val();
			$('#sequencesListe').find(':radio:checked').removeAttr('checked');
			$('.watchSelector:not(.ui-state-disabled)').find(':radio[value="'+state+'"]').attr('checked', 'checked');
			$('.watchSelector:not(.ui-state-disabled)').buttonset("refresh");
			refreshWatchesSelection();
			if (state == 'unwatch')
				$('tr').find('.watchSelector').removeClass('bordHi2 ui-corner-all');
		});
	});
</script>

<div class="ui-widget-content noBorder">
	<table class="seqTableHead">
		<tr>
			<th class="w150">&nbsp;Titre</th>
			<th class="w50">Label</th>
			<th class=""></th>
			<th class="w150 center mini">
				<div class="inline mid" title="Force department selection">
					<select class="w150" id="forceDeptAll">
						<option value="0">AUTO</option>
						<option disabled>-------</option>
						<?php foreach($projDepts as $deptID => $deptName): ?>
							<option value="<?php echo $deptID; ?>"><?php echo strtoupper($deptName); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</th>
			<th class="w80 center pico">
				<div id="watchSelectAll">
					<input type="radio" name="watch_all" elemType="all" elemId="0" value="watch" id="btnWatch_all" />
					<label for="btnWatch_all" title="include all sequences"><span class="ui-icon ui-icon-check"></span></label>
					<input type="radio" name="watch_all" elemType="all" elemId="0" value="unwatch" id="btnUnwatch_all" />
					<label for="btnUnwatch_all" title="exclude all sequences"><span class="ui-icon ui-icon-close"></span></label>
				</div>
			</th>
		</tr>
	</table>
</div>
<div id="sequencesListe">
<?php foreach($projSequences as $seqID => $seq):
	$nbShots = $p->getNbShots($seqID, 'actifs'); ?>
	<div class="ui-state-default">
		<table class="seqTable w100p">
			<tr class="seqLine" idSeq="<?php echo $seqID; ?>">
				<td class="w150">&nbsp;<?php echo $seq[Sequences::SEQUENCE_TITLE]; ?></td>
				<td class="w50 colorSoft"><?php echo $seq[Sequences::SEQUENCE_LABEL]; ?></td>
				<td title="Shots count"><span class="inline mid ui-icon ui-icon-copy"></span><b><?php echo $nbShots; ?></b></td>
				<td class="w150 center mini noPad nowrap">
					<?php if ($nbShots > 0) : ?>
					<div class="inline mid" title="Force department selection">
						<select class="w150 forceDept" itemType="seq" itemID="<?php echo $seqID; ?>">
							<option value="0">AUTO</option>
							<option disabled>-------</option>
							<?php foreach($projDepts as $deptID => $deptName): ?>
								<option value="<?php echo $deptID; ?>"><?php echo strtoupper($deptName); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</td>
				<td class="w80 center pico noPad nowrap">
					<div class="inline mid watchSelector">
						<input type="radio" name="watch_<?php echo $seqID; ?>" elemType="seq" elemId="<?php echo $seqID; ?>" value="watch" id="btnWatch_<?php echo $seqID; ?>" />
						<label for="btnWatch_<?php echo $seqID; ?>" title="include sequence"><span class="ui-icon ui-icon-check"></span></label>
						<input type="radio" name="watch_<?php echo $seqID; ?>" elemType="seq" elemId="<?php echo $seqID; ?>" value="unwatch" id="btnUnwatch_<?php echo $seqID; ?>" checked="checked" />
						<label for="btnUnwatch_<?php echo $seqID; ?>" title="exclude sequence"><span class="ui-icon ui-icon-close"></span></label>
					</div>
					<?php endif; ?>
				</td>
			</tr>
			<tr class="fondSect3 colorPage detailSeq hide">
				<td class="nowrap pad10" colspan="9"><img src="gfx/ajax-loader.gif" /> please wait...</td> <!-- LISTE DES SHOTS (call Ajax)-->
			</tr>
		</table>
	</div>
<?php endforeach; ?>
</div>