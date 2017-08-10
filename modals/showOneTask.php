<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH'].'/inc/checkConnect.php' );

extract($_POST);

try {
	$t = new Tasks((int)$taskID);
	$task = $t->getTaskInfos();
	$taskMessages = $t->getMessages();

	// Récup de vignette éventuelle
	switch($task[Tasks::SECTION_TASK]) {
		case Tasks::SECTION_ASSETS:
			$sectionL	= L_ASSET;
			$pathAsset	= $task[Tasks::HOOKED_ENTITY_TASK][Assets::ASSET_PATH_REL];
			$nameAsset	= $task[Tasks::HOOKED_ENTITY_TASK][Assets::ASSET_NAME];
			$vignette	= check_asset_vignette($pathAsset, $nameAsset, $idProj, true);
			$entityName = $nameAsset;
			break;
		case Tasks::SECTION_SCENES:
			$sectionL	= L_SCENE;
			$idScene	= $task[Tasks::HOOKED_ENTITY_TASK][Scenes::ID_SCENE];
			$vignette	= check_scene_vignette($idScene, true);
			$entityName = $task[Tasks::HOOKED_ENTITY_TASK][Scenes::TITLE];
			break;
		case Tasks::SECTION_SHOTS:
			$sectionL	= L_SHOT;
			$labelSeq	= Sequences::getLabelSequence((int)$task[Tasks::HOOKED_ENTITY_TASK][Shots::SHOT_ID_SEQUENCE]);
			$labelShot	= $task[Tasks::HOOKED_ENTITY_TASK][Shots::SHOT_LABEL];
			$vignette	= check_shot_vignette_ext($idProj, $labelSeq, $labelShot);
			$entityName = $task[Tasks::HOOKED_ENTITY_TASK][Shots::SHOT_TITLE];
			break;
		default:
			$sectionL	= L_ROOT;
			$p = new Projects($idProj);
			$titleProj = $p->getTitleProject();
			$vignette	= check_proj_vignette_ext($idProj, $titleProj, true);
			$entityName = '';
	}
	$usersProject = Users::getUsers_by_projects(Array((int)$idProj), 9);

	$l = new Liste();
	$allTypes = array_filter(array_unique($l->getListe(TABLE_TASKS, Tasks::TYPE_TASK)));
	sort($allTypes);
	$allTypes = implode(',', $allTypes);
}
catch (Exception $e) {
	die('<pre class="colorErreur">'.$e->getMessage().'</pre>');
} ?>

<script>
	var addMessageWip = false;
	var idTask = <?php echo $taskID; ?>;
	$(function(){
		$('.inputCal').datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true});
		$('.modTask[key="<?php echo Tasks::ASSIGNEE_TASK; ?>"]').multiselect({noneSelectedText: '<i>Nobody</i>', selectedText: '# users', selectedList: 3, checkAllText: ' ', uncheckAllText: ' '});

		var availableTypes = "<?php echo $allTypes; ?>";
		$('.modTask[key="<?php echo Tasks::TYPE_TASK; ?>"]').autocomplete({
			source: availableTypes.split(',')
		});
		$('#modalModTask').dialog('destroy');

		// Ajout de message à la retake
		$('#btn_addMessage').click(function(){
			if (addMessageWip) return;
			addMessageWip = true;
			$('#messagesList').prepend(addMessageDiv());
			$('.btnM').button();
			$('#btn_addMessage').addClass('ui-state-activeFake');
		});

		// Validation de l'ajout de message
		$('#messagesList').off('click', '#addMessageValid');
		$('#messagesList').on('click', '#addMessageValid', function(){
			var messageTxt = $('#addMessageTxt').val();
			if (messageTxt == '') { alert('message vide !'); return; }
			addMessageWip = false;
			var ajaxReq = 'action=addMessage&idProj='+project_ID+'&idTask='+idTask+'&texte='+encodeURIComponent(messageTxt);
			console.log(ajaxReq);
			AjaxJson(ajaxReq, 'tasks', retourAjaxMsg, true);
		});

		// Annulation de l'ajout de message
		$('#messagesList').off('click', '#addMessageAnnul');
		$('#messagesList').on('click', '#addMessageAnnul', function(){
			addMessageWip = false;
			$('#addMessageDiv').remove();
			$('#btn_addMessage').removeClass('ui-state-activeFake');
		});

		// Suppression de message
		$('.delMessage').click(function(){
			if (!confirm('Delete this message ?')) return;
			var idComm = $(this).attr('idM');
			var ajaxReq = 'action=deleteMessage&idComm='+idComm+'&idTask='+idTask;
			AjaxJson(ajaxReq, 'tasks', retourAjaxMsg, true);
		});
	});

