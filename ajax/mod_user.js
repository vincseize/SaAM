
$(function () {
	// Init de l'animation des lignes de la liste des users
	$('.tableListe tr.ui-state-default').hover(
		function(){$(this).addClass('ui-state-hover');},
		function(){$(this).removeClass('ui-state-hover');}
	);

	// Init du filtrage (toggle bouton)
	$('#filtreToggle').toggle(
		function() {
			$('#filtreContent').show();
			$(this).children('button').addClass('ui-state-error');
		},
		function() {
			$('#filtreContent').hide();
			$('.filter').removeAttr('checked').next().removeClass('ui-state-error ui-state-active');
			$(this).children('button').removeClass('ui-state-error');
			$('tr:not(.detailUser)').show();
		}
	);
	$('#filtreContent').off('click', '.filter');
	$('#filtreContent').on('click', '.filter', function(){
		if (! $(this).next().hasClass('ui-state-error')) {
			$('.filter').removeAttr('checked').next().removeClass('ui-state-error ui-state-active');
			$(this).next().addClass('ui-state-error');
			var toFilter = $(this).val();
			var entriesOK = $('tr[status~="'+toFilter+'"]');
			$('tr:not(.headerUsersList)').hide();
			entriesOK.show();
		}
		else {
			$('.filter').removeAttr('checked').next().removeClass('ui-state-error ui-state-active');
			$('tr:not(.detailUser)').show();
		}
	});
	$('#searchUsers').off('click', '#usrSearchBtn');
	$('#searchUsers').on('click', '#usrSearchBtn', function(){
		if ($('#filtreToggle').children('button').hasClass('ui-state-error'))
			$('#filtreToggle').click();
	});
	$('#searchUsers').off('click', '#usrSearchSubmit');
	$('#searchUsers').on('click', '#usrSearchSubmit', function(){
		var term = $('#usrSearchInput').val();
		if (term == '') {
			$('tr:not(.headerUsersList)').not('.detailUser').show();
			$(this).parent().removeClass('ui-state-error noBG');
			return;
		}
		$('tr:not(.headerUsersList)').hide();
		var terme = term.replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
		var re = new RegExp(terme, "i");
		$('tr:not(.headerUsersList)').not('.detailUser').filter(function() {
			return (re.test($(this).attr('loginUser')) || re.test($(this).attr('pseudoUser')) || re.test($(this).attr('nameUser')));
		}).show();
		$(this).parent().addClass('ui-state-error noBG');
		$('#usrSearchInput').focus();
	});

	// Déroulage des infos détaillées d'un user
	$('.tableListe td:not(.nowrap)').click(function(){
		if (!$(this).parent().hasClass('ui-state-focusFake')) {
			$('tr').removeClass('ui-state-focusFake');
			$('.detailUser').hide();
			$(this).parent().addClass('ui-state-focusFake').next('tr').show();
		}
		else {
			$('tr').removeClass('ui-state-focusFake');
			$('.detailUser').hide();
		}
	});


	// Assignations (projets, seq, shots, assets...) d'un user
	$('.assignUserBtn').click(function() {
		if ($(this).parent('td').hasClass('ui-state-disabled')) { alert('demo mode : restricted'); return; }
		var loginUser = $(this).parents('tr').attr('loginUser');
		var ajaxReq = 'action=loadAssigns&loginUser='+loginUser;
		AjaxJson(ajaxReq, 'admin/admin_users_actions', setAssignUserValues);
	});

	// Activation / désactivation d'un user
	$('.activeUserBtn').click(function() {
		var thisBtn = $(this);
		if (thisBtn.parent('td').hasClass('ui-state-disabled')) { alert('demo mode : restricted'); return; }
		var loginUser = thisBtn.parents('tr').attr('loginUser');
		var active  = thisBtn.hasClass('ui-state-activeFake');
		var activeData = '1';
		if (active) {
			if (!confirm('Disable user "'+loginUser+'" ? Sure ?')) return;
			activeData = '0';
		}
		var ajaxReq = 'action=activate&loginUser='+loginUser+'&active='+activeData;
		AjaxJson(ajaxReq, 'admin/admin_users_actions', function(R){
			retourAjaxMsg(R);
			if (R.error == 'OK') {
				thisBtn.addClass('ui-state-activeFake').blur();
				thisBtn.parents('tr').removeClass('ui-state-disabled').addClass('ui-state-default');
				if (active) {
					thisBtn.removeClass('ui-state-activeFake');
					thisBtn.parents('tr').removeClass('ui-state-default').addClass('ui-state-disabled');
				}
			}
		});
	});

	// Modification d'un user
	$('.modUserBtn').click(function() {
		if ($(this).parent('td').hasClass('ui-state-disabled')) { alert('demo mode : restricted'); return; }
		var loginUser = $(this).parents('tr').attr('loginUser');
		var ajaxReq = 'action=load&loginUser='+loginUser;
		AjaxJson(ajaxReq, 'admin/admin_users_actions', setModUserValues);
	});

	// Suppression d'un user
	$('.delUserBtn').click(function() {
		if ($(this).parent('td').hasClass('ui-state-disabled')) { alert('demo mode : restricted'); return; }
		var idUser  = $(this).parents('tr').attr('idUser');
		var logUser = $(this).parents('tr').attr('loginUser');
		var ajaxReq = 'action=del&idUser='+idUser+'&loginUser='+logUser;
		if (confirm("Supprimer l'utilisateur "+logUser+' (#'+idUser+') ?'))
			AjaxJson(ajaxReq, 'admin/admin_users_actions', retourAjaxMsg, true);
	});

});
// FIN DU DOCUMENT READY

