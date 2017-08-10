
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
				var ajaxReq = "action=moveVignette&idShot="+shot_ID+"&dept="+departement+"&vignetteName="+decodeURI(retour[0].name);
				AjaxJson(ajaxReq, "admin/admin_shots_actions", retourAjaxStructure, 'reloadShot');
			}
		}
	});

	// Traitement de la modif de champ decTech
	$('.modDectechItem').click(function(){
		if (modDecTechWIP) return;
		var itemCateg = $(this).attr('categTech');
		var itemType = $(this).attr('typeTech');
		var itemPos = $(this).attr('posTech');
		var itemID  = 'item_'+itemType;
		var oldTxt  = $('#'+itemID).html();
		$('#'+itemID).html('<textarea class="noBorder fondPage ui-corner-all pad3 colorHard" id="textTechMod">'+oldTxt+'</textarea>');
		$(this).parent('div').html(
			 '<button class="ui-state-activeFake btn" id="confirmMod" categTech="'+itemCateg+'" type="'+itemType+'" posTech="'+itemPos+'" title="confirmer la modif"><span class="ui-icon ui-icon-check"></span></button> '
			+'<button class="ui-state-error btn" id="cancelMod" title="annuler la modif"><span class="ui-icon ui-icon-cancel"></span></button> '
		);
		$('.btn').button();
		$('#textTechMod').focus();
		modDecTechWIP = true;
		$('.modDectechItem[typeTech!="'+itemType+'"]').addClass('ui-state-disabled');
	});

	// Validation de la modif
	$('#shotView').off('click', '#confirmMod');
	$('#shotView').on('click', '#confirmMod', function(){
		var categTech = $(this).attr('categTech');
		var typeTech = $(this).attr('type');
		var posTech = $(this).attr('posTech');
		var txtTech  = $('#textTechMod').val();
		var ajaxReq  = 'action=modifDecTechValue&dirShot='+dir_shot+'&categ='+categTech+'&type='+typeTech+'&pos='+posTech+'&txt='+encodeURIComponent(txtTech);
		console.log(ajaxReq);
		AjaxJson(ajaxReq, 'depts/dectech_actions', retourAjaxStructure, 'reloadShot');
	});

	// Annulation de la modif
	$('#shotView').off('click', '#cancelMod');
	$('#shotView').on('click', '#cancelMod', function(){
		modDecTechWIP = false;
		loadPageContentModal('depts_shot_specific/dept_dectech_shots', {projectID: project_ID, sequenceID: seq_ID, shotID: shot_ID});
	});


});
// FIN DU DOCUMENT READY


