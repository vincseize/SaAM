<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('dates.php' );

	$id_project = $_POST['projectID'];

	$p = new Projects($id_project);
	$titleProj = $p->getTitleProject();


	$l = new Liste();
	$l->addFiltre(Sequences::SEQUENCE_ID_PROJECT, "=", $id_project);
	$l->addFiltre(Sequences::SEQUENCE_ARCHIVE, "=", '0');
	$l->addFiltre(Sequences::SEQUENCE_HIDE, "=", '0');
	$sequences_list = $l->getListe(TABLE_SEQUENCES,'*', 'date');
	$l->resetFiltre();
	$l->addFiltre(Shots::SHOT_ID_PROJECT, "=", $id_project);
	$l->addFiltre(Shots::SHOT_ARCHIVE, "=", '0');
	$l->addFiltre(Shots::SHOT_HIDE, "=", '0');
	$shots_list = $l->getListe(TABLE_SHOTS,'*', 'date');

	$l->resetFiltre();
	$l->getListe(TABLE_USERS);
	$usersList = $l->simplifyList();
	$projStart = SQLdateConvert($p->getProjectInfos(Projects::PROJECT_DATE), 'object');
	$projStop  = SQLdateConvert($p->getProjectInfos(Projects::PROJECT_DEADLINE), 'object');

	$inList  = Array($projStart);
	$outList = Array($projStop);
	foreach($sequences_list as $seq) {
		$inList[]  = SQLdateConvert($seq[Sequences::SEQUENCE_DATE], 'object');
		$outList[] = SQLdateConvert($seq[Sequences::SEQUENCE_DEADLINE], 'object');
	}
	$firstPoint = min($inList);
	$lastPoint  = max($outList);
	$lenghtStamp = $lastPoint->format('U') - $firstPoint->format('U');

	$cssProjLeft  = round(($projStart->format('U') - $firstPoint->format('U')) / $lenghtStamp * 100, 1);
	$cssProjRight = 100 - round(($projStop->format('U') - $firstPoint->format('U')) / $lenghtStamp * 100, 1);

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

<script src="js/gantt.js"></script>
<script>
	$(function(){
		$('.bouton').button();

		var view = stageHeight - 28 - 45;
		$('.stageContent').slimScroll({
			position: 'right',
			height: view+'px',
			width: '100%',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});

		$('.projectLine').click(function(){
			$('.shotLine').hide(transition, function(){
				$('.sequenceLine').show(transition);
			});
			$('.sequenceLine').removeAttr('opened');
			setTimeout(refreshDatesEventShow, transition);
		});

		$('.sequenceLine').click(function(){
			var seqID = $(this).attr('sequenceID');
			if (!$(this).attr('opened')) {
				$('.sequenceLine[sequenceID!="'+seqID+'"]').hide(transition);
				$('.sequenceLine[sequenceID="'+seqID+'"]').show();
				$('.shotLine').hide();
				$('.shotLine[seqID="'+seqID+'"]').show(transition);
				$(this).attr('opened', 'open');
			}
			else {
				$('.shotLine').hide(transition, function(){
					$('.sequenceLine').show(transition);
				});
				$(this).removeAttr('opened');
			}
			setTimeout(refreshDatesEventShow, transition);
		});
	});
</script>



<div class="floatR mini marge10r margeTop10">
	<button class="bouton" title="zoom in" id="zoomTimeIn"><span class="ui-icon ui-icon-zoomin"></span></button>
	<button class="bouton" title="global view" id="zoomTimeAll"><span class="ui-icon ui-icon-arrowthick-2-e-w"></span></button>
	<button class="bouton" title="zoom out" id="zoomTimeOut"><span class="ui-icon ui-icon-zoomout"></span></button>
</div>

<h2 class="marge10l"><?php echo $titleProj; ?> Gantt</h2>



