<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
?>

<script src="ajax/init_chart.js"></script>

<script>
	var limitToProj = 'ALL';
	$(function(){
		$('.bouton').button();
		$('.headerBtn[active]').addClass('ui-state-activeFake');
		getChartdata('line', 'messages');
	});
</script>

<div class="headerStage">
    <div class="inline headerBtn colorSoft" dataType="storage" chartType="pie">Quotas</div>
	<div class="inline headerBtn colorSoft" dataType="messages" chartType="line" active><?php echo L_MESSAGES; ?></div>
	<div class="inline headerBtn colorSoft" dataType="published" chartType="line"><?php echo L_RETAKES; ?></div>
</div>

<div class="pageContent">
	<div class="pad5 big gras">
		GLOBAL OVERVIEW: <b class="colorBtnFake titleOverviewType"><?php echo mb_strtoupper(L_MESSAGES); ?></b>
	</div>
	<div id="chartContainer"></div>
</div>

<div class="ui-corner-all" id="retourAjax"></div>