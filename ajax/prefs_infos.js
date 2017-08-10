
$(function () {


	//////////////// ANNULATIONS //////////////

	// clic sur annulation de l'avatar
	$('.annulAvatar').click(function(){
		$('#vignette_upload_img').attr('src', 'datas/users/'+userID+'_'+userLogin+'/vignette.png');
		hideAllBtns();
	});
	// clic sur un bouton d'annulation
	$('.annulModUser').click(function(){
		$(this).parent('div').hide().prev('input').val(lastModifUndo);
	});
	// pareil, mais pour le multiselect
	$('.annulSelect').click(function(){
		$(this).parent('div').hide();
		var undos = lastModifUndo.toString();
		$('#user_skills').children('option').each(function(){
			if (undos.indexOf($(this).val()) >= 0) {
				$(this).attr('selected', 'selected');
			}
			else $(this).removeAttr('selected');
		});
		$('#user_skills').multiselect('refresh');
	});
	// pareil, mais pour select lang
	$('.annulLang').click(function(){
		$('#user_lang').selectmenu('value', lastModifUndo);
		$(this).parent('div').hide();
	});
	// pareil, mais pour select theme
	$('.annulTheme').click(function(){
		$('#user_theme').selectmenu('value', lastModifUndo);
		$(this).parent('div').hide();
	});
	// pareil, mais pour receive mails
	$('.annulMailR').click(function(){
		$('.userModRM').removeAttr('checked');
		$('#'+lastModifUndo).attr('checked', 'checked');
		$('#mailReceive').buttonset("refresh");
		$(this).parent('div').hide();
	});
	// pareil, mais pour receive mails
	$('.annulNotifsR').click(function(){
		$('.userModRN').removeAttr('checked');
		$('#'+lastModifUndo).attr('checked', 'checked');
		$('#notifsReceive').buttonset("refresh");
		$(this).parent('div').hide();
	});


	//////////////// VALIDATIONS //////////////

	// clic sur validation de l'avatar
	$('.validAvatar').click(function(){
		var vignetteTmp = localStorage.getItem('addProj_VignetteUserTempName');
		var dirUser = userID+'_'+userLogin;
		var ajaxReq = 'action=validAvatar&dirUser='+dirUser+'&vignetteName='+vignetteTmp;
		AjaxJson(ajaxReq, 'prefs_user_actions', retourAjaxMsg, true);
	});

	// clic sur un bouton de validation
	$('.validModUser').click(function(){
		var typeVal = $(this).attr('what');
		var newVal = $(this).parent('div').prev('input').val();
		if (newVal != lastModifUndo)
			modifUserVal(typeVal, newVal);
	});
	// pareil, mais pour le multiselect (competences)
	$('.validSelect').click(function(){
		var typeVal = 'competences';
		var newVal = $('#user_skills').val();
		if (newVal != lastModifUndo)
			modifUserVal(typeVal, newVal);
	});
	// pareil, mais pour select lang
	$('.validLang').click(function(){
		var typeVal = 'lang';
		var newVal = $('#user_lang').val();
		modifUserVal(typeVal, newVal);
	});
	// pareil, mais pour select theme
	$('.validTheme').click(function(){
		var typeVal = 'theme';
		var newVal = $('#user_theme').val();
		modifUserVal(typeVal, newVal);
	});
	// pareil, mais pour receive mails
	$('.validMailR').click(function(){
		var typeVal = 'receiveMails';
		var newVal = $('#mailReceive :radio:checked').val();
		modifUserVal(typeVal, newVal);
	});
	// pareil, mais pour receive notifications
	$('.validNotifsR').click(function(){
		var typeVal = 'receiveNotifs';
		var newVal = $('#notifsReceive :radio:checked').val();
		modifUserVal(typeVal, newVal);
	});


	var editPpath = false;
	$('.modProjUserPath').click(function(){
		if (editPpath) return;
		editPpath = true;
		var td  = $(this).parents('td');
		var inpt = $('<input type="text" class="noBorder pad3 ui-corner-all fondSect3 w100p modProjUserPathVal" />');
		var oldVal = td.find('.currPath').text();
		if (oldVal !== 'Unknown yet.')
			inpt.val(oldVal);
		td.prepend(inpt).find('.currPath').hide();
		td.find('.confirmBtns').show();
		inpt.focus();
		$(this).hide();
	});
	$('.modProjUserUrl').click(function(){
		if (editPpath) return;
		editPpath = true;
		var td  = $(this).parents('td');
		var inpt = $('<input type="text" class="noBorder pad3 ui-corner-all fondSect3 w100p modProjUserUrlVal" />');
		var oldVal = td.find('.currPath').text();
		if (oldVal !== 'Unknown yet.')
			inpt.val(oldVal);
		td.prepend(inpt).find('.currPath').hide();
		td.find('.confirmBtns').show();
		inpt.focus();
		$(this).hide();
	});

	$('table').off('click', '.annuleProjUserPath');
	$('table').on('click', '.annuleProjUserPath', function(){
		var td  = $(this).parents('td');
		$('.modProjUserPath, .modProjUserUrl').show();
		td.find('.currPath').show();
		td.find('input').remove();
		td.find('.confirmBtns').hide();
		editPpath = false;
	});

	$('table').off('click', '.confirmProjUserPath');
	$('table').on('click', '.confirmProjUserPath', function(){
		var pID = $(this).parents('tr').attr('idProj');
		var uPath = $(this).parents('td').find('.modProjUserPathVal').val();
		AjaxJson('action=modUserPpath&pid='+pID+'&uPath='+uPath, 'prefs_user_actions', retourModPath);
	});

	$('table').off('click', '.confirmProjUserUrl');
	$('table').on('click', '.confirmProjUserUrl', function(){
		var pID = $(this).parents('tr').attr('idProj');
		var uUrl = $(this).parents('td').find('.modProjUserUrlVal').val();
		AjaxJson('action=modUserPurl&pid='+pID+'&uUrl='+uUrl, 'prefs_user_actions', retourModPath);
	});
});
// FIN DU DOCUMENT READY



// éxécute la requête ajax
function modifUserVal (typeVal, newVal) {
	if (typeVal == 'mail') {
		if (!verifyEmail(newVal)) {
			retourAjaxMsg({error: 'adresse email invalide !'});
			return;
		}
	}
	if (typeVal == 'competences') newVal = JSON.encode(newVal);
	var reload = false;
	if (typeVal == 'login' || typeVal == 'passwd') reload = 'relog';
	if (typeVal == 'lang' || typeVal == 'theme') reload = 'all';
	hideAllBtns();
	var ajaxReq = "action=modUserVal&row="+typeVal+"&val="+newVal;
	AjaxJson(ajaxReq, 'prefs_user_actions', retourAjaxMsg, reload);
}


function retourModPath (retour) {
	retourAjaxMsg(retour);
	$('.deptBtn[content="prefs_infos"]').click();
}