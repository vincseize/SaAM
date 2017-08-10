<?php
	@session_start();
	require_once ("../inc/checkConnect.php" );

?>

<div class="headerStage">
	<div class="inline deptBtn colorSoft" type="projects" content="proj_admin_list" active><?php echo L_LIST; ?></div>
	<div class="inline deptBtn colorSoft" type="projects" content="proj_admin_calendar"><?php echo L_SCHEDULE; ?></div>
	<!--<div class="inline deptBtn colorSoft" type="projects" content="proj_admin_rules">Droits</div>-->
</div>


<div class="ui-corner-all" id="retourAjax"></div>

<div class="pageContent noPad">

</div>