// rempli les données de l'utilisateur dans la modal de modif
function setModUserValues (Udatas) {
	if (Udatas.error == 'OK') {
		$('.modUser').selectmenu('destroy');
		$('.modUser').multiselect('destroy');
		// Récup des infos de l'user, et remplissage des inputs
		$.each(Udatas.userDatas, function(row, dataUser) {
			if (row == 'vignetteUser' || row == 'status' || row == 'competences' || row == 'my_projects') return true;
			$('.modUser#'+row).val(dataUser);
		});
		try {
			$('#vignette_user').attr('src', 'gfx/novignette/novignette_user.png');
			var vignetteUser = Udatas.userDatas.vignetteUser;
			if (vignetteUser != '')
				$('#vignette_user').attr('src', vignetteUser);

			var userStatus = Udatas.userDatas.status;
			$('#status').children('option').removeAttr('selected');
			$('#status').children('option[value="'+userStatus+'"]').attr('selected', 'selected');

			$('#competences').children('option').removeAttr('selected');
			if (Udatas.userDatas.competences != '') {
				var userComps = JSON.decode(Udatas.userDatas.competences);
				$.each(userComps, function(i,comp) {
					$('#competences').children('option[value="'+comp+'"]').attr('selected', 'selected');
				});
			}
		}
		catch(e) {
			//alert(e); console.log(Udatas); return; }
		}

		// Init de la modal de modif user
		var $modModal = $('#modUserModal').clone();
		$modModal.dialog({
			autoOpen: true, height: 600, width: 750, modal: true,
			show: "fade", hide: "fade",
			open: function() {
				$(this).find('#status').selectmenu({style: 'dropdown'});
				$(this).find('#competences').multiselect({noneSelectedText: 'Aucun', selectedText: '# competence(s)', selectedList: 3, checkAllText: ' ', uncheckAllText: ' '});
				// check si les valeurs sont remplies
				$(this).find('.requiredField').keyup(function() { checkOneFilled($(this)); });
				checkAllFilled($(this));
			},
			close: function() {$(this).remove();},
			buttons:{'Valider' : function() {if (saveModUser($(this))) {$(this).dialog('close');}},
					 'Annuler'  : function() {$(this).dialog('close');}
			}
		});
	}
	else {
		retourAjaxMsg({error:'error', message:'invalid or missing data !'});
	}
}


