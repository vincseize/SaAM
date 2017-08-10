<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('dates.php' );

	if (!($_SESSION['user']->isSupervisor() || $_SESSION['user']->isDemo())) die('projects schedule: Access denied.');

	$l = new Liste();
	if ($_SESSION['user']->isDemo())
		$l->addFiltre(Projects::PROJECT_TYPE, "=", 'demo');
	else
		$l->addFiltre(Projects::PROJECT_TYPE, "!=", 'demo');
	$l->addFiltre(Projects::PROJECT_ARCHIVE, "=", '0');
	$l->addFiltre(Projects::PROJECT_HIDE, "=", '0');
//	$l->addFiltre(Projects::PROJECT_ID_PROJECT, "=", '308');
	$project_list = $l->getListe(TABLE_PROJECTS,'*', 'date');

	$inList = Array();	$outList = Array();
	foreach($project_list as $proj) {
		$inList[]  = SQLdateConvert($proj[Projects::PROJECT_DATE], 'object');
		$outList[] = SQLdateConvert($proj[Projects::PROJECT_DEADLINE], 'object');
	}
	$firstPoint = min($inList);
	$lastPoint  = max($outList);
	$lenghtStamp = $lastPoint->format('U') - $firstPoint->format('U');

	$periodYear = new DatePeriod($firstPoint, new DateInterval('P1Y'), $lastPoint);
	$periodMois = new DatePeriod($firstPoint, new DateInterval('P1M'), $lastPoint);
	$periodDays = new DatePeriod($firstPoint, new DateInterval('P1D'), $lastPoint->add(new DateInterval('P1D')));
	$countY = 0; $countM = 0; $countD = 0;
	foreach($periodYear as $y) $countY++;
	foreach($periodMois as $m) $countM++;
	foreach($periodDays as $d) $countD++;
	$widthSepYears = 0;
	$widthSepMois  = 0;
	$widthSepDays  = 100 / $countD;
?>

<script>
	$(function(){
		$('.bouton').button();

		$('.projectLine').click(function(){
			var projID = $(this).attr('projID');
			localStorage['activeBtn_'+projID] = "03_gantt" ;
			localStorage['lastDept_'+projID] = "gantt";
			localStorage['lastGroupDepts_'+projID] = "racine";
			localStorage['lastDept_'+projID+'_GRP_racine'] = "gantt";
			openProjectTab(projID);
		});
	});
</script>
<script src="js/gantt.js"></script>

<div class="floatR mini marge10r margeTop10">
	<button class="bouton" title="zoom in" id="zoomTimeIn"><span class="ui-icon ui-icon-zoomin"></span></button>
	<button class="bouton" title="global view" id="zoomTimeAll"><span class="ui-icon ui-icon-arrowthick-2-e-w"></span></button>
	<button class="bouton" title="zoom out" id="zoomTimeOut"><span class="ui-icon ui-icon-zoomout"></span></button>
</div>

<h2 class="marge10l">Calendrier des projets</h2>


<div class="center" id="schedule">
	<div class="ui-state-default colorDiscret2 curMove leftText" id="calTimeline">
		<?php
//		echo $widthSepDays;
		$affYear = $offset = false;
		foreach ($periodMois as $date) :
			if ($date->format('m') == '01' && $widthSepYears > 2):
				?><div class="timeCol yearCol" style="width: <?php echo $widthSepYears; ?>%;">
						<div class="timeSep fondSect3"></div>
						<?php echo (int)$date->format('Y') - 1; ?>
				</div><?php
				$widthSepYears = (int)$date->format('t') * $widthSepDays;
				$affYear = true;
			else:
				if ($affYear == false && $offset == false) {
					$widthSepYears += ((int)$date->format('t') - (int)$date->format('d')) * $widthSepDays +0.1;
					$offset = true;
				}
				else
					$widthSepYears += (int)$date->format('t') * $widthSepDays;
			endif;
		endforeach;
		if (!$affYear):
			?><div class="timeCol" style="width: 100%;">
				<?php echo $firstPoint->format('Y'); ?>
			</div><?php
		endif; ?>
		<br />
		<?php
		$affMois = false;
		foreach ($periodDays as $date) :
			if ($date->format('d') == '01' && $widthSepMois > 2):
				?><div class="timeCol moisCol" style="width: <?php echo $widthSepMois; ?>%;">
					<div class="timeSep fondSect3"></div><?php
					$date->sub(new DateInterval('P1M'));
					echo $date->format('M');?>
				</div><?php
				$widthSepMois = $widthSepDays;
				$affMois = true;
			else:
				$widthSepMois += $widthSepDays;
			endif;
		endforeach;
		if (!$affMois):
			?><div class="timeCol" style="width: 100%;">
				<?php echo $firstPoint->format('M'); ?>
			</div><?php
		endif; ?>
		<br />
		<?php
		foreach ($periodDays as $date) :
			$show = ($widthSepDays > 2) ? 'display:inline-block;' : 'display:none;';
			?><div class="timeCol daysCol" style="width: <?php echo $widthSepDays; ?>%; <?php echo $show; ?>">
					<div class="timeSep fondSect3"></div>
					<?php echo $date->format('d'); ?>
			</div><?php
		endforeach;?>
	</div>

	<div class="fondSect4" id="calContent">
		<?php
		foreach($project_list as $proj) :
			$TSin  = SQLdateConvert($proj[Projects::PROJECT_DATE], 'timeStamp');
			$TSout = SQLdateConvert($proj[Projects::PROJECT_DEADLINE], 'timeStamp');
			$dateIn  = SQLdateConvert($proj[Projects::PROJECT_DATE]);
			$dateOut = SQLdateConvert($proj[Projects::PROJECT_DEADLINE]);
			$cssLeft  = round(($TSin-$firstPoint->format('U')) / $lenghtStamp * 100, 1);
			$cssRight = 100 - round(($TSout-$firstPoint->format('U')) / $lenghtStamp * 100, 1);
			$prod = $proj[Projects::PROJECT_COMPANY];
			$rea  = $proj[Projects::PROJECT_DIRECTOR];
			$sup  = $proj[Projects::PROJECT_SUPERVISOR]; ?>
			<br />
			<div class="ui-state-focusFake ui-corner-all shadowOut pad5 doigt calLine projectLine"
				 title="<?php echo "PROD: $prod&#10;Director: $rea&#10;Supervisor: $sup&#10;Start: $dateIn&#10;End: $dateOut"; ?>"
				 style="margin-left: <?php echo $cssLeft; ?>%; margin-right: <?php echo $cssRight; ?>%;"
				 projID="<?php echo $proj[Projects::PROJECT_ID_PROJECT]; ?>">
				<div class="floatL colorDiscret hide datesEvent"><?php echo $dateIn; ?></div>
				<div class="floatR colorDiscret hide datesEvent"><?php echo $dateOut; ?></div>
				<div class="center">
					<?php echo preg_replace('/_/', ' ', $proj[Projects::PROJECT_TITLE]); ?>
				</div>
			</div>
		<?php endforeach; ?>
			<br />
	</div>
</div>