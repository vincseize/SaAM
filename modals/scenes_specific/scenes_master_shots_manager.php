<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('dates.php');

	if (isset($_POST['sceneID']))
		$sceneID = $_POST['sceneID'];
	else die('Scène indéfinie...');

	if (isset($_POST['idProj']))
		$idProj = $_POST['idProj'];
	else die('Projet indéfini...');

	extract($_POST);

try {
	$ACL = new ACL($_SESSION['user']);
	if (!$ACL->check('SCENES_ADMIN')) die('<div class="marge5 ui-state-error ui-corner-all pad5">Acces denied.</div>');

	$p			= new Projects($idProj);
	$teamAll	= $p->getEquipe();
	$titleProj	= $p->getTitleProject();
	// Récupère la liste des séquences et des shots du projet
	$projSeqs	= $p->getSequences(true);
	$projShots	= $p->getShots('all', 'actifs');

	$sc = new Scenes($sceneID);
	if ($sc->getMaster() > 0) die('<div class="marge5 ui-state-error ui-corner-all pad5">This manager is for MASTER scenes only.</div>');
	$sceneTitle = $sc->getSceneInfos(Scenes::TITLE);
	$sceneLabel = $sc->getSceneInfos(Scenes::LABEL);
	$sceneShots = $sc->getShots();
	$filles		= $sc->getDerivatives(true);
	$nbDeriv	= count($filles);
	$nbDerivA	= count($sc->getDerivatives());
	$nextDerivL	= '#'.substr($sceneLabel, 1).'_D_'.sprintf('%03d', $nbDerivA+1);

	$jsSceneShots = json_encode($sceneShots);

?>
<script>

	shotsInitList  = JSON.decode('<?php echo $jsSceneShots; ?>');
	var scMID = <?php echo $sceneID; ?>;

	$(function(){
//		console.log(shotsInitList);
		$('.bouton').button();
		$('.inputCal').datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true});
		$('.masterAddDeriv_supervisor, .masterAddDeriv_lead').selectmenu({style:'dropdown'});
		$('.masterAddDeriv_team').multiselect({height: '200px', minWidth: 206, selectedList: 2, noneSelectedText: '<?php echo L_NOBODY; ?>', selectedText: '# artists', checkAllText: ' ', uncheckAllText: ' '});


		// Click sur master overview
		$('#masterShotsOverview').click(function(){
			$('.filleShotsList').show(transition);
			$('.shotsSceneFilleItem').removeClass('colorHard').addClass('colorBtnFake');
		});
		// Click sur une dérivée à gauche
		$('#derivList').off('click', '.shotsSceneFilleItem');
		$('#derivList').on('click', '.shotsSceneFilleItem', function(){
			var sceneID = $(this).attr('sceneID');
			$('.shotsSceneFilleItem').removeClass('colorHard').addClass('colorBtnFake');
			$(this).removeClass('colorBtnFake').addClass('colorHard');
			$('.filleShotsList').hide();
			$('.filleShotsList[sceneID="'+sceneID+'"]').show(transition/2);
		});

		// Ajout de dérivée rapidos
		$('#masterAddDerivBtn').click(function(){
			$(this).hide();
			$('#masterAddDerivDiv').show(transition);
		});

		// Bouton valide ajout derivée
		$('#masterAddDerivValid').click(function(){
			var infos = getMasterAddDerivValues();
			var ajaxStr = 'action=addScene&projID='+idProj+'&infos='+encodeURIComponent(JSON.encode(infos));
			AjaxJson(ajaxStr, 'depts/scenes_actions', retourAjaxModalSM, 'addDeriv');
			$('#masterAddDerivDiv').hide(transition);
			$('#masterAddDerivBtn').show(transition);
		});

		// Bouton cancel ajout derivée
		$('#masterAddDerivCancel').click(function(){
			$('#masterAddDerivDiv').hide(transition);
			$('#masterAddDerivBtn').show(transition);
		});

		// Show Assignation de shot à une fille
		$('#shotsList').off('click', '.masterAssignShotFilleBtn');
		$('#shotsList').on('click', '.masterAssignShotFilleBtn', function(){
			$('.masterAssignShotDiv').hide();
			$('.masterAssignShotFilleBtn').show();
			var $filleDiv = $(this).parents('.filleShotsList').find('.masterAssignShotDiv');
			var $modele = $('#masterAssignShotModele').clone(true);
			$modele.show().appendTo($filleDiv);
			$filleDiv.find('.masterAssignSceneSeq, .masterAssignSceneShot').selectmenu({style:'dropdown'});
			$('.bouton').button();
			$(this).hide();
			$filleDiv.show(transition);
		});

		// Choix de la séquence
		$('#shotsList').off('change','.masterAssignSceneSeq');
		$('#shotsList').on('change','.masterAssignSceneSeq', function(){
			var idSeq = $(this).val();
			if (idSeq == '0') return;
			$(this).parents('.filleShotsList').find('.masterAssignSceneShotDiv').hide();
			$(this).parents('.filleShotsList').find('.masterAssignSceneShotDiv[seqID="'+idSeq+'"]').show();
			$(this).parents('.filleShotsList').find('.masterNoSeqMsg').hide();
		});

		// Choix du shot
		$('#shotsList').off('change','.masterAssignSceneShot');
		$('#shotsList').on('change','.masterAssignSceneShot', function(){
			var value = $(this).val();
			if (value == '0') return;
			$('.masterAssignShotValid').show();
		});

		// Bouton valide assignation shot
		$('#shotsList').off('click', '.masterAssignShotValid');
		$('#shotsList').on('click', '.masterAssignShotValid', function(){
			var filleID = $(this).parents('.filleShotsList').attr('sceneID');
			var idSeq   = $(this).parents('.filleShotsList').find('.masterAssignSceneSeq').val();
			var idShot  = $(this).parents('.filleShotsList').find('.masterAssignSceneShotDiv[seqID="'+idSeq+'"]').find('.masterAssignSceneShot').val();
			var ajaxStr = 'action=assignShot&projID='+idProj+'&sceneID='+filleID+'&seqID='+idSeq+'&shotID='+idShot;
			AjaxJson(ajaxStr, 'depts/scenes_actions', retourAjaxModalSM, 'addShot');
			$('.masterAssignShotDiv').hide(transition, function(){ $(this).html(''); });
			$('.masterAssignShotFilleBtn').show();
		});

		// Bouton cancel assignation shot
		$('#shotsList').off('click', '.masterAssignShotCancel');
		$('#shotsList').on('click', '.masterAssignShotCancel', function(){
			$('.masterAssignShotDiv').hide(transition, function(){ $(this).html(''); });
			$('.masterAssignShotFilleBtn').show();
		});

		// Dé-assignation de shot
		$('#shotsList').off('click', '.sceneFilleRemoveAssignedShot');
		$('#shotsList').on('click', '.sceneFilleRemoveAssignedShot', function(){
			var idSeq  = $(this).parents('.sceneShotLine').attr('seqID');
			var idShot = $(this).parents('.sceneShotLine').attr('shotID');
			var filleID = $(this).parents('.sceneShotLine').attr('filleID');
			var ajaxStr = 'action=removeShot&projID='+idProj+'&sceneID='+filleID+'&seqID='+idSeq+'&shotID='+idShot;
			AjaxJson(ajaxStr, 'depts/scenes_actions', retourAjaxModalSM, 'removeShot');
		});
	});

	// Récupère les valeurs lors de l'ajout de scène fille
	function getMasterAddDerivValues () {
		return {
			<?php echo Scenes::MASTER; ?>		: scMID,
			<?php echo Scenes::TITLE; ?>		: $('#masterAddDerivDiv').find('.masterAddDeriv_title').val(),
			<?php echo Scenes::LABEL; ?>			: $('#masterAddDerivDiv').find('.masterAddDeriv_label').html(),
			<?php echo Scenes::SUPERVISOR; ?>	: $('#masterAddDerivDiv').find('.masterAddDeriv_supervisor').val(),
			<?php echo Scenes::LEAD; ?>			: $('#masterAddDerivDiv').find('.masterAddDeriv_lead').val(),
			<?php echo Scenes::TEAM; ?>			: $('#masterAddDerivDiv').find('.masterAddDeriv_team').val(),
			<?php echo Scenes::DATE; ?>			: $('#masterAddDerivDiv').find('.masterAddDeriv_date').datepicker("getDate"),
			<?php echo Scenes::DEADLINE; ?>		: $('#masterAddDerivDiv').find('.masterAddDeriv_deadline').datepicker("getDate"),
			<?php echo Scenes::DESCRIPTION; ?>	: $('#masterAddDerivDiv').find('.masterAddDeriv_description').val()
		};
	}

	var timerAlertSM;
	function retourAjaxModalSM (datas, type) {
		clearTimeout(timerAlertSM);
		if (datas.error == 'OK') {
			$('#retourAjaxModal').html('<b>'+datas.message+'</b>').removeClass('ui-state-error').addClass('ui-state-highlight').show(transition);
			if (type == 'addDeriv') {
				$('#derivList').append('<div class="margeTop10 doigt colorBtnFake shotsSceneFilleItem" title="'+datas.sceneTitle+'" sceneID="'+datas.sceneID+'">'+datas.sceneTitle+'</div>');
				$('#shotsList').append(
					'<div class="margeTop5 filleShotsList fondSect4" sceneID="'+datas.sceneID+'">'
						+'<div class="inline mid margeTop10 pad3 marge5bot nano" style="width: 120px;">'
							+'<button class="bouton marge10r masterAssignShotFilleBtn" title="Assign shots...">'
								+'<span class="inline mid terra"><?php echo L_ASSIGN.' '.L_SHOTS; ?></span>'
								+'<span class="inline mid ui-icon ui-icon-copy"></span>'
							+'</button>'
						+'</div>'
						+'<div class="inline mid margeTop10 marge5bot colorBtnFake">'+datas.sceneTitle+'</div>'
						+'<div class="fondSect4 pad5 marge5bot hide masterAssignShotDiv"></div>'
					+'</div>');
				$('.bouton').button();
				var oldLabel = $('.masterAddDeriv_label').html();
				var oldTitle = $('.masterAddDeriv_title').val();
				var newNum	 = (1e4+(parseInt(oldLabel.split('_').pop()) + 1)+"").slice(-3);
				var regX = new RegExp('D_[0-9]{3}','g');
				var newLabel = oldLabel.replace(regX, 'D_'+newNum);
				var newTitle = oldTitle.replace(regX, 'D_'+newNum);
				$('.masterAddDeriv_label').html(newLabel);
				$('.masterAddDeriv_title').val(newTitle);
				var derivCount = parseInt($('#masterCountDeriv').html());
				$('#masterCountDeriv').html(derivCount+1);
			}
			else if (type == 'addShot') {
				$('#shotsList').find('.filleShotsList[sceneID="'+datas.filleID+'"]').append(
					'<div class="ui-state-default sceneShotLine" filleID="'+datas.filleID+'" seqID="'+datas.shotInfos.ID_sequence+'" shotID="'+datas.shotInfos.id+'">'
						+'<div class="floatR marge10 nano" style="padding-top:3px;" title="Remove assignation">'
							+'<button class="bouton sceneFilleRemoveAssignedShot"><span class="ui-icon ui-icon-trash"></span></button>'
						+'</div>'
						+'<div class="inline mid"><img src="'+datas.vignetteShot+'" width="80" height="45" /></div> '
						+'<div class="inline mid w300 colorHi" title="Shot title">'
							+' '+datas.shotInfos.title+' '
							+'<span class="colorDiscret petit" title="Shot label">('+datas.shotInfos.label+')</span>'
						+'</div> '
						+'<div class="inline mid colorMid marge10r" title="Sequence title">'+datas.seqTitle+'</div> '
					+'</div>');
//				$('.filleShotsList[sceneID="'+datas.filleID+'"]').hide();
				$('.bouton').button();
			}
			else if (type == 'removeShot')
				$('.sceneShotLine[shotID="'+datas.shotID+'"]').remove();
			else if (type == 'close')
				$('#managersModal').dialog('close');
			timerAlertSM = setTimeout(function(){$('#retourAjaxModal').fadeOut(transition*2, function(){$('#retourAjaxModal').html('').removeClass('ui-state-error');});}, 4000);
		}
		else {
			$('#retourAjaxModal').html('<div class="marge10l doigt floatR" onClick="fermeRAM()"><span class="ui-icon ui-icon-close"></span></div><b>'+datas.message+'</b>').addClass('ui-state-error').show(transition);
		}
	}
	function fermeRAM () {
		$('#retourAjaxModal').hide();
	}

