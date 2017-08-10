<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once('directories.php');

// OBLIGATOIRE, id du projet à charger
if (isset($_POST['projectID']))
	$idProj = $_POST['projectID'];
else die('Pas de projet à charger...');

try {
	$p = new Projects($idProj);
	$dirFinal = FOLDER_DATA_PROJ.$p->getDirProject().'/final';
	// récupère la liste des messages final
	$cb = new CommentsList('final', $idProj);
	$messages = $cb->getComments();
	$BAtags = $_SESSION['user']->getUserTags(true);
}
catch (Exception $e) {
	echo 'ERREUR : '.$e->getMessage();
	die();
}
?>

<script>
	var dir = "<?php echo $dirFinal; ?>";
	var project_ID = <?php echo $idProj ?>;

	$(function(){
		$('.bouton').button();
		$('.watchSelector').buttonset();
		$('#watchSelectAll').buttonset();
		$('.forceDept').selectmenu({style: 'dropdown'});
		$('#forceDeptAll').selectmenu({style: 'dropdown'});
		$('#selectionHistory').selectmenu({style: 'dropdown'});
		$('#viewMode').selectmenu({style: 'dropdown'});
		$('.computeBtn, #saveSelectionToDisk, #loadSelectionFromDisk, #deleteSelectionFromDisk').addClass('ui-state-disabled');

		var msgH = stageHeight - 132 - 300;
		$('#messagesList').slimScroll({
			position: 'right',
			height: msgH+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});
		var seqH = stageHeight - 80;
		$('#viewContent').slimScroll({
			position: 'right',
			height: seqH+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		}).disableSelection();
	});
</script>
<script src="ajax/depts/dept_final.js"></script>

