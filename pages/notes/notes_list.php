<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	$ln = new Liste();
	$ln->getListe(TABLE_NOTES, '*', 'position', 'DESC', 'ID_user', '=', $_SESSION['user']->getUserInfos(Users::USERS_ID));
	$listeNotes = $ln->simplifyList('id');

?>

<script>
	var WIPmodNote = false;
	var oldText = '';

	$(function(){
		$('.bouton').button();

		// Ajout de note
		$('#btnAddNote').click(function(){
			$('.noNote').hide();
			$('#texteAddNote').val('<?php echo L_ADD; ?> note');
			$('#modeleAddNote').show(transition);
			WIPmodNote = true;
		});

		// Focus de textarea new note
		$('#texteAddNote').focus(function(){
			if ($(this).val() == '<?php echo L_ADD; ?> note')
				$(this).val('');
		});

		// Validation ajout de note
		$('#valideAddNote').click(function(){
			var textAdd = $('#texteAddNote').val();
			var ajaxReq = "action=addNote&textNote="+encodeURIComponent(textAdd);
			AjaxJson(ajaxReq, 'notes_actions', reloadNotes);
			WIPmodNote = false;
		});

		// Annulation ajout de note
		$('#cancelAddNote').click(function(){
			$('.noNote').show();
			$('#modeleAddNote').hide(transition);
			WIPmodNote = false;
		});

		// Modif de note
		$('#listeNotes').off('click', '.modifNote');
		$('#listeNotes').on('click', '.modifNote', function(){
			if (WIPmodNote) return;
			var idNote = $(this).parent('div').attr('idNote');
			var divNote = $('.textNote[idNote="'+idNote+'"]');
			oldText = divNote.html();
			divNote.html('<textarea class="fondSect3 noBorder ui-corner-all pad3" rows="6" cols="22" id="texteModNote">'+oldText.replace(/<br>/g,"\n")+'</textarea>'
						+'<div class="rightText pico">'
							+'<button class="bouton" id="valideModNote" idNote="'+idNote+'"><span class="ui-icon ui-icon-check"></span></button> '
							+'<button class="bouton" id="cancelModNote" idNote="'+idNote+'"><span class="ui-icon ui-icon-cancel"></span></button>'
						+'</div>');
			$('.bouton').button();
			WIPmodNote = true;
		});

		// Validation modif de note
		$('#listeNotes').off('click', '#valideModNote');
		$('#listeNotes').on('click', '#valideModNote', function(){
			var textMod = $('#texteModNote').val();
			var idNote = $(this).attr('idNote');
			var ajaxReq = "action=modNote&textNote="+encodeURIComponent(textMod)+"&idNote="+idNote;
			AjaxJson(ajaxReq, 'notes_actions', reloadNotes);
			WIPmodNote = false;
		});

		// Annulation modif de note
		$('#listeNotes').off('click', '#cancelModNote');
		$('#listeNotes').on('click', '#cancelModNote', function(){
			var idNote = $(this).attr('idNote');
			$('.textNote[idNote="'+idNote+'"]').html(oldText);
			WIPmodNote = false;
		});


		// Suppression de note
		$('#listeNotes').off('click', '.deleteNote');
		$('#listeNotes').on('click', '.deleteNote', function(){
			if (!confirm('Delete this note ?')) return;
			var idNote = $(this).parent('div').attr('idNote');
			var ajaxReq = "action=deleteNote&idNote="+idNote;
			AjaxJson(ajaxReq, 'notes_actions', reloadNotes);
		});

		// Modification de la couleur de note
		$('#listeNotes').off('click', '.colorMarkBtn');
		$('#listeNotes').on('click', '.colorMarkBtn', function(){
			var idNote = $(this).parent('div').attr('idNote');
			var position = $(this).attr('colorMark');
			var ajaxReq = "action=markPosNote&idNote="+idNote+'&position='+position;
			AjaxJson(ajaxReq, 'notes_actions', reloadNotes);
		});

	});

	function reloadNotes (datas) {
		if (datas.error == 'OK') {
			$('#retourAjax').html(datas.message).addClass('ui-state-highlight').show(transition);
			setTimeout(function(){$('#retourAjax').fadeOut(transition*5, function(){$('#retourAjax').html('').removeClass('ui-state-highlight');});}, 2000);
			$('.deptBtn[content="notes_list"]').click();
			$('.myMenuHeadEntry[menuload="my_notes"]').click();
		}
		else {
			$('#retourAjax').html('<b>'+datas.message+'</b>').addClass('ui-state-error').show(transition);
			setTimeout(function(){$('#retourAjax').fadeOut(transition*10, function(){$('#retourAjax').html('').removeClass('ui-state-error');});}, 2000);
		}
	}

</script>

<div class="stageContent pad5">

<div class="inline mid big gras marge30r">
	<?php echo L_MY_NOTES; ?>
</div>
<div class="inline mid petit">
	<button class="bouton" title="<?php echo L_ADD; ?>" id="btnAddNote"><span class="ui-icon ui-icon-plusthick"></span></button>
</div>



	<div class="margeTop10" id="listeNotes">

		<div class="inline mid marge10r" style="display: none;" id="modeleAddNote">
			<textarea class="fondSect3 noBorder ui-corner-all pad3 w200" rows="6" id="texteAddNote"></textarea>
			<div class="rightText nano">
				<button class="bouton" id="valideAddNote"><span class="ui-icon ui-icon-check"></span></button>
				<button class="bouton" id="cancelAddNote"><span class="ui-icon ui-icon-cancel"></span></button>
			</div>
		</div>

		<?php
		if (is_array($listeNotes)) :
			foreach ($listeNotes as $note) :
				switch ($note['position']) {
					case 2:		$styleNote = 'box-shadow: 0px 0px 6px #FECF5B;'; break;
					case 1:		$styleNote = 'box-shadow: 0px 0px 6px #006E9E;'; break;
					default:	$styleNote = 'box-shadow: 2px 2px 8px #000;'; break;
				} ?>

			<div class="inline top w200 ui-corner-all fondSect4 marge10r margeTop5" style="<?php echo $styleNote; ?>">
				<div class="fondSect3 ui-corner-top rightText petit colorSoft">
					<div class="floatL" idNote="<?php echo $note['id']; ?>">
						<span class="inline ui-icon ui-icon-trash doigt deleteNote"></span>
						<span class="inline ui-icon ui-icon-pencil doigt modifNote"></span>
						<div class="inline bot marge5bot colorMarkBtn pad5 marge10l doigt" style="background-color:#FECF5B;" title="yellow color mark" colorMark="2"></div>
						<div class="inline bot marge5bot colorMarkBtn pad5 doigt" style="background-color:#006E9E;" title="blue color mark" colorMark="1"></div>
						<div class="inline bot marge5bot colorMarkBtn doigt" style="padding:4px; border:1px solid lightgray;" title="No color" colorMark="0"></div>
					</div>
					<div class="inline pad3" title="<?php echo SQLdateConvert($note['date']); ?>">
						<?php echo SQLdateConvert($note['date'], 'messages'); ?>
					</div>
				</div>
				<div class="pad5 textNote" idNote="<?php echo $note['id']; ?>"><?php echo preg_replace('/\\n/','',nl2br(urldecode($note['note']))); ?></div>
			</div>

		<?php endforeach;
		else: ?>
			<div class="ui-state-disabled margeTop5 noNote">
				<?php echo L_NO_NOTE; ?>
			</div>
		<?php endif; ?>

	</div>
</div>