</script>


<div class="inline top demi bordBankSection">
	<div class="ui-state-default pad3 gras colorMid center"><span id="masterCountDeriv"><?php echo $nbDeriv; ?></span> <?php echo mb_convert_case(L_DERIVATIVES, MB_CASE_UPPER); ?></div>
	<div class="padH5" id="derivList">
		<div class="fondSect4 pad5 hide" id="masterAddDerivDiv">
			<div class="inline mid w100"></div>
			<div class="inline mid colorMid marge5bot">Create a derivative</div>
			<br />
			<div class="inline mid w100 colorSoft margeTop1">Scene title</div>
			<div class="inline mid w300 margeTop1" title="Scene title">
				<input type="text" class="noBorder pad3 ui-corner-all fondSect3 w100p masterAddDeriv_title"
					   value="#<?php echo substr($sceneTitle, 1, strlen(NOMENCLATURE_SCENES)+3); ?>_D_<?php printf('%03d', $nbDerivA+1); ?>_<?php echo substr($sceneTitle, strlen(NOMENCLATURE_SCENES)+5); ?>" />
			</div>
				<br />
				<div class="inline mid w100 colorSoft margeTop5">Scene label</div>
				<div class="inline mid w200 margeTop5" title="Scene label">
					&nbsp;<span class="colorSoft masterAddDeriv_label"><?php echo $nextDerivL; ?></span>
				</div>
				<br />
				<div class="inline mid w100 colorSoft margeTop5">Supervisor</div>
				<div class="inline mid w300 margeTop5 mini" title="Scene supervisor">
					<select class="masterAddDeriv_supervisor" style="width:297px;">
						<option disabled selected>none</option>
						<?php foreach($teamAll as $idM=>$nameM):
							$usr = new Users($idM);
							if(!$usr->isSupervisor()) continue; ?>
							<option value="<?php echo $idM; ?>"><?php echo $nameM; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<br />
				<div class="inline mid w100 colorSoft margeTop1">Lead</div>
				<div class="inline mid w300 margeTop1 mini" title="Scene lead">
					<select class="masterAddDeriv_lead" style="width:297px;">
						<option disabled selected>none</option>
						<?php foreach($teamAll as $idM=>$nameM):
							$usr = new Users($idM);
							if(!$usr->isLead()) continue; ?>
							<option value="<?php echo $idM; ?>"><?php echo $nameM; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<br />
				<div class="inline mid w100 colorSoft margeTop1">Team</div>
				<div class="inline mid w300 margeTop1 mini" title="Scene team">
					<select class="masterAddDeriv_team w300" multiple="multiple">
						<?php foreach($teamAll as $idM=>$nameM): ?>
							<option value="<?php echo $idM; ?>"><?php echo $nameM; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<br />
				<div class="inline mid w100 colorSoft margeTop1">Dates start</div>
				<div class="inline mid w300 margeTop1" title="Scene dates (start, end)">
					<input type="text" class="noBorder pad3 ui-corner-all fondSect3 inputCal masterAddDeriv_date" style="width:126px;" value="<?php echo date(DATE_FORMAT); ?>" /> <span class="colorSoft">end</span>
					<input type="text" class="noBorder pad3 ui-corner-all fondSect3 inputCal masterAddDeriv_deadline" style="width:126px;" value="<?php echo SQLdateConvert($sc->getSceneInfos(Scenes::DEADLINE)); ?>" />
				</div>
				<br />
				<div class="inline top w100 colorSoft margeTop5">Description</div>
				<div class="inline top w300 margeTop1" title="Scene description">
					<textarea class="noBorder pad3 ui-corner-all fondSect3 w100p masterAddDeriv_description" rows="4"></textarea>
				</div>
				<br />
			<div class="inline mid w100"></div>
			<div class="inline mid w300 margeTop5 nano rightText">
				<button class="bouton" id="masterAddDerivValid"><span class="ui-icon ui-icon-check"></span></button>
				<button class="ui-state-error bouton" id="masterAddDerivCancel"><span class="ui-icon ui-icon-cancel"></span></button>
			</div>
		</div>
		<div class="padH5 nano">
			<button class="bouton" title="Add a Derivative" id="masterAddDerivBtn">
				<span class="inline mid terra">Create derivative</span>
				<span class="inline mid ui-icon ui-icon-plusthick doigt"></span>
			</button>
		</div><?php
		if($nbDeriv >= 1): ?>
			<div class="margeTop5 doigt colorErrText" id="masterShotsOverview">MASTER overview</div>
		<?php foreach($filles as $filleID => $fille): ?>
			<div class="margeTop10 doigt colorBtnFake shotsSceneFilleItem" title="<?php echo $fille[Scenes::TITLE]; ?>" sceneID="<?php echo $filleID; ?>">
				<?php echo $fille[Scenes::TITLE]; ?>
			</div>
		<?php endforeach;
		else: ?>
			<div class="margeTop5 doigt ui-state-disabled">No derivatives yet.</div>
		<?php endif; ?>
	</div>