<div class="stageContent">
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
			<div class="ui-state-highlight colorBtnFake ui-corner-all fleche gray-layer pad3 calLine projectLine"
				 title="<?php echo "Start: ".$projStart->format(DATE_FORMAT)."&#10;End: ".$projStop->format(DATE_FORMAT).""; ?>"
				 style="margin-left: <?php echo $cssProjLeft; ?>%; margin-right: <?php echo $cssProjRight; ?>%;"
				 projectID="<?php echo $id_project; ?>">
				<div class="floatL colorDiscret hide datesEvent"><?php echo $projStart->format(DATE_FORMAT); ?></div>
				<div class="floatR colorDiscret hide datesEvent"><?php echo $projStop->format(DATE_FORMAT); ?></div>
				<div class="center">
					<?php echo preg_replace('/_/', ' ', $titleProj); ?>
				</div>
			</div>
			<?php
			foreach($sequences_list as $seq) :
				$TSin  = SQLdateConvert($seq[Sequences::SEQUENCE_DATE], 'timeStamp');
				$TSout = SQLdateConvert($seq[Sequences::SEQUENCE_DEADLINE], 'timeStamp');
				$dateIn  = SQLdateConvert($seq[Sequences::SEQUENCE_DATE]);
				$dateOut = SQLdateConvert($seq[Sequences::SEQUENCE_DEADLINE]);
				$cssLeft  = round(($TSin-$firstPoint->format('U')) / $lenghtStamp * 100, 1);
				$cssRight = 100 - round(($TSout-$firstPoint->format('U')) / $lenghtStamp * 100, 1);
				$label = $seq[Sequences::SEQUENCE_LABEL];
				$lead  = $seq[Sequences::SEQUENCE_LEAD];
				$sup   = @$usersList[$seq[Sequences::SEQUENCE_SUPERVISOR]]['pseudo'];
				?>
				<div class="marge5bot"></div>
				<div class="ui-state-focusFake ui-corner-all shadowOut pad5 doigt calLine sequenceLine"
					 title="<?php echo "ID: #".$seq[Sequences::SEQUENCE_ID_SEQUENCE]."&#10;LABEL: $label&#10;Lead: $lead&#10;Supervisor: ".$sup."&#10;Start: $dateIn&#10;End: $dateOut"; ?>"
					 style="margin-left: <?php echo $cssLeft; ?>%; margin-right: <?php echo $cssRight; ?>%;"
					 sequenceID="<?php echo $seq[Sequences::SEQUENCE_ID_SEQUENCE]; ?>">
					<div class="floatL colorDiscret hide datesEvent"><?php echo $dateIn; ?></div>
					<div class="floatR colorDiscret hide datesEvent"><?php echo $dateOut; ?></div>
					<div class="center">
						<?php echo preg_replace('/_/', ' ', $seq[Sequences::SEQUENCE_TITLE]); ?>
					</div>
				</div>
				<div class="marge5bot"></div>
				<?php
				foreach($shots_list as $shot) :
					if ($shot[Shots::SHOT_ID_SEQUENCE] != $seq[Sequences::SEQUENCE_ID_SEQUENCE])
						continue;
					$eventStart = SQLdateConvert($shot[Shots::SHOT_DATE], 'object');
					$dateIn     = $eventStart->format(DATE_FORMAT);
					$eventStop  = SQLdateConvert($shot[Shots::SHOT_DEADLINE], 'object');
					$dateOut    = $eventStop->format(DATE_FORMAT);
					if (($eventStart < $projStart || $eventStart > $projStop) && ($eventStop < $projStart || $eventStop > $projStop)) : ?>
						<div class="leftText marge15bot doigt hide calLine shotLine"
							 title="<?php echo $shot[Shots::SHOT_TITLE]; ?>"
							 seqID="<?php echo $shot[Shots::SHOT_ID_SEQUENCE]; ?>"
							 shotID="<?php echo $shot[Shots::SHOT_ID_SHOT]; ?>">
							<div class="inline ui-state-error ui-corner-right pad3">
								<div class="inline mid"><span class="ui-icon ui-icon-arrowthick-1-w"></span></div><div class="inline mid"><span class="ui-icon ui-icon-help"></span></div>
							</div>
						</div>
						<div class="marge5bot"></div>
						<?php continue;
					endif;
					$cssLeft  = round(($eventStart->format('U') - $firstPoint->format('U')) / $lenghtStamp * 100, 1);
					$cssRight = 100 - round(($eventStop->format('U') - $firstPoint->format('U')) / $lenghtStamp * 100, 1);
					$label = $shot[Shots::SHOT_LABEL];
					$lead  = @$usersList[$shot[Shots::SHOT_LEAD]]['pseudo'];
					$sup   = @$usersList[$shot[Shots::SHOT_SUPERVISOR]]['pseudo'];
					?>
					<div class="ui-state-activeFake ui-corner-all shadowOut pad5 doigt hide calLine shotLine"
						 title="<?php echo "ID_SEQ: #".$shot[Shots::SHOT_ID_SEQUENCE]."&#10;LABEL: $label&#10;Lead: $lead&#10;Supervisor: ".$sup."&#10;Start: $dateIn&#10;End: $dateOut"; ?>"
						 style="margin-left: <?php echo $cssLeft; ?>%; margin-right: <?php echo $cssRight; ?>%;"
						 seqID="<?php echo $shot[Shots::SHOT_ID_SEQUENCE]; ?>"
						 shotID="<?php echo $shot[Shots::SHOT_ID_SHOT]; ?>">
						<div class="floatL colorSoft hide datesEvent"><?php echo $dateIn; ?></div>
						<div class="floatR colorSoft hide datesEvent"><?php echo $dateOut; ?></div>
						<div class="center">
							<?php echo preg_replace('/_/', ' ', $shot[Shots::SHOT_TITLE]); ?>
						</div>
					</div>
					<div class="marge5bot"></div>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>