
$(function () {
	// check si les valeurs sont remplies
	$('.requiredField').keyup(function() {
		var inputTxt = $(this).val();
		if ($(this).attr('id') != 'mail') {
			if (inputTxt.length >= 4)
				$(this).next().removeClass('ui-state-error').find('span').addClass('ui-icon-check');
			else $(this).next().addClass('ui-state-error').find('span').removeClass('ui-icon-check').addClass('ui-icon-notice');
		}
		else {
			if (verifyEmail(inputTxt)) $(this).next().removeClass('ui-state-error').find('span').addClass('ui-icon-check');
			else $(this).next().addClass('ui-state-error').find('span').removeClass('ui-icon-check').addClass('ui-icon-notice');
		}
		if (checkAllFilled())
			$('#submitBtns').show(transition);
		else $('#submitBtns').hide();
	});

	// check spécifique au mail
	$('#mail').blur(function(){
		if (!verifyEmail($(this).val())) {
			$('#noticeMail').html('<span class="inline top ui-icon ui-icon-notice"></span>email invalide !!</span>').addClass('ui-state-error').removeClass('noBG');
		}
		else {
			$('#noticeMail').html('<span class="ui-icon ui-icon-check"></span></span>').removeClass('ui-state-error');
			$(this).next().removeClass('ui-state-error').find('span').addClass('ui-icon-check');
		}
	});

	// clic sur bouton "terminé" (ajouter user)
	$('#add_user_Done').click(function(){
		var ajaxReq = 'action=add';
		$('.addUser').each(function(){
			var valName = $(this).attr('id');
			var value	= $(this).val();
			if (value != null && value != undefined && value != '') {
				if (valName == 'my_projects' || valName == 'competences') {
					var arrVal = value.toString().split(',');
					value = JSON.encode(arrVal);
				}
				ajaxReq += '&'+valName+'='+value;
			}
		});
		if (localStorage.getItem('addProj_VignetteAddUserTempName')) {
			ajaxReq += '&avatar='+localStorage.getItem('addProj_VignetteAddUserTempName');
			localStorage.removeItem('addProj_VignetteAddUserTempName');
		}
		AjaxJson(ajaxReq, 'admin/admin_users_actions', retourAjaxUserAdd);
	});

});
// FIN DU DOCUMENT READY


function checkAllFilled() {
	var r = true;
	$('.requiredField').each(function() {
		if ($(this).val().length < 4) r = false;
		if ($(this).attr('id') == 'mail') {
			if (!verifyEmail($(this).val()))
				r = false;
		}
	});
	return r;
}


function retourAjaxUserAdd (retour) {
	if (retour.error == 'OK') {
		$('#retourAjax').html(retour.message).removeClass('ui-state-error').addClass('ui-state-highlight').show(transition);
		$('.deptBtn[content="user_list"]').click();
		setTimeout(function() {$('#retourAjax').fadeOut(transition);}, 1000);
	}
	else {
		$('#retourAjax').html(retour.error+' : '+retour.message).addClass('ui-state-error').removeClass('ui-state-highlight').show(transition);
		setTimeout(function() {$('#retourAjax').fadeOut(transition);}, 3000);
	}
}