function addMessageDiv(mode) {
	var widthTxt = '90%';
	var margeTxt = '';
	var classBtn = '';
	if (mode !== undefined && mode == 'reponseMode') {
		widthTxt = '75%';
		margeTxt = 'marge30l';
		classBtn = 'responseMode';
	}
	return '<div class="margeTop5" id="addMessageDiv">'
		+	'<textarea class="ui-corner-all ui-corner-all fondSect3 noBorder pad3 '+margeTxt+'" style="width:'+widthTxt+'; resize:none;" rows="6" id="addMessageTxt"></textarea>'
		+	'<div class="nano rightText">'
		+		'<div class="inline top pad3 giant ui-state-disabled marge10r">Écrivez votre message, puis validez -></div>'
		+		'<button class="btnM '+classBtn+'" id="addMessageValid"><span class="ui-icon ui-icon-check" title="valider"></span></button>'
		+		'<button class="btnM '+classBtn+'" id="addMessageAnnul"><span class="ui-icon ui-icon-cancel" title="annuler"></span></button>'
		+	'</div>'
		+'</div>';
}
</script>

<div class="fondSect1 fleche ui-corner-tl" style="min-height: 150px;">
	<div class="floatR noMarge noPad doigt taskVignette" style="min-width: 270px;">
		<img src="<?php echo $vignette; ?>" />
	</div>
	<div class="floatR marge10">
		<div class="gros">
			<span class="ui-state-active pad3" title="<?php echo L_TYPE; ?>"><?php echo $task[Tasks::TYPE_TASK]; ?></span>
		</div>
		<div class="margeTop10">
			<span class="colorDiscret" title="Task #<?php echo $task[Tasks::ID_TASK]; ?>"><?php echo $sectionL.' '.$entityName; ?></span>
		</div>
		<div class="margeTop10">
			<div class="inline mid ui-state-default noBorder noBG"><span class="ui-icon ui-icon-clock"></span></div>
			<div class="inline mid" title="<?php echo L_START; ?>"><?php echo SQLdateConvert($task[Tasks::START_TASK]); ?></div><br />
			<div class="inline mid ui-state-error noBorder noBG"><span class="ui-icon ui-icon-clock"></span></div>
			<div class="inline mid colorErrText" title="<?php echo L_END; ?>"><?php echo SQLdateConvert($task[Tasks::END_TASK]); ?></div>
		</div>
		<div class="nano margeTop10">
			<button class="bouton changeTaskStatus" newStatus="1" title="<?php echo L_CURRENT; ?>" <?php echo ($task[Tasks::STATUS_TASK] == 1) ? 'disabled' : ''; ?>><span class="ui-icon ui-icon-arrowthickstop-1-e"></span></button>
			<button class="bouton changeTaskStatus" newStatus="2" title="<?php echo L_STANDBY; ?>" <?php echo ($task[Tasks::STATUS_TASK] == 2) ? 'disabled' : ''; ?>><span class="ui-icon ui-icon-power"></span></button>
			<button class="bouton changeTaskStatus" newStatus="3" title="<?php echo L_DONE; ?>" <?php echo ($task[Tasks::STATUS_TASK] == 3) ? 'disabled' : ''; ?>><span class="ui-icon ui-icon-check"></span></button>
			<?php $disabledMod = ($task[Tasks::STATUS_TASK] == 3) ? 'disabled' : ''; ?>
			<button class="bouton openModTask marge10l" title="<?php echo L_MODIFY; ?>" <?php echo $disabledMod; ?>><span class="ui-icon ui-icon-pencil"></span></button>
			<button class="bouton deleteTask" title="<?php echo L_DELETE; ?>"><span class="ui-icon ui-icon-trash"></span></button>
		</div>
	</div>
	<div class="pad10 gros" title="<?php echo L_TITLE; ?>">
		<span class="ui-state-error pad3"><?php echo $task[Tasks::STATUS_TASK_READABLE]; ?></span>
		<?php echo $task[Tasks::TITLE_TASK]; ?>
	</div>
	<div class="padV10 colorMid">
		<?php echo L_FROM; ?> <b><?php echo $task[Tasks::CREATOR_TASK]; ?></b> <?php echo mb_convert_case(L_TO, MB_CASE_LOWER); ?>
		<?php foreach($task[Tasks::ASSIGNEE_TASK] as $to): ?>
			<span class="fondSect4 marge10r pad3 padV5 ui-corner-all"><?php echo $to.' ';?></span>
		<?php endforeach; ?>
	</div>
	<div class="pad10 margeTop5 colorBtnFake gros" title="<?php echo L_DESCRIPTION; ?>">
		<?php echo nl2br($task[Tasks::DESCRIPTION_TASK]); ?>
	</div>
</div>

