<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	$d			= new Dailies($_POST['projectID'], 0);
	$weekLoad	= (isset($_POST['week'])) ? $_POST['week'] : date('Y_W') ;
	$summary	= $d->getSummary($weekLoad);

	$actualWeek		= ($weekLoad == date('Y_W')) ? true : false;
	$classComments	= (!$actualWeek) ? 'ui-state-disabled' : '';
	$weekStr		= L_THIS_WEEK;
	$thisWeekDateDisp = getweekStartEndDays(date('W'), date('Y'));
	if (!$actualWeek) {
		$nbW = explode('_', $weekLoad);
		$nbWtoLoad = $nbW[1];
		if ((int)$nbWtoLoad == ((int)date('W') - 1))
			$weekStr = L_LAST_WEEK;
		else $weekStr = L_WEEK.' n°'.$nbWtoLoad;
		$thisWeekDateDisp = getweekStartEndDays($nbW[1], $nbW[0]);
	}
?>

<script>
	var loadedWeek = "<?php echo $weekLoad; ?>";

	$(function(){

		setTimeout(function(){
			var viewSummary = $('#commentList').height();
			$('#summary').slimScroll({
				position: 'left',
				height: viewSummary+'px',
				size: '10px',
				wheelStep: 10,
				railVisible: true
			});
		}, 300);


		$('#weekIndicator').html('<?php echo $weekStr; ?>');
		$('#weekIndicatorDays').html('(<?php echo $thisWeekDateDisp['start'].'-'.$thisWeekDateDisp['end']; ?>)');

		if (actualWeek !== loadedWeek)
			$('#btn_addComment').css({visibility:'hidden'});
		else
			$('#btn_addComment').css({visibility:'visible'});

		// Validation de l'ajout de commentaire
		$('#commentList').off('click', '#addCommentValid');
		$('#commentList').on('click', '#addCommentValid', function(){
			if (actualWeek !== loadedWeek) return false;
			var messageTxt = $('#addCommentTxt').val();
			if (messageTxt == '') { alert('message vide !'); return; }
			addCommentWip = false;
			var ajaxReq = 'action=addSummary&projID='+project_ID+'&comment='+encodeURIComponent(messageTxt);
			AjaxJson(ajaxReq, 'depts/dailies_actions', retourAjaxDailies);
		});

		// Annulation de l'ajout de commentaire
		$('#commentList').off('click', '#addCommentAnnul');
		$('#commentList').on('click', '#addCommentAnnul', function(){
			addCommentWip = false;
			$('#addCommentDiv').remove();
			$('#btn_addComment').removeClass('ui-state-activeFake');
		});

		// Suppression de commentaire
		$('#commentList').off('click', '.delComment');
		$('#commentList').on('click', '.delComment', function(){
			if (!confirm('Delete this comment? Sure?')) return;
			var idComm = $(this).attr('idM');
			var ajaxReq = 'action=deleteComment&idComm='+idComm;
			AjaxJson(ajaxReq, 'depts/dailies_actions', retourAjaxDailies);
		});

		// Modification de commentaire
		$('#commentList').off('click', '.modComment');
		$('#commentList').on('click', '.modComment', function(){
			var idComm = $(this).attr('idM');
			var oldText = $('.commentText[idM="'+idComm+'"]').html();
			$(modCommentDiv(idComm, oldText)).insertBefore('.commentBlock[idComment="'+idComm+'"]');
			$('.commentBlock[idComment="'+idComm+'"]').hide();
			$('.btnM').button();
			addCommentWip = true;
		});

		// Validation de la modif de commentaire
		$('#commentList').off('click', '#modCommentValid');
		$('#commentList').on('click', '#modCommentValid', function(){
			var idComm = $(this).attr('idM');
			var messageTxt = $('#modCommentTxt').val();
			if (messageTxt == '') { alert('message vide !'); return; }
			addCommentWip = false;
			var ajaxReq = 'action=modSummary&projID='+project_ID+'&commID='+idComm+'&comment='+encodeURIComponent(messageTxt);
			AjaxJson(ajaxReq, 'depts/dailies_actions', retourAjaxDailies);
		});

		// Annulation de la modif de commentaire
		$('#commentList').off('click', '#modCommentAnnul');
		$('#commentList').on('click', '#modCommentAnnul', function(){
			var idComm = $(this).attr('idM');
			$('.commentBlock[idComment="'+idComm+'"]').show();
			addCommentWip = false;
			$('#modCommentDiv').remove();
		});
	});

	function addCommentDiv () {
		return '<div class="margeTop5" id="addCommentDiv">'
			+	'<textarea class="ui-corner-all ui-corner-all fondSect4 noBorder pad3" style="width:75%; resize:none;" rows="12" id="addCommentTxt"></textarea> '
			+	'<div class="inline bot nano">'
			+		'<p class="pad3 enorme ui-state-disabled">Écrivez votre<br />commentaire,<br />puis validez.</p>'
			+		'<button class="btnM" id="addCommentValid"><span class="ui-icon ui-icon-check" title="valider"></span></button>'
			+		'<button class="btnM" id="addCommentAnnul"><span class="ui-icon ui-icon-cancel" title="annuler"></span></button>'
			+	'</div>'
			+'</div>';
	}

	function modCommentDiv (idM, oldText) {

		return '<div class="margeTop5" id="modCommentDiv">'
			+	'<textarea class="ui-corner-all ui-corner-all fondSect4 noBorder pad3" style="width:75%; resize:none;" rows="12" id="modCommentTxt">'+oldText.replace(/<br>/g,"\n")+'</textarea> '
			+	'<div class="inline bot nano">'
			+		'<p class="pad3 enorme ui-state-disabled">Modifiez votre<br />commentaire,<br />puis validez.</p>'
			+		'<button class="btnM" id="modCommentValid" idM="'+idM+'"><span class="ui-icon ui-icon-check" title="valider"></span></button>'
			+		'<button class="btnM" id="modCommentAnnul" idM="'+idM+'"><span class="ui-icon ui-icon-cancel" title="annuler"></span></button>'
			+	'</div>'
			+'</div>';
	}
