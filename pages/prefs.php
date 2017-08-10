<?php
	@session_start();
	require_once ("../inc/checkConnect.php" );

?>

<div class="headerStage">
	<div class="inline deptBtn colorSoft" type="prefsUser" content="prefs_infos" active><?php echo L_USER_PREFS_INFOS; ?></div>
	<div class="inline deptBtn colorSoft" type="prefsUser" content="prefs_ui"><?php echo L_USER_PREFS_UI; ?></div>
</div>

<div class="ui-corner-all doigt" id="retourAjax"></div>

<div class="pageContent"></div>