</div><div class="inline top demi">
	<div class="ui-state-default pad3 gras colorMid center"><?php echo mb_convert_case(L_SHOTS. ' ' .L_DERIVATIVES, MB_CASE_UPPER); ?></div>
	<div class="padV5" id="shotsList"><?php
		if($nbDeriv >= 1):
			foreach($filles as $filleID => $fille): ?>
				<div class="margeTop10 filleShotsList fondSect4" sceneID="<?php echo $filleID; ?>">
					<div class="inline mid margeTop5 pad3 marge5bot nano" style="width: 120px;">
						<button class="bouton masterAssignShotFilleBtn" title="Assign shots...">
							<span class="inline mid terra"><?php echo L_ASSIGN.' '.L_SHOTS; ?></span>
							<span class="inline mid ui-icon ui-icon-copy"></span>
						</button>
					</div>
					<div class="inline mid margeTop5 marge5bot colorBtnFake"><?php echo $fille[Scenes::TITLE]; ?></div>
					<div class="fondSect4 pad5 marge5bot hide masterAssignShotDiv"></div>
					<?php
					if (is_array(@$sceneShots[$filleID])):
						foreach($sceneShots[$filleID] as $shotID):
							$sh = new Shots($shotID);
							$shot = $sh->getShotInfos();
							$seqID = (int)$shot[Shots::SHOT_ID_SEQUENCE];
							$se = new Sequences($seqID);
							$seq = $se->getSequenceInfos();
							$vignette = check_shot_vignette_ext($idProj, $seq[Sequences::SEQUENCE_LABEL], $shot[Shots::SHOT_LABEL]);
							?>
							<div class="ui-state-default sceneShotLine" filleID="<?php echo $filleID; ?>" seqID="<?php echo $seqID; ?>" shotID="<?php echo $shotID; ?>" title="<?php echo $fille[Scenes::TITLE]; ?>">
								<div class="floatR marge10 nano" style="padding-top:3px;" title="Remove assignation">
									<button class="bouton sceneFilleRemoveAssignedShot"><span class="ui-icon ui-icon-trash"></span></button>
								</div>
								<div class="inline mid"><img src="<?php echo $vignette; ?>" width="80" height="45" /></div>
								<div class="inline mid w300 colorHi" title="Shot title">
									<?php echo $shot[Shots::SHOT_TITLE]; ?>
									<span class="colorDiscret petit" title="Shot label">(<?php echo $shot[Shots::SHOT_LABEL]; ?>)</span>
								</div>
								<div class="inline mid colorMid marge10r" title="Sequence title"><?php echo $seq[Sequences::SEQUENCE_TITLE]; ?></div>
							</div>
				<?php endforeach;
				endif; ?>
			</div>
		<?php endforeach;
		else:  ?>
			<div class="margeTop5 doigt ui-state-disabled">Create a derivative to assign a shot.</div>
		<?php endif; ?>
	</div>
