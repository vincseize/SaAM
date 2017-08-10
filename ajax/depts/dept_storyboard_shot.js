
$(function () {

	// Empêche les drop de fichier dans le browser
	$(document).bind('drop dragover', function (e) {
		e.preventDefault();
	});

	// Init de l'uploader de vignette SHOT
	$('#vignetteShot_upload').fileupload({
		url: "actions/upload_vignettes.php?type=shot",
		dataType: 'json',
		dropZone: $('.vignetteTopInfos'),
		drop: function (e, data) {
			$('#retourAjax')
				.html('<span class="ui-state-disabled"><i>transfert</i></span> ' + data.files[0].name + '... <img src="gfx/ajax-loader.gif" />')
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
				var ajaxReq = "action=moveVignette&idShot="+shot_ID+"&dept=storyboard&vignetteName="+decodeURI(retour[0].name);
				AjaxJson(ajaxReq, "admin/admin_shots_actions", retourAjaxStructure, 'reloadShot');
			}
		}
	});

	// Hover des images de retakes "A,B,C,..."
	$('.imageChooser').hover(
		function() {
			if (! $(this).hasClass('ui-state-active'))
				$(this).addClass('ui-state-hover');
		},
		function() {
			$(this).removeClass('ui-state-hover');
		}
	);

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















