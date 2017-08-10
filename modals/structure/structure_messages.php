<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('dates.php');
	require_once ('vignettes_fcts.php');

	if (isset($_POST['shotID']))
		$idShot = $_POST['shotID'];
	else die('Plan indéfini...');

	if (isset($_POST['dept']))
		$dept = $_POST['dept'];
	else die('Département indéfini...');
	try {
		$deptInfos = new Infos(TABLE_DEPTS);
		$deptInfos->loadInfos('label', $dept);
		$idDept = $deptInfos->getInfo('id');
		if (!$idDept) {
			$idDept = $_POST['deptID'];
			$deptInfos->loadInfos('id', $idDept);
		}
	}
	catch (Exception $e) { $idDept = $dept; }

	if (isset($_POST['retakeNum']))
		$retakeNum = $_POST['retakeNum'];
	else $retakeNum = false;

	if ($retakeNum)
		$retakeNumStr = sprintf('%03d',$retakeNum);
	else $retakeNumStr = L_CURRENT;

	try {
		$cl = new CommentsList('retake', $idShot, $idDept);
		$messages = $cl->getComments($retakeNum);
	}
	catch (Exception $e) {
		echo 'ERREUR : '.$e->getMessage();
		die();
	}

?>

<script>
	var addMessageWip = false;
	<?php if (isset($_POST['authAdd'])) : ?>
		var authAddMessage = <?php echo $_POST['authAdd']; ?>;
	<?php else: ?>
		var authAddMessage = !authAddRetake;
	<?php endif; ?>

	$(function(){

		if (isLocked)
			$('.addReponse, .delMessage').addClass('ui-state-disabled');

		var shotViewH = $('#shotViewLeft').height();
		$('#messagesList').slimScroll({
			position: 'right',
			height: shotViewH+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});

		// affiche le numéro de la retake en haut de liste messages
		$('#numRetakeMessages').html("<?php echo $retakeNumStr ?>");

		// Si la retake est validée (donc si on peut en ajouter une nouvelle), on empêche l'ajout de messages
		if (!authAddMessage) {
			$('#btn_addMessage').hide();
			$('#messagesList').addClass('ui-state-disabled');
		}
		else {
			$('#btn_addMessage').show();
			$('#messagesList').removeClass('ui-state-disabled');
		}

		// Ajout de message à la retake
		$('#btn_addMessage').click(function(){
			if (isLocked) {
				$('#retourAjax').html('This shot is locked.').addClass('ui-state-error').show(transition);
				setTimeout(function(){$('#retourAjax').fadeOut(transition, function(){$('#retourAjax').html('');});}, 1000);
				return;
			}
			if (!authAddMessage) return;
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
			var repTo = 'false';
			if ($(this).hasClass('responseMode'))
				repTo = $(this).parents('.messageBlock').attr('idMessage');
			var ajaxReq = 'action=addMessage&projID='+project_ID+'&shotID='+shot_ID+'&dept='+id_dept+'&texte='+encodeURIComponent(messageTxt)+'&reponse='+repTo;
			AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, 'reloadShot');
		});

		// Annulation de l'ajout de message
		$('#messagesList').off('click', '#addMessageAnnul');
		$('#messagesList').on('click', '#addMessageAnnul', function(){
			addMessageWip = false;
			$('#addMessageDiv').remove();
			$('#btn_addMessage').removeClass('ui-state-activeFake');
		});


		// Ajout d'une réponse à un message
		$('.addReponse').click(function(){
			if (isLocked) {
				$('#retourAjax').html('This shot is locked.').addClass('ui-state-error').show(transition);
				setTimeout(function(){$('#retourAjax').fadeOut(transition, function(){$('#retourAjax').html('');});}, 1000);
				return;
			}
			if (!authAddMessage) return;
			if (addMessageWip) return;
			addMessageWip = true;
//			$(this).parents('.messageBlock').prepend(addMessageDiv('reponseMode'));
			$(addMessageDiv('reponseMode')).insertAfter($(this).parents('.messageBlock').children().first());
			$('.btnM').button();
			$('#btn_addMessage').addClass('ui-state-activeFake');
		});

		// Suppression de message
		$('.delMessage').click(function(){
			if (isLocked) {
				$('#retourAjax').html('This shot is locked.').addClass('ui-state-error').show(transition);
				setTimeout(function(){$('#retourAjax').fadeOut(transition, function(){$('#retourAjax').html('');});}, 1000);
				return;
			}
			if (!confirm('Delete this message ?')) return;
			var idComm = $(this).attr('idM');
			var ajaxReq = 'action=deleteMessage&idComm='+idComm+'&shotID='+shot_ID;
			AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, 'reloadShot');
		});
	});

	function addMessageDiv(mode) {
		var widthTxt = '380px';
		var margeTxt = '';
		var classBtn = '';
		if (mode !== undefined && mode == 'reponseMode') {
			widthTxt = '330px';
			margeTxt = 'marge30l';
			classBtn = 'responseMode';
		}
		return '<div class="margeTop5" id="addMessageDiv">'
			+	'<textarea class="ui-corner-all ui-corner-all fondSect3 noBorder pad3 '+margeTxt+'" style="width:'+widthTxt+'; resize:none;" rows="6" id="addMessageTxt"></textarea>'
			+	'<div class="inline bot nano">'
			+		'<p class="pad3 enorme ui-state-disabled">Écrivez votre<br />message,<br />puis validez.</p>'
			+		'<button class="btnM '+classBtn+'" id="addMessageValid"><span class="ui-icon ui-icon-check" title="valider"></span></button>'
			+		'<button class="btnM '+classBtn+'" id="addMessageAnnul"><span class="ui-icon ui-icon-cancel" title="annuler"></span></button>'
			+	'</div>'
			+'</div>';
	}