<div class="stageContent">
	<div class="colorSoft" id="deptNameBGBank"><?php echo L_FINAL; ?></div>

	<div class="ui-corner-all" id="retourAjax"></div>

	<div id="finalLeft">

		<div class="margeTop5 pad5" style="height: 35px;">
			<div class="inline mid" title="Choose type of view: by sequences or by tags.">
				<select id="viewMode" style="width:200px;">
					<option value="sequences">SEQUENCES</option>
					<option disabled>-------</option>
					<?php if (is_array($BAtags)) :
						foreach($BAtags as $tag): ?>
						<option value="<?php echo $tag; ?>">TAG <?php echo preg_replace('/^#FT_/', '', $tag); ?></option>
					<?php endforeach;
					endif;?>
				</select>
			</div>
			<div class="inline mid" title="Load a previously saved selection">
				<select id="selectionHistory" style="width:200px;">
					<option value="none" class="colorDiscret">Load selection</option>
					<option disabled>-------</option>
					<?php foreach(glob(INSTALL_PATH.$dirFinal.'/*.json') as $fileSel):
						$selName = preg_replace('/\.json$/', '', basename($fileSel)); ?>
						<option value="<?php echo $selName; ?>"><?php echo $selName; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="inline mid nano">
				<button class="bouton" id="loadSelectionFromDisk" title="Load choosen selection"><span class="ui-icon ui-icon-arrowthickstop-1-s"></span></button>
				<img src="gfx/ajax-loader-white.gif" id="loadSelWaiter" class="hide" style="margin: 0px 5.5px;" />
				<button class="bouton" id="saveSelectionToDisk" title="Save current selection"><span class="ui-icon ui-icon-disk"></span></button>
				<button class="bouton" id="deleteSelectionFromDisk" title="Delete choosen selection"><span class="ui-icon ui-icon-trash"></span></button>
			</div>
		</div>

		<div id="viewContent"><?php include('depts_shot_specific/dept_final_sequences_list.php'); ?></div>

	</div>

	<div id="finalRight">
		<div class="colorDark" style="height: 47px;">
			<div class="floatL margeTop10">
				<button class="bouton computeBtn" title="Compute final / Replay"><span class="ui-icon ui-icon-play"></span></button>
			</div>
			<div class="inline big gras marge10l"  id="dataResult">

			</div>
		</div>
		<div class="" id="finalVideo">
			<div class="colorSoft gros pad10 center" id="nothingToWatch">
				<p>Please choose <br />some sequences to watch.</p>
			</div>
			<div class="center hide" id="msgWait">Computing video, please wait... <img src="gfx/ajax-loader-white.gif" /></div>
			<video class="hide" height="300" id="footageResult" controls preload="none"></video>
		</div>

		<div class="fondSect4 margeTop5" id="finalMsgs">
			<div id="messagesList">
				<div class="micro margeTop10">
					<button class="bouton" title="<?php echo L_ADD_MESSAGE; ?>" id="btn_addMessage"><span class="ui-icon ui-icon-plusthick"></span></button>
				</div>
			<?php if (is_array(@$messages) && count(@$messages)>0) :
					foreach ($messages as $idM=>$message) :
						$avatarMsg = check_user_vignette_ext(@$message['senderId'], $message['senderLogin']);
						$btnDelete = '';
						if ($_SESSION['user']->getUserInfos('pseudo') == $message['sender'])
							$btnDelete = '<span class="inline ui-icon ui-icon-trash doigt delMessage" idM="'.$idM.'" title="supprimer"></span>'; ?>
						<div class="messageBlock" idMessage="<?php echo $idM; ?>">
							<div class="ui-corner-all fondSect3 messageFinal">
								<div class="ui-corner-top fondPage colorMid">
									<table class="w100p">
										<tr>
											<td class="w100">
												<?php echo $btnDelete; ?>
												<span class="inline ui-icon ui-icon-mail-closed doigt addReponse" title="repondre"></span>
											</td>
											<td>
												<div class="inline mid"><img src="<?php echo $avatarMsg; ?>" height="12" /></div>
												<div class="inline mid"><?php echo $message['sender']; ?></div>
												<span class="mini" style="color:#383838;"><?php echo $_SESSION['STATUS_LIST'][(int)$message['senderStatus']] ?></span>
											</td>
											<td class="w100 rightText" title="<?php echo substr($message['date'],10,-3); ?>"><?php echo SQLdateConvert($message['date'], 'messages'); ?></td>
										</tr>
									</table>
								</div>
								<div class="pad5"><?php echo nl2br(stripslashes($message['comment'])); ?></div>
							</div>
						<?php if (is_array(@$message['reponses'])) :
							foreach ($message['reponses'] as $idR=>$reponse) :
								$avatarRep = check_user_vignette_ext($reponse['senderId'],$reponse['senderLogin']);
								$btnRepDelete = '';
								if ($_SESSION['user']->getUserInfos('pseudo') == $reponse['sender'])
									$btnRepDelete = '<span class="inline ui-icon ui-icon-trash doigt delMessage" idM="'.$idR.'" title="supprimer"></span>'; ?>
								<div class="ui-corner-all fondSect3 reponseFinal" idReponse="<?php echo $idR; ?>">
									<div class="ui-corner-top fondPage colorMid">
										<table class="w100p">
											<tr>
												<td class="w100">
													<?php echo $btnRepDelete; ?>
													<span class="inline ui-icon ui-icon-mail-closed doigt addReponse" title="repondre"></span>
												</td>
												<td>
													<div class="inline mid"><img src="<?php echo $avatarRep; ?>" height="12" /></div>
													<div class="inline mid"><?php echo $reponse['sender']; ?></div>
													<span class="mini" style="color:#383838;"><?php echo $_SESSION['STATUS_LIST'][(int)$reponse['senderStatus']]; ?></span>
												</td>
												<td class="w100 rightText" title="<?php substr($reponse['date'],10,-3); ?>"><?php echo SQLdateConvert($reponse['date'], 'messages'); ?></td></tr>
										</table>
									</div>
									<div class="pad5"><?php echo nl2br(stripslashes($reponse['comment'])); ?></div>
								</div>
						<?php endforeach;
						endif; ?>
						</div>
				<?php endforeach;
				else : ?>
					<div class="ui-corner-all pad5 fondSect3 messageFinal">Aucun message.</div>
		<?php endif; ?>
			<br /><br />
			</div>
		</div>
	</div>
</div>