<div class="pad10 fondSect4" style="height: 300px;">
	<div class="">
		<div class="inline mid enorme colorDiscret">
			<?php echo L_MESSAGES; ?>
		</div>
		<div class="inline mid nano marge10l">
			<button class="bouton" title="<?php echo L_ADD_MESSAGE; ?>" id="btn_addMessage"><span class="ui-icon ui-icon-plusthick"></span></button>
		</div>
	</div>
	<div id="messagesList"><?php
	if (is_array($taskMessages) && count($taskMessages)>0) :
		foreach ($taskMessages as $idM=>$message) :
			$avatarMsg = check_user_vignette_ext(@$message['senderId'], $message['senderLogin']); ?>
			<div class="messageBlock" idMessage="<?php echo $idM; ?>">
				<div class="ui-corner-all fondSect3 messageRetake">
					<div class="ui-corner-top fondPage colorMid">
						<table class="w100p">
							<tr>
								<td class="w80"><?php
									if ($_SESSION['user']->getUserInfos(Users::USERS_ID) == $message['senderId']): ?>
										<span class="inline ui-icon ui-icon-trash doigt delMessage" idM="<?php echo $idM; ?>" title="<?php echo L_DELETE; ?>"></span><?php
									endif; ?>
								</td>
								<td>
									<div class="inline mid"><img src="<?php echo $avatarMsg; ?>" height="12" /></div>
									<div class="inline mid"><?php echo $message['sender']; ?></div>
									<span class="mini" style="color:#383838;"><?php echo $_SESSION['STATUS_LIST'][(int)$message['senderStatus']]; ?></span>
								</td>
								<td class="w100 rightText" title="<?php echo substr($message['date'],10,-3); ?>"><?php echo SQLdateConvert($message['date'], 'messages'); ?></td>
							</tr>
						</table>
					</div>
					<div class="pad5"><?php echo nl2br(stripslashes($message['comment'])); ?></div>
				</div>
			</div><?php
		endforeach;
	else: ?>
		<div class="ui-corner-all pad5 fondSect3 messageRetake">Aucun message.</div><?php
	endif; ?>
	</div>
</div>

<div class="pad10 fondSect2" style="min-height: 130px;">
	<div class="enorme colorDark"><?php echo L_STANDBY; ?></div>
</div>

<div class="hide" id="modalModTask">
	<div class="inline mid w100"><?php echo L_TITLE; ?></div>
	<div class="inline mid w300">
		<input type="text" class="noBorder ui-corner-all pad3 w100p fondSect2 modTask" key="<?php echo Tasks::TITLE_TASK ?>" value="<?php echo $task[Tasks::TITLE_TASK]; ?>" placeholder="<?php echo L_TITLE; ?>" />
	</div>
	<br />
	<div class="inline top w100"><?php echo L_DESCRIPTION; ?></div>
	<div class="inline top w300">
		<textarea class="noBorder ui-corner-all pad3 w100p fondSect2 modTask" key="<?php echo Tasks::DESCRIPTION_TASK ?>" rows="5" placeholder="<?php echo L_DESCRIPTION; ?>"><?php echo $task[Tasks::DESCRIPTION_TASK]; ?></textarea>
	</div>
	<br />
	<div class="inline mid w100"><?php echo L_TYPE; ?></div>
	<div class="inline mid w300">
		<input type="text" class="noBorder ui-corner-all pad3 w100p fondSect2 modTask" key="<?php echo Tasks::TYPE_TASK; ?>" value="<?php echo $task[Tasks::TYPE_TASK]; ?>" placeholder="<?php echo L_TYPE; ?>" />
	</div>
	<br /><br />
	<div class="inline mid w100"><?php echo L_START; ?>, <?php echo L_END; ?></div>
	<div class="inline mid w300">
		<input type="text" class="noBorder ui-corner-all pad3 w100 fondSect2 inputCal modTask" key="<?php echo Tasks::START_TASK; ?>" value="<?php echo SQLdateConvert($task[Tasks::START_TASK]); ?>" placeholder="<?php echo L_START; ?>" />
		<input type="text" class="noBorder ui-corner-all pad3 w100 fondSect2 inputCal modTask" key="<?php echo Tasks::END_TASK; ?>"   value="<?php echo SQLdateConvert($task[Tasks::END_TASK]); ?>" placeholder="<?php echo L_END; ?>" />
		<input type="hidden" class="modTask" key="<?php echo Tasks::ID_TASK; ?>" value="<?php echo $task[Tasks::ID_TASK]; ?>" />
		<input type="hidden" class="modTask" key="<?php echo Tasks::ID_PROJECT_TASK; ?>" value="<?php echo $idProj; ?>" />
		<input type="hidden" class="modTask" key="<?php echo Tasks::SECTION_TASK; ?>" value="<?php echo $task[Tasks::SECTION_TASK]; ?>" />
	</div>
	<br /><br />
	<div class="inline top w100"><?php echo L_ASSIGNMENTS.' '.L_USERS; ?></div>
	<div class="inline top w300">
		<div class="inline top">
			<select class="modTask" key="<?php echo Tasks::ASSIGNEE_TASK; ?>" multiple="multiple"><?php
				foreach($usersProject as $usr) :
					$selected = (in_array($usr[Users::USERS_PSEUDO], $task[Tasks::ASSIGNEE_TASK])) ? 'selected' : '';?>
					<option value="<?php echo $usr[Users::USERS_ID]; ?>" <?php echo $selected; ?>><?php echo $usr[Users::USERS_PSEUDO]; ?></option><?php
				endforeach; ?>
			</select>
		</div>
	</div>
</div>