</script>


<div id="messagesList">
	<?php

	if (is_array($messages) && count($messages)>0) {
		foreach ($messages as $idM=>$message) {
			$btnDelete = '';
			$avatarMsg = check_user_vignette_ext(@$message['senderId'], $message['senderLogin']);
			if ($_SESSION['user']->getUserInfos(Users::USERS_ID) == $message['senderId'] && !is_array(@$message['reponses']))
				$btnDelete = '<span class="inline ui-icon ui-icon-trash doigt delMessage" idM="'.$idM.'" title="'.L_DELETE.'"></span>';
			echo '<div class="messageBlock" idMessage="'.$idM.'">
					<div class="ui-corner-all fondSect3 messageRetake">
						<div class="ui-corner-top fondPage colorMid">
							<table class="w100p">
								<tr>
									<td class="w80">
										'.$btnDelete.'
										<span class="inline ui-icon ui-icon-mail-closed doigt addReponse" title="'.L_ANSWER.'"></span>
									</td>
									<td>
										<div class="inline mid"><img src="'.$avatarMsg.'" height="12" /></div>
										<div class="inline mid">'.$message['sender'].'</div> <span class="mini" style="color:#383838;">'.$_SESSION['STATUS_LIST'][(int)$message['senderStatus']].'</span>
									</td>
									<td class="w100 rightText" title="'.substr($message['date'],10,-3).'">'.SQLdateConvert($message['date'], 'messages').'</td>
								</tr>
							</table>
						</div>
						<div class="pad5">'.nl2br(stripslashes($message['comment'])).'</div>
					</div>';
			if (is_array(@$message['reponses'])) {
				foreach ($message['reponses'] as $idR=>$reponse) {
					$avatarRep = check_user_vignette_ext($reponse['senderId'],$reponse['senderLogin']);
					$btnRepDelete = '';
					if ($_SESSION['user']->getUserInfos(Users::USERS_ID) == $reponse['senderId'])
						$btnRepDelete = '<span class="inline ui-icon ui-icon-trash doigt delMessage" idM="'.$idR.'" title="'.L_DELETE.'"></span>';
					echo '<div class="ui-corner-all fondSect3 reponseRetake" idReponse="'.$idR.'">
							<div class="ui-corner-top fondPage colorMid">
								<table class="w100p">
									<tr>
										<td class="w80">
											<span class="ui-state-disabled inline ui-icon ui-icon-arrowreturnthick-1-e"></span>
											<span class="inline ui-icon ui-icon-mail-closed doigt addReponse" title="'.L_ANSWER.'"></span>
											'.$btnRepDelete.'
										</td>
										<td>
											<div class="inline mid"><img src="'.$avatarRep.'" height="12" /></div>
											<div class="inline mid">'.$reponse['sender'].'</div> <span class="mini" style="color:#383838;">'.$_SESSION['STATUS_LIST'][(int)$reponse['senderStatus']].'</span>
										</td>
										<td class="w100 rightText"title="'.substr($reponse['date'],10,-3).'">'.SQLdateConvert($reponse['date'], 'messages').'</td></tr>
								</table>
							</div>
							<div class="pad5">'.nl2br(stripslashes($reponse['comment'])).'</div>
						</div>';
				}
			}
			echo '</div>';
		}
	}
	else echo '<div class="ui-corner-all pad5 fondSect3 messageRetake">Aucun message.</div>';
	?>
</div>