// rempli les données d'assignations de l'utilisateur dans la modal d'assignation
function setAssignUserValues (UassignDatas) {
	if (UassignDatas.error == 'OK') {
		$('.assignUser').multiselect('destroy');
		try {
			$('.assignUser#my_projects').children('option').removeAttr('selected');
			$('.assignUser#idUser').val(UassignDatas.idUser);
			if (UassignDatas.projectsUser != '' && UassignDatas.projectsUser != null) {
				$.each(UassignDatas.projectsUser, function(i,proj) {
					$('.assignUser#my_projects').children('option[value="'+proj+'"]').attr('selected', 'selected');
				});
			}
		}
		catch(e) {alert(e);console.log(UassignDatas);return;}

		// Init de la modal de modif user
		var $assignModal = $('#assignUserModal').clone();
		$assignModal.dialog({
			autoOpen: true, height: 400, width: 650, modal: true,
			show: "fade", hide: "fade",
			open: function() {
				$(this).find('#my_projects').multiselect({autoOpen: true, noneSelectedText: 'Aucun', selectedText: '# projet(s)', selectedList: 3, checkAllText: ' ', uncheckAllText: ' '});
			},
			close: function() {$(this).remove();},
			buttons:{'Valider' : function() {if (saveAssignUser($(this))) {$(this).dialog('close');}},
					 'Annuler'  : function() {$(this).dialog('close');}
			}
		});
	}
	else {
		retourAjaxMsg({error:'error', message:'invalid or missing data !'});
	}
}



// Sauvegarde de modif d'un user
function saveModUser($dialog) {
	if (!checkAllFilled($dialog)) {
		alert('Il manque des informations !');
		return false;
	}
	var ajaxReq = 'action=mod';
	$dialog.find('.modUser').each(function(){
		var row = $(this).attr('id');
		var val = $(this).val();
		if (val != '') {
			if (row == 'my_projects' || row == 'competences')
				val = JSON.encode(val);
			ajaxReq += '&'+row+'='+encodeURIComponent(val);
		}
	});
	AjaxJson(ajaxReq, 'admin/admin_users_actions', retourAjaxMsg, true);
	return true;
}

// Sauvegarde des assignations d'un user
function saveAssignUser($dialog) {
	var IDuser = $dialog.find('#idUser').val();
	var projects = $dialog.find('#my_projects').val();
	var projList = JSON.encode(projects);
	var ajaxReq = 'action=modAssigns&idUser='+IDuser+'&projects='+projList;
	AjaxJson(ajaxReq, 'admin/admin_users_actions', retourAjaxMsg, true);
	return true;
}


// Fonctions de vérification des valeurs obligatoires
function checkOneFilled($elem) {
	var inputTxt = $elem.val();
	if ($elem.attr('id') != 'mail') {
		if (inputTxt.length >= 4)
			$elem.next('div').removeClass('ui-state-error').find('span').addClass('ui-icon-check');
		else $elem.next('div').addClass('ui-state-error').find('span').removeClass('ui-icon-check').addClass('ui-icon-notice');
	}
	else {
		if (verifyEmail(inputTxt)) $elem.next().removeClass('ui-state-error').html('<span class="ui-icon ui-icon-check"></span>');
		else $elem.next('div').addClass('ui-state-error').find('span').removeClass('ui-icon-check').addClass('ui-icon-notice');
		$elem.blur(function(){checkMailFilled($elem)});
	}
}

function checkMailFilled($elem) {
	if (!verifyEmail($elem.val())) {
		$elem.next('div').addClass('ui-state-error').removeClass('noBG').html('<span class="inline mid ui-icon ui-icon-notice"></span>email invalide !');
	}
	else {
		$elem.next('div').removeClass('ui-state-error').html('<span class="ui-icon ui-icon-check"></span>');
		$elem.next('div').removeClass('ui-state-error').find('span').addClass('ui-icon-check');
	}
}

function checkAllFilled($elem) {
	var r = true; var s = true;
	$elem.find('.requiredField').each(function() {
		s = true;
		if ($(this).val().length < 4) { r = false; s = false;}
		if ($(this).attr('id') == 'mail') {
			if (!verifyEmail($(this).val())) { r = false; s = false; }
		}
		if (s) $(this).next('div').removeClass('ui-state-error').html('<span class="ui-icon ui-icon-check"></span>');
		else   $(this).next('div').addClass('ui-state-error').children('span').removeClass('ui-icon-check');
	});
	return r;
}
