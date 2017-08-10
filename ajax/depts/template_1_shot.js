
$(function () {

	// Empêche les drop de fichier dans le browser
	$(document).bind('drop dragover', function (e) {
		e.preventDefault();
	});

	// Upload progress bar
	$('#vignetteShot_upload').bind('fileuploadprogress', function (e, data) {
		var filename = data.files[0].name;
		var percent =  data.loaded / data.total * 100;
		$('#retourAjax').find('.uploadProg[filename="'+filename+'"]')
						.progressbar({value: percent})
						.children('span').html('speed : '+Math.round(data.bitrate / 10000)+'Kb/s => '+Math.round(percent)+' %...');
	});

	// Init de l'uploader de vignette SHOT
	$('#vignetteShot_upload').fileupload({
		url: "actions/upload_vignettes.php?type=shot",
		dataType: 'json',
		dropZone: $('.vignetteTopInfos'),
		drop: function (e, data) {
			if (isLocked) {
				$('#retourAjax').html('This shot is locked.').addClass('ui-state-error').show(transition);
				setTimeout(function(){$('#retourAjax').fadeOut(transition, function(){$('#retourAjax').html('');});}, 1000);
				return;
			}
			$('#retourAjax')
				.html('Sending vignette...<br /><div class="uploadProg mini" filename="'+data.files[0].name+'"><span class="floatL marge5 colorMid"></span></div>')
				.addClass('ui-state-highlight')
				.show(transition);
		},
		done: function (e, data) {
			var retour = data.result;
			if (retour[0].error) {
				$('#retourAjax')
					.append('<br /><span class="colorErreur gras">Failed : '+retour[0].error+'</span>')
					.addClass('ui-state-error')
					.show(transition);
			}
			else {
				var ajaxReq = "action=moveVignette&idShot="+shot_ID+"&dept="+id_dept+"&vignetteName="+decodeURI(retour[0].name);
				AjaxJson(ajaxReq, "admin/admin_shots_actions", retourAjaxStructure, 'reloadShot');
			}
		}
	});

	$('#btn_modShot').off('click');
	// bouton MOD SHOT
	$('#btn_modShot').click(function(){
		if (isLocked) {
			$('#retourAjax').html('This shot is locked.').addClass('ui-state-error').show(transition);
			setTimeout(function(){$('#retourAjax').fadeOut(transition, function(){$('#retourAjax').html('');});}, 1000);
			return;
		}
		var $dialog = $('#modShot_dialog').clone(true);
		// init de la fenêtre de modification de shot
		$dialog.dialog({
			autoOpen: true, height: 300, width: 800, modal: true,
			show: "fade", hide: "fade",
			open: function() {
				$(this).find('#fps').selectmenu({style: 'dropdown'});
			},
			buttons: {'Valider'  : function() {
							if (checkBornes($(this))) {
								var valModShotDept = JSON.encode(getModValues());
								var ajaxReq = 'action=modShotDeptInfos&IDshot='+shot_ID+'&dept='+id_dept+'&shotDeptInfos='+valModShotDept;
								console.log(ajaxReq);
								AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, 'reloadShot');
								$(this).dialog('close');
							}
							else alert('Les bornes ont une valeur impossible.')
					  },
					  'Annuler'  : function() { $(this).dialog('close');  }
			},
			close: function() { $dialog.dialog('destroy'); $dialog.remove(); }
		});
	});

	// Modif des tags du shot
	$('#tagsContainer').find('input').change(function(){
		var tagsList = [];
		$('#tagsContainer').find('input').each(function(i, elem){ if ($(elem).attr('checked')) tagsList.push($(elem).val()); });
		var tagsJson = encodeURIComponent(JSON.encode(tagsList));
		var ajaxReq = 'action=modShotTags&IDshot='+shot_ID+'&tagName='+tagsJson;
		AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, false);
		if ($(this).attr('checked')) {
			$(this).parents('.tagLine').addClass('ui-state-active') ;
		}
		else {
			$(this).parents('.tagLine').removeClass('ui-state-active');
		}
	});


});
// FIN DU DOCUMENT READY




// Construit l'array des valeurs de modif dept infos
function getModValues() {
	var arrModVals = {};
	$('.modShotDeptDetail').each(function(){
		var valName = $(this).attr('id');
		var newValue = $(this).val();
		if (valName != '' && newValue != '' && newValue != null)
			arrModVals[valName] = newValue;
	});
	return arrModVals;
}


// vérifie si les bornes ne sont pas inversées, et si endF n'est pas plus grand que nbFrame
function checkBornes ($dialog) {
	var startF = parseInt($dialog.find('#startF').val(), 10);
	var endF   = parseInt($dialog.find('#endF').val(), 10);
	var nbF	   = parseInt($dialog.find('#nbFrames').val(), 10);
	if (startF <= 0) return false;
	if (endF <= startF + 3) return false;
	if (endF > nbF) return false;
	return true;
}