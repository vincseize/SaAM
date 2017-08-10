
$(function () {
	$('.requiredField').keyup(function() {
		var inputTxt = $(this).val();
		if ($(this).attr('id') == 'proj_title') {
			var regSpace = new RegExp('[ ]', 'gi');
			var refTxt = inputTxt.replace(regSpace, '');
			$('#proj_reference').html(default_ref+refTxt);
		}
		if (inputTxt.length >= 5)
			$(this).next().removeClass('ui-state-error').find('span').addClass('ui-icon-check');
		else $(this).next().addClass('ui-state-error').find('span').removeClass('ui-icon-check').addClass('ui-icon-notice');
		if (checkAllFilled()) {
			$('.submitBtns').removeClass("ui-state-disabled");
			$('.deptBtn[content="proj_add_team"]').removeClass('ui-state-disabled inactiveBtn');
		}
		else {
			$('.submitBtns').addClass("ui-state-disabled");
			$('.deptBtn[content="proj_add_team"]').addClass('ui-state-disabled inactiveBtn');
		}
	});

	// récup des datas si projet en cours de création
	if (localStorage['addProj_BaseVals']) {
		var baseValOnLoad = JSON.decode(localStorage['addProj_BaseVals']);
		$.each( baseValOnLoad, function (key, value) {
			if (key == 'date' || key == 'deadline') {
				if (value != undefined) {
					var D = new Date(value);
					var d = $.datepicker.formatDate( 'dd/mm/yy', D);
					$('#proj_'+key).datepicker('setDate', d);
				}
			}
			else if (key == 'softwares') {
				$.each(value, function(i,v){
					$('#proj_softwares input:checkbox[value="'+v+'"]').attr('checked', 'checked');
				});
			}
			else $('#proj_'+key).val(value);
			$('.requiredField').keyup();
		});
		if (checkAllFilled()) {
			$('.requiredField').next().removeClass('ui-state-error').find('span').addClass('ui-icon-check');
			$('.submitBtns').removeClass("ui-state-disabled");
			$('.deptBtn[content="proj_add_team"]').removeClass('ui-state-disabled inactiveBtn');
		}
		else $('.submitBtns').addClass("ui-state-disabled");
	}

	// Autocompletes
	$('#proj_director').autocomplete({source: autocompleteReals});
	$('#proj_supervisor').autocomplete({source: autocompleteSups});

	// selectMenus et buttonSets
	$('#proj_project_type').selectmenu({style: 'dropdown'});
	$('#proj_fps').selectmenu({style: 'dropdown'});
	$('#proj_softwares').buttonset();

	// clic sur bouton "Envoyer une vignette"
	$('#vignette_upload_Btn').click(function(){
		$('#vignette_upload').click();
	});


	// Boutons suivants et terminé
	$('#proj_NEXT_team').click(function(){
		if (!checkAllFilled()) {alert('Il manque une info (titre, deadline ou départements) !');return;}
		getBaseValues();
		$('.deptBtn[content="proj_add_team"]').click();
	});
	$('#proj_DONE').click(function(){
		if (!checkAllFilled()) {alert('Il manque une info (titre, deadline ou départements) !');return;}
		var valNewProj = getBaseValues();
		var valDepts   = encodeURIComponent(JSON.encode(getDeptsList()));
		console.log(valDepts);
		var ajaxStr = 'action=addProject&values='+valNewProj+'&depts='+valDepts;
		AjaxJson(ajaxStr, 'admin/admin_projects_actions', retourAjaxAddProj);
	});

	$('.deptBtn[content="proj_add_team"]').click(function(){
		getBaseValues();
	});

});
// FIN DU DOCUMENT READY


// vérifie que les champs "requiredFields" sont bien remplis
function checkAllFilled() {
	var r = true;
	$('.requiredField').each(function() {if ($(this).val().length < 4) r = false;});
	var dateStart = $('#proj_date').datepicker("getDate");
	var dateEnd   = $('#proj_deadline').datepicker("getDate");
	if (dateStart == null || dateEnd == null)
		r = false;
	else {
		if (dateStart > dateEnd) {
			$('#noticeDates').html('<span class="inline top ui-icon ui-icon-notice"></span>Inversion !&nbsp;</span>').addClass('ui-state-error').removeClass('noBG');
			r = false;
		}
		else $('#noticeDates').html('<span class="ui-icon ui-icon-check"></span>').removeClass('ui-state-error');
	}
	if ($('#noticeDepts').hasClass('ui-state-error')) r = false;
	return r;
}


// récupère toutes les valeurs de l'étape "base"
function getBaseValues () {
	var infosNewProj = {};
	$('.addProjDetail').each(function(){
		var valName = $(this).attr('id');
		if (valName == 'proj_softwares' || valName == 'proj_date' || valName == 'proj_deadline') return true;	// skip les softs et les dates pour traitement à part
		infosNewProj[valName.replace('proj_', '')] = $(this).val();
	});
	// dates
	infosNewProj['date']	 = $('#proj_date').datepicker("getDate");
	infosNewProj['deadline'] = $('#proj_deadline').datepicker("getDate");
	// Reference
	infosNewProj['reference'] = $('#proj_reference').html();
	// softs
	infosNewProj['softwares'] = [];
	$('#proj_softwares').children('input').each(function(){
		if ($(this).attr('checked'))
			infosNewProj['softwares'].push($(this).val());
	});
	infosNewProj['vignette'] = lastUploadedVignette;
	localStorage.setItem('addProj_EtapeWIP', 'team');
	localStorage.setItem('addProj_BaseVals', JSON.encode(infosNewProj));
	return (encodeURIComponent(JSON.encode(infosNewProj)));
}

