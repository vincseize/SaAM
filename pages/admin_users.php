<?php
@session_start();
require_once ("../inc/checkConnect.php" );

try {
$acl = new ACL(@$_SESSION['user']); ?>

<div class="headerStage">
	<div class="floatR" id="filtres" title="Filter by level">
		<div class="inline top petit" style="display:none;" id="filtreContent">
			<input type='checkbox' class="filter" value="visitor" id='flt2' /><label for='flt2'><?php echo L_VISITOR; ?></label>
			<input type='checkbox' class="filter" value="artist" id='flt3' /><label for='flt3'><?php echo L_ARTIST; ?></label>
			<input type='checkbox' class="filter" value="lead" id='flt4' /><label for='flt4'><?php echo L_LEAD; ?></label>
			<input type='checkbox' class="filter" value="supervisor" id='flt5' /><label for='flt5'><?php echo L_SUPERVISOR; ?></label>
			<input type='checkbox' class="filter" value="prod" id='flt6' /><label for='flt6'><?php echo L_DIR_PROD; ?></label>
			<input type='checkbox' class="filter" value="magic" id='flt7' /><label for='flt7'><?php echo L_MAGIC; ?></label>
			<input type='checkbox' class="filter" value="dev" id='flt8' /><label for='flt8'><?php echo L_DEVELOPPER; ?></label>
		</div>
		<div class="inline top nano" id="filtreToggle"><button class="bouton"><span class="ui-icon ui-icon-gear"></span></button></div>
		<div class="inline top" style="margin-top: 4px;" id="searchUsers">
			<span class="inline top ui-icon ui-icon-search doigt showSearch" id="usrSearchBtn" title="Search within users login, pseudo or name"></span>
			<input type="text" class="inline top noBorder ui-corner-all fondSect3 w150 searchInput" style="display:none;" id="usrSearchInput" />
			<span class="inline top ui-icon ui-icon-circle-zoomin doigt submitSearch" style="display:none;" id="usrSearchSubmit"></span>
		</div>
	</div>
	<div class="inline deptBtn colorSoft" type="admins" content="user_list" active><?php echo L_LIST; ?></div>
	<?php if($acl->check("ADMIN_USERS_ADD")): ?>
		<div class="inline deptBtn colorSoft" type="admins" content="user_add"><?php echo L_ADD; ?></div>
	<?php endif; ?>
	<div class="fixFloat"></div>
</div>

<div class="ui-corner-all" id="retourAjax"></div>

<div class="pageContent"></div><?php
}
catch (Exception $e) { die('<span class="colorErreur">'. $e->getMessage().'</span>'); }
?>