<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	// OBLIGATOIRE, id du projet à charger
	if (isset($_POST['projectID']))
		$idProj = $_POST['projectID'];
	else die('Pas de projet à charger...');

	try {
		$d = new Dailies($idProj, DAILIES_MAX_WEEKS);
		$dailies = $d->getDailies();
		$summary = $d->getSummary();
	}
	catch(Exception $e) { die('ERREUR : '.$e->getMessage()); }

	$thisWeekDateDisp = getweekStartEndDays(date('W'), date('Y'));
?>

<script>
	var addCommentWip			= false;
	var project_ID				= <?php echo $idProj; ?>;
	var actualWeek = loadedWeek	= "<?php echo date('Y_W'); ?>";

	$(function(){

		$('.bouton').button();

		var view = stageHeight - 70;
		$('.dailiesWeeks').slimScroll({
			position: 'right',
			height: view+'px',
			width: '613px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});

		// FILTRAGE des dailies
		$('.filterDaily').click(function() {
			if ($(this).hasClass('ui-state-activeFake')) {
				$('.daily').show();
				$('.filterDaily').removeClass('ui-state-activeFake ui-state-focus');
				return;
			}
			var group = $(this).attr('filter');
			$('.daily').hide();
			$('.daily_'+group).show();
			$('.filterDaily').removeClass('ui-state-activeFake');
			$(this).addClass('ui-state-activeFake');
		});

		// Affichage des autres semaines
		$('.weekHeader').click(function(){
			if ($(this).hasClass('ui-state-active'))
				return;
			$('.weekHeader').removeClass('ui-state-active').addClass('ui-state-hover');
			$(this).removeClass('ui-state-hover').addClass('ui-state-active');
			$('.weekContent').hide(transition);
			$(this).next('.weekContent').show(transition);

			var week = $(this).attr('week');
			$('#commentList').load('modals/dailies_summary_week.php', {'projectID':project_ID,'week':week});
		});

		// Ajout de commentaire à la discussion de la semaine
		$('#btn_addComment').click(function(){
			if (addCommentWip) return;
			if (actualWeek !== loadedWeek) return false;
			addCommentWip = true;
			$('#commentList').prepend(addCommentDiv());
			$('.btnM').button();
			$('#btn_addComment').addClass('ui-state-activeFake');
		});

		// Clic sur un shot
		$('.goto_shots').click(function(){
			var idProj = $(this).attr('projID');
			var idSeq = $(this).attr('seqID');
			var idShot = $(this).attr('shotID');
			var department = $(this).attr('dept');
			var template = $(this).attr('template');
			$('#selectDeptsList').val('shots').change();
			setTimeout(function(){
				loadPageContentModal('../modals/structure/structure_shots', {dept: department, template: template, projectID: idProj, sequenceID: idSeq, shotID: idShot});
			}, 1000);
		});

		// Clic sur un asset
		$('.goto_assets').click(function(){
			var nameAsset = $(this).attr('nameAsset');
			var pathAsset = $(this).attr('pathAsset');
			var idProj	  = $(this).attr('projID');
			localStorage['openAssetPath_'+idProj] = pathAsset;
			$('.myAsset').removeClass('ui-state-active');
			$('#selectDeptsList').val('assets').change();
			setTimeout(function(){
				$('.assetItem[filename="'+nameAsset+'"]').click();
			}, 1000);
		});

		// Clic sur une scène
		$('.goto_scenes').click(function(){
			var sceneID = $(this).attr('sceneID');
			$('#selectDeptsList').val('scenes').change();
		});

		// Clic sur un BANK
		$('.goto_other').click(function(){
			$('#racine_depts').find('.deptBtn[label="bank"]').click();
		});

	});

	function retourAjaxDailies (datas) {
		if (datas.error == 'OK') {
			$('#retourAjax').html(datas.message).addClass('ui-state-highlight').show(transition);
			setTimeout(function(){$('#retourAjax').fadeOut(transition, function(){$('#retourAjax').html('');});}, 3000);
			$('#racine_depts').find('.deptBtn[label="daylies"]').click();
		}
		else {
			$('#retourAjax').html('<b>'+datas.message+'</b>').addClass('ui-state-error').show(transition);
			setTimeout(function(){$('#retourAjax').fadeOut(transition*10, function(){$('#retourAjax').html('').removeClass('ui-state-error');});}, 3000);
		}
	}
</script>

<div class="stageContent pad5">
	<div id="dailiesList">
		<div class="rightText marge5bot marge10r" title="filter">
	<!--		<div class="inline mid colorDiscret">Show only ></div>-->
			<div class="inline mid mini">
				<button class="bouton filterDaily" title="toggle show / hide assets" filter="assets"><?php echo L_ASSETS; ?></button>
				<button class="bouton filterDaily" title="toggle show / hide scenes" filter="scenes"><?php echo L_SCENES; ?></button>
				<button class="bouton filterDaily" title="toggle show / hide shots" filter="shots"><?php echo L_SHOTS; ?></button>
				<button class="bouton filterDaily" title="toggle show / hide other actions" filter="other"><?php echo L_OTHERS; ?></button>
			</div>
		</div>
		<div class="dailiesWeeks" style="width:613px;">
			<div class="week">
				<div class="ui-state-active ui-corner-top pad5 marge5bot marge10r weekHeader doigt" week="<?php echo date('Y_W'); ?>">
					<div class="floatR colorMid"><?php echo $thisWeekDateDisp['start'].'-'.$thisWeekDateDisp['end']; ?></div>
					<?php echo L_THIS_WEEK; ?> <span class="colorMid mini">(n°<?php echo date('W'); ?>)</span>
				</div>
				<div class="weekContent">
		<?php
		$currWeek = $prevWeek = date('W'); $w = 0; $item = 0; $prevIdent = 'none';
		if (is_array($dailies)) :
		foreach ($dailies as $daily) :
			if (!$daily['show']) { continue; }
			$item++;
			switch ($daily['groupClass']) {
				case 'scenes' : $icone = 'ui-icon-clipboard'; break;
				case 'assets' : $icone = 'ui-icon-image'; break;
				case 'shots'  : $icone = 'ui-icon-copy'; break;
				case 'other'  : $icone = 'ui-icon-info'; break;
			}
			$week = date('W', $daily['time']);
			$year = date('Y', $daily['time']);
			$weekDateDisp = getweekStartEndDays($week, $year);
			if ($week != $prevWeek) :
				if ($w == 0 && $item == 1)
					echo '<p class="ui-state-disabled marge10l">'.L_NOTHING . ' ' . strtolower(L_DAILY).'...</p>';
				$weekStr = ($week == $currWeek-1) ? L_LAST_WEEK .' <span class="colorMid mini">(n°'.$week.')</span>' : L_WEEK .' n°'. $week;
				$prevWeek = $week;
				$w++; ?>
				</div>
			</div>
			<div class="week">
				<div class="ui-state-hover ui-corner-top pad5 marge5bot marge10r weekHeader doigt" week="<?php echo date('Y').'_'.$week; ?>">
					<div class="floatR colorMid" title="last action's date for this week"><?php echo $weekDateDisp['start'].'-'.$weekDateDisp['end']; ?></div>
					<?php echo $weekStr; ?>
				</div>
				<div class="weekContent hide">
			<?php endif; ?>
					<div class="petit fondSect1 ui-corner-all marge10l marge10r marge5bot daily daily_<?php echo $daily['groupClass']; ?>">
						<div class="floatL pad5 fondSect1 doigt goto_<?php echo $daily['groupClass']; ?>"
							 title="click to GO!"
							 <?php foreach ($daily['link'] as $attr => $val) echo ' '.$attr.'="'.$val.'"'; ?>>
							<img src="<?php echo $daily['vignette']; ?>" width="90" height="50" />
						</div>
						<div class="fondPage ui-corner-tr pad3">
							<div class="floatR colorSoft marge10r">
								<div class="inline mid"><span class="ui-icon ui-icon-person"></span></div>
								<div class="inline mid"><?php echo $daily['user']; ?></div>
								<div class="inline mid"><span class="colorDiscret"> | <?php echo $daily['date']; ?></span></div>
							</div>
							<div class="inline mid marge10l"><span class="ui-icon <?php echo $icone; ?>"></span></div>
							<div class="inline mid"><span class="colorMid"><?php echo $daily['group']; ?></span></div>
						</div>
						<div class="pad3 colorMid" style="min-height:30px;">
							<div class="floatR rightText" style="width:100px; margin-left:5px;">
								<?php echo $daily['icons']; ?>&nbsp;
							</div>
							<?php echo $daily['message']; ?>
						</div>
						<div class="fixFloat"></div>
					</div>
		<?php endforeach;
			else: ?>
				<p class="ui-state-disabled marge10l"><?php echo L_NOTHING . ' ' . strtolower(L_DAILY); ?></p>
		<?php endif;?>
				</div>
			</div>
		</div>
	</div>

	<div id="dailiesDiscut">
		<div class="marge10l big">
			<div class="floatR pico margeTop10 marge10r">
				<?php if ($_SESSION['user']->isSupervisor() || $_SESSION['user']->isDemo()) : ?>
				<div class="bouton" title="Add something to this week's discussion." id="btn_addComment"><span class="ui-icon ui-icon-plusthick"></span></div>
				<?php endif; ?>
			</div>
			<div class="inline colorDiscret terra" id="weekIndicator"><?php echo strtoupper(L_THIS_WEEK); ?></div>
			<div class="inline colorDiscret micro marge10l" id="weekIndicatorDays">(<?php echo $thisWeekDateDisp['start'].'-'.$thisWeekDateDisp['end']; ?>)</div>
			<div class="fixFloat"></div>
		</div>
		<div class="marge10l margeTop10 fondSect3 ui-corner-all pad5" style="height:85%;" id="commentList">
			<?php include('dailies_summary_week.php'); ?>
			<!--<pre><?php print_r($summary); ?></pre>-->
		</div>
	</div>
</div>