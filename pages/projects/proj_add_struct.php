<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	include('directories.php');

	$isDemoUser    = ($_SESSION['user']->isDemo()) ? 'true' : 'false';
	$hideIfNotDemo = ($_SESSION['user']->isDemo()) ? '' : 'hide';
?>

<script>
	var nomencSeq = "<?php echo NOMENCLATURE_SEQ; ?>";
	var nomencSepa = "<?php echo NOMENCLATURE_SEPARATOR; ?>";
	var isDemoMode = <?php echo $isDemoUser; ?>;
	var maxDemoSeq = <?php echo MAX_DEMO_SEQUENCES; ?>;
	$(function() {
		$('.bouton').button();
	});
</script>

<script src="ajax/add_project_struct.js"></script>

<div class="stageContent pad5">
	<h2>"<span id="struct_projTitle"></span>": <?php echo mb_convert_case(L_STRUCTURE, MB_CASE_LOWER); ?></h2>

	<div class="inline top" id="addSeqs">

		<div class="big marge30l margeTop10">
			<?php echo L_ADD; ?> <input type="text" id="proj_nbSeq" class="numericInput noBorder ui-corner-all pad3 center fondSect3" size="2" value="0" />
			<?php echo mb_convert_case(L_SEQUENCE, MB_CASE_LOWER); ?><span id="seqPluriel"></span>
			<span class="colorErreur mini <?php echo $hideIfNotDemo; ?>">(demo : maximum <?php echo MAX_DEMO_SEQUENCES ?>)</span>
		</div>

		<div class="marge30l margeTop10" id="seqList">

		</div>

		<div class="marge30l margeTop10 rightText ui-state-disabled submitBtns">
			<br /><br />
			<button class="bouton" id="proj_DONE"><?php echo mb_convert_case(L_DONE, MB_CASE_UPPER); ?></button>
		</div>

		<br /><br /><br />
	</div>
</div>