</div>


<div class="center" style="position:absolute; top:0px; width:95%;"><div class="inline pad5 ui-corner-all" id="retourAjaxModal"></div></div>



<div class="hide" id="masterAssignShotModele">
	<div class="inline mid w100"></div>
	<div class="inline mid colorMid marge5bot"><?php echo L_ASSIGNMENTS.' '.L_SHOT; ?></div>
	<br />
	<div class="inline mid w100 colorSoft margeTop1"><?php echo L_SEQUENCE; ?></div>
	<div class="inline mid margeTop1" title="<?php echo L_SEQUENCE; ?>">
		<select class="w200 masterAssignSceneSeq">
			<option value="0" disabled selected>Choose a sequence</option>
			<?php
			if (is_array($projSeqs)):
				foreach($projSeqs as $seq): ?>
					<option value="<?php echo $seq[Sequences::SEQUENCE_ID_SEQUENCE]; ?>"><?php echo $seq[Sequences::SEQUENCE_TITLE]; ?></option> <?php
				endforeach;
			endif; ?>
		</select>
	</div>
	<br />
	<div class="inline mid w100 colorSoft margeTop1"><?php echo L_SHOT; ?></div>
	<div class="inline mid margeTop1" title="<?php echo L_SHOT; ?>">
		<span class="ui-state-disabled masterNoSeqMsg">Choose a sequence before</span>
		<?php
		if (is_array($projSeqs)):
			foreach($projSeqs as $seqID => $seq): ?>
			<div class="masterAssignSceneShotDiv hide" seqID="<?php echo $seqID; ?>">
				<select class="w200 masterAssignSceneShot">
					<option value="0" disabled selected>Choose a shot (<?php echo $seq[Sequences::SEQUENCE_TITLE]; ?>)</option><?php
					if (is_array($projSeqs)):
						foreach($projShots as $shot):
							if($shot[Shots::SHOT_ID_SEQUENCE] != $seqID) continue; ?>
						<option value="<?php echo $shot[Shots::SHOT_ID_SHOT]; ?>"><?php echo $shot[Shots::SHOT_TITLE]; ?></option>
						<?php endforeach;
					endif; ?>
				</select>
			</div>
		<?php endforeach;
		endif; ?>
	</div>
	<br />
	<div class="inline mid w100"></div>
	<div class="inline mid w200 margeTop5"></div>
	<div class="inline mid margeTop5 nano rightText">
		<button class="bouton hide masterAssignShotValid"><span class="ui-icon ui-icon-check"></span></button>
		<button class="ui-state-error bouton masterAssignShotCancel"><span class="ui-icon ui-icon-cancel"></span></button>
	</div>
</div>

<?php
}
catch(Exception $e) { echo('<div class="marge5 ui-state-error ui-corner-all pad5">'.$e->getMessage().'</div>'); }
?>