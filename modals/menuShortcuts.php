<?php $uPs = ' '.$_SESSION['user']->getUserInfos(Users::USERS_PSEUDO) ; ?>

<div class="inline marge15bot" id="arboMenu"></div>

<div class="rightText" id="myMenuHead">
<?php if ($_SESSION['user']->isArtist() || $_SESSION['user']->isDemo()) : ?>
	<div class="inline top center ui-state-focus ui-corner-bottom padV5 doigt myMenuHeadEntry" menuLoad="my_tasks" help="vos_taches" title="<?php echo L_TASKS.' '.L_ASSIGNED_TO.$uPs; ?>">
		<span class="inline mid ui-icon ui-icon-tag"></span> <span class="inline mid"><?php echo L_MY_TASKS ?></span>
	</div>
        <div class="inline top center ui-state-focus ui-corner-bottom padV5 doigt myMenuHeadEntry" menuLoad="my_shots" help="vos_plans" title="<?php echo L_SHOTS.' '.L_ASSIGNED_TO.$uPs; ?>">
		<span class="inline mid ui-icon ui-icon-copy"></span> <span class="inline mid"><?php echo L_MY_SHOTS ?></span>
	</div>
	<div class="inline top center ui-state-default ui-corner-bottom padV5 doigt myMenuHeadEntry" menuLoad="my_scenes" help="vos_scenes" title="<?php echo L_SCENES.' '.L_ASSIGNED_TO.$uPs; ?>">
		<span class="inline mid ui-icon ui-icon-link"></span> <span class="inline mid"><?php echo L_MY_SCENES ?></span>
	</div>
	<div class="inline top center ui-state-default ui-corner-bottom padV5 doigt myMenuHeadEntry" menuLoad="my_assets" help="vos_assets" title="<?php echo L_ASSETS.' '.L_ASSIGNED_TO.$uPs; ?>">
		<span class="inline mid ui-icon ui-icon-image"></span> <span class="inline mid"><?php echo L_MY_ASSETS ?></span>
	</div>
	<div class="inline top center ui-state-default ui-corner-bottom padV5 doigt myMenuHeadEntry" menuLoad="my_notes" help="vos_notes" title="<?php echo L_NOTES.' '.L_ASSIGNED_TO.$uPs; ?>">
		<span class="inline mid ui-icon ui-icon-note"></span>
	</div>
<?php endif; ?>
</div>

<div id="myMenuWrapper">
	<div class="inline center" id="myMenu">
		<?php include('my_shots.php'); ?>
	</div>
</div>