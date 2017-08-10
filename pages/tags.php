<?php
	@session_start();
	require_once ("../inc/checkConnect.php" );

?>

<script src="ajax/admin_tags.js"></script>

<div class="headerStage">
	<div class="inline deptBtn colorSoft" type="tags" content="tags_user" active>Tags utilisateur</div>
	<div class="inline deptBtn colorSoft" type="tags" content="tags_global">Tags globaux</div>
</div>

<div class="pageContent">

</div>

<div class="ui-state-highlight ui-corner-all" id="retourAjax"></div>