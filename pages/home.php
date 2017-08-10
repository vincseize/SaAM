<?php
	@session_start();
	require_once ("../inc/checkConnect.php" );

	$calendar = json_decode(file_get_contents('../'.CALENDAR_JSON_DATAFILE), true);
	$userProjects = $_SESSION['user']->getUserProjects();
	unset($_SESSION['active_project_id']);
?>
<script>
	var calendarDataFile = '<?php echo CALENDAR_JSON_DATAFILE; ?>';
	var idLastEvent = <?php echo count($calendar); ?>;
	var projectShowed = [<?php $JSprojList=''; foreach($userProjects as $projID) $JSprojList .= $projID.', '; $JSprojList = substr($JSprojList, 0, -2); echo $JSprojList; ?>];
</script>

<div class="headerStage" help="SaAM_home_navigation">
	<div class="floatR hide" id="calendarNav">
		<div class="inline top nano" id="calendarPrevWeek" title="semaine précédente"><button class="bouton"><span class="ui-icon ui-icon-carat-1-w"></span></button></div>
		<div class="inline top nano" id="calendarToday" title="aujourd'hui"><button class="bouton"><span class="ui-icon ui-icon-arrowreturnthick-1-s"></span></button></div>
		<div class="inline top nano" id="calendarNextWeek" title="semaine suivante"><button class="bouton"><span class="ui-icon ui-icon-carat-1-e"></span></button></div>
	</div>
	<div class="inline deptBtn colorSoft" type="home" content="overview" active><?php echo L_GLOBAL_VIEW;?></div>
	<div class="inline deptBtn colorSoft" type="home" content="news"><?php echo strtolower(L_NEWS); ?></div>
	<div class="inline deptBtn colorSoft" type="home" content="agenda"><?php echo strtolower(L_CALENDAR); ?></div>
	<div class="inline deptBtn colorSoft" type="home" ><a href="<?php echo INTRANET_URL;?>" target="_blank" title='intranet'>intranet</a></div>
</div>

<div class="ui-corner-all" id="retourAjax"></div>

<div class="pageContent noscroll"></div>