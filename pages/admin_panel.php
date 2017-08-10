<?php
	@session_start();
	require_once ("../inc/checkConnect.php" );				// Ligne à placer toujours en haut du code des pages

?>

<div class="headerStage">
	<div class="inline deptBtn colorSoft" type="admins" content="departments" active><?php echo L_DEPTS ;?></div>
	<div class="inline deptBtn colorSoft" type="admins" content="interface"><?php echo L_INTERFACE; ?></div>
	<div class="inline deptBtn colorSoft" type="admins" content="plugins"><?php echo L_PLUGINS; ?></div>
	<div class="inline deptBtn colorSoft" type="admins" content="about"><?php echo L_ABOUT; ?></div>
</div>

<div class="pageContent"></div>

<div class="ui-corner-all" id="retourAjax"></div>