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
	$projTitle	= $p->getProjectInfos(Projects::PROJECT_TITLE);
	$seq		= new Sequences($idSeq);
	$shotsList  = $seq->getSequenceShots(false);
}
catch (Exception $e) {
	echo '<pre>'; var_dump($_POST); echo '</pre>';
	die($e->getMessage());
}

if (is_array($shotsList)) : ?>
	<td class="nowrap" colspan="5">
		<table class="sousTable">
		<?php
		foreach($shotsList as $shot) :
			$forceDeptAuto  = ($_POST['forceDept'] == '0') ? 'selected' : '';
			$watchChecked   = ($_POST['selectAll'] == 'true') ? 'checked="checked"' : '';
			$unwatchChecked = ($_POST['selectAll'] == 'true') ? '' : 'checked="checked"'; ?>
			<tr class="shotTable" idShot="<?php echo $shot['id']; ?>" idSeq="<?php echo $idSeq ?>">
					<td class="nowrap w50">
						<img src="<?php echo check_shot_vignette_ext($idProj, $seq->getSequenceInfos(Sequences::SEQUENCE_LABEL), $shot['label']); ?>" width="50" />
					</td>
					<td class="nowrap w100">&nbsp;<?php echo $shot['title']; ?></td>
					<td class="nowrap w50 colorSoft" style="width:63px" title="Label du plan"><?php echo $shot['label']; ?></td>
					<td class="nowrap"></td>
					<td class="w150 center mini noPad nowrap">
						<div class="inline mid" title="Force department selection">
							<select class="w150 forceDept" itemType="shot" itemID="<?php echo $shot['id']; ?>">
								<option value="0" <?php echo $forceDeptAuto; ?>>AUTO</option>
								<option disabled>-------</option>
								<?php
								$l = new Liste();
								$allDepts = $l->getListe(TABLE_DEPTS, 'id,label', 'position', 'ASC', 'type', '=', 'shots');
								foreach($allDepts as $dept):
									$selected = ($_POST['forceDept'] == $dept['id']) ? 'selected' : ''; ?>
									<option value="<?php echo $dept['id']; ?>" <?php echo $selected; ?>><?php echo strtoupper($dept['label']); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</td>
					<td class="nowrap w80 center pico noPad">
						<div class="inline watchSelector">
							<input type="radio" name="watch_<?php echo $shot['id']; ?>" elemType="shot" elemId="<?php echo $shot['id']; ?>" value="watch" id="btnWatch_<?php echo $shot['id']; ?>" <?php echo $watchChecked ?> />
							<label for="btnWatch_<?php echo $shot['id']; ?>" title="include shot"><span class="ui-icon ui-icon-check"></span></label>
							<input type="radio" name="watch_<?php echo $shot['id']; ?>" elemType="shot" elemId="<?php echo $shot['id']; ?>" value="unwatch" id="btnUnwatch_<?php echo $shot['id']; ?>" <?php echo $unwatchChecked ?> />
							<label for="btnUnwatch_<?php echo $shot['id']; ?>" title="exclude shot"><span class="ui-icon ui-icon-close"></span></label>
						</div>
					</td>
				</tr>
		<?php endforeach; ?>
		</table>
	</td>
<?php else : ?>'<td class="nowrap pad5" colspan="9">No shot.</td>
<?php endif; ?>


<script>
	$(function(){
		$('.bouton').button();
		$('.watchSelector').buttonset();
		$('.forceDept').selectmenu({style: 'dropdown'});
	});
</script>