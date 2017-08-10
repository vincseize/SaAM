
var changeDepts = false;

$(function () {

	// Empêche les drop de fichier dans le browser
	$(document).bind('drop dragover', function (e) {
		e.preventDefault();
	});

	// bouton VALIDER MOD SHOT
	$('#btn_modShot').click(function(){
		if (isLocked) {
			$('#retourAjax').html('This shot is locked.').addClass('ui-state-error').show(transition);
			setTimeout(function(){$('#retourAjax').fadeOut(transition, function(){$('#retourAjax').html('');});}, 1000);
			return;
		}
		var valModShot = encodeURIComponent(JSON.encode(getModValues()));
		var ajaxReq = 'action=modShot&IDshot='+shot_ID+'&shotInfos='+valModShot;
		AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, 'reloadShot');
	});

	// bouton ANNULER MOD SHOT
	$('#btn_annulModShot').click(function(){
		if (confirm('Annuler les modifs ?')) {
			retourAjaxStructure({error: 'OK', message: 'annulé'}, 'totalReload');
		}
	});


	// bouton HIDE SHOT
	$('#btn_hideShot').click(function() {
		if (isLocked) {
			$('#retourAjax').html('This shot is locked.').addClass('ui-state-error').show(transition);
			setTimeout(function(){$('#retourAjax').fadeOut(transition, function(){$('#retourAjax').html('');});}, 1000);
			return;
		}
		var ajaxReq  = 'action=modShot&IDshot='+shot_ID;
		if ($(this).hasClass('ui-state-disabled')) {
			if (confirm('Montrer le plan ?')) {
				ajaxReq += '&shotInfos={"hide":"0"}';
				AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, false);
				$(this).removeClass('ui-state-disabled').removeClass('ui-state-focus');
			}
		}
		else {
			if (confirm('Cacher le plan ?')) {
				ajaxReq += '&shotInfos={"hide":"1"}';
				AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, false);
				$(this).addClass('ui-state-disabled doigt').removeClass('ui-state-focus');
			}
		}
	});

	// bouton LOCK SHOT
	$('#btn_lockShot').click(function() {
		var ajaxReq  = 'action=modShot&IDshot='+shot_ID;
		if ($(this).hasClass('ui-state-disabled')) {
			if (confirm('Débloquer le plan ?')) {
				ajaxReq += '&shotInfos={"lock":"0"}';
				AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, false);
				$(this).removeClass('ui-state-disabled').removeClass('ui-state-focus');
			}
		}
		else {
			if (confirm('Bloquer le plan ?')) {
				ajaxReq += '&shotInfos={"lock":"1"}';
				AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, false);
				$(this).addClass('ui-state-disabled doigt').removeClass('ui-state-focus');
			}
		}
	});

	// bouton ARCHIVE SHOT
	$('#btn_archiveShot').click(function(){
		if (confirm('Archiver le plan ?')) {
			var ajaxReq = 'action=archiveShot&IDshot='+shot_ID;
			AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, true);
		}
	});
	// bouton RESTAURE SHOT
	$('#btn_restoreShot').click(function(){
		if (confirm('Restaurer le plan ?')) {
			var ajaxReq = 'action=restoreShot&IDshot='+shot_ID;
			AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, true);
		}
	});


	// BOUTON ajout d'artiste à la liste de l'équipe du shot
	$('#showAddArtistToShot').toggle(
		function(){
			$('#addArtistToShotDiv').show(transition);
			$(this).children('span').addClass('ui-icon-minus')
		},
		function(){
			$('#addArtistToShotDiv').hide(transition);
			$(this).children('span').removeClass('ui-icon-minus')
		}
	);
	$('#addArtistToShotBtn').click(function(){
		var artistToAdd = $('#addArtistToShotInput').val();
		var jsonArtists = JSON.encode(artistToAdd);
		var ajaxReq = 'action=modShotTeam&IDshot='+shot_ID+'&newTeam='+jsonArtists;
		AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, 'reloadShot');
		$('#showAddArtistToShot').click();
	});

	// Modif des TAGS du shot
	$('#tagsContainer').find('input').change(function(){
		var tagsList = [];
		$('#tagsContainer').find('input').each(function(i, elem){ if ($(elem).attr('checked')) tagsList.push($(elem).val()); });
		var tagsJson = encodeURIComponent(JSON.encode(tagsList));
		var ajaxReq = 'action=modShotTags&IDshot='+shot_ID+'&tagName='+tagsJson;
		AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, false);
		if ($(this).attr('checked')) {
			$(this).parents('.tagLine').addClass('ui-state-error') ;
		}
		else {
			$(this).parents('.tagLine').removeClass('ui-state-error');
		}
	});

	// Assignation des départements du shot
	$('.selectShotDepts').multiselect({height: 300, minWidth: 250, selectedList: 0, noneSelectedText: '<i>Departments assignation</i>', selectedText: '# departments assigned', checkAllText: ' ', uncheckAllText: ' ',
		click: function(){
			changeDepts = true;
		},
		close: function(){
			if (changeDepts == false) return;
			var newShotDepts = JSON.encode($(this).val());
			var ajaxReq = 'action=modShotDepts&shotID='+shot_ID+'&newDepts='+newShotDepts;
			changeDepts = false;
			AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, 'reloadShot');
		}
	});
});
// FIN DU DOCUMENT READY




// Construit l'array des valeurs de modif project
function getModValues() {
	var arrModVals = {};
	$('.modShotDetail').each(function(){
		var valName = $(this).attr('id');
		if (valName == 'date' || valName == 'deadline') return true;	// skip les les dates pour traitement à part
		var newValue = $(this).val();
		if (valName == 'description')
			newValue = newValue.replace(/\n/g, '<br>');
		if (valName != '' && newValue != '' && newValue != null)
			arrModVals[valName] = newValue;
	});
	// dates
	arrModVals['date']	 = $('.modShotDetail#date').datepicker("getDate");
	arrModVals['deadline'] = $('.modShotDetail#deadline').datepicker("getDate");

	return arrModVals;
}
