<?php

	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once('dates.php');
	require_once('directories.php');

	// OBLIGATOIRE, id du projet à charger
	if (isset($_POST['projectID']))
		$idProj = $_POST['projectID'];
	else die('Pas de projet à charger...');

	$dept   = $_POST['dept'];
	$deptID = $_POST['deptID'];
	$p = new Projects($idProj);
    $projInfos = $p->getProjectInfos();
    $titleProj = $projInfos[Projects::PROJECT_TITLE];
?>

<script src="ajax/init_chart.js"></script>

<script>
	var limitToProj = idProj;
	$(function(){
		$('.bouton').button();
		$('.headerBtn[active]').addClass('ui-state-activeFake');
		getChartdata('pie', 'storage');
	});
</script>

<div class="topInfosStage" style="height: 19px; padding: 3px 5px;">
    <div class="inline headerBtn ui-corner-top" dataType="storage"	 chartType="pie" active><?php echo L_STORAGE; ?></div>
	<div class="inline headerBtn ui-corner-top" dataType="users"	 chartType="pie"><?php echo L_USERS; ?></div>
	<div class="inline headerBtn ui-corner-top" dataType="messages"  chartType="line"><?php echo L_MESSAGES; ?></div>
	<div class="inline headerBtn ui-corner-top" dataType="published" chartType="line"><?php echo L_RETAKES; ?></div>
</div>

<div class="stageContent noPad">
	<div id="chartContainer"></div>
</div>