</script>

<div id="summary">

<?php
if (is_array($summary)):
	foreach ($summary as $comment):
		$u = new Users((int)$comment[Dailies::DAILIES_USER]);
		$btnDelete = ''; $bntModify = '';
		if (($actualWeek && (int)$comment[Dailies::DAILIES_USER] == $_SESSION['user']->getUserInfos(Users::USERS_ID))
			|| $_SESSION['user']->isDirProd()) {
			$btnDelete = '<span class="inline ui-icon ui-icon-trash doigt delComment" idM="'.$comment[Dailies::DAILIES_ID].'" title="supprimer"></span> ' ;
			$bntModify = '<span class="inline ui-icon ui-icon-pencil doigt modComment" idM="'.$comment[Dailies::DAILIES_ID].'" title="modifier"></span>';
		} ?>
		<div class="commentBlock marge5 <?php echo $classComments; ?>" idComment="<?php echo $comment[Dailies::DAILIES_ID]; ?>">
			<div class="ui-corner-all fondSect1">
				<div class="ui-corner-top fondPage colorMid">
					<table class="w100p">
						<tr>
							<td class="w80">
								<span class="inline ui-state-disabled ui-icon ui-icon-info"></span>
								<?php echo $btnDelete.$bntModify; ?>
							</td>
							<td><?php echo $u->getUserInfos(Users::USERS_PSEUDO); ?></td>
							<td class="w100 rightText" title="<?php echo substr($comment[Dailies::DAILIES_DATE],10,-3); ?>">
								<?php echo SQLdateConvert($comment[Dailies::DAILIES_DATE], 'messages'); ?>
							</td>
						</tr>
					</table>
				</div>
				<div class="pad5 commentText" idM="<?php echo $comment[Dailies::DAILIES_ID]; ?>"><?php
					echo preg_replace('/\n/', '', nl2br(stripslashes($comment[Dailies::DAILIES_COMMENT])));
				?></div>
			</div>
		</div>
<?php
	endforeach;
else: ?>
	<span class="ui-state-disabled">No summary for this week, yet.</span>
<?php
endif; ?>

</div>