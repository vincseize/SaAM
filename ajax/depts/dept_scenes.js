
var oldDescr;
addSceneAuth = true;
var assignShotAuth = true;

// Document ready
$(function() {

	recalc_scrolls_scene();

	// Boutons des menus en haut
	$('#sceneDetails').off('click', '.menuSceneBtn');
	$('#sceneDetails').on('click', '.menuSceneBtn', function(){
		var type   = $(this).attr('type');
		var toShow = $(this).attr('show');
		localStorage['disp_scenes_'+type] = toShow;
		if (type == 'parent') {
			$('.iScParentDiv').hide();
			$('#sectionSceneParent').find('.menuSceneBtn').removeClass('shadowOut fondHigh');
			$(this).addClass('shadowOut');
		}
		if (type == 'selected') {
			$('.iScSelectedDiv').hide();
			$('#sectionSceneSelect').find('.menuSceneBtn').removeClass('fondHigh');
		}
		$(this).addClass('fondHigh');
		$('#'+toShow).show();
	});

	// Pour griser les boutons de départements si pas d'infos pour cette scene
	if (window['sceneID'] && window['disabledDepts']) {
		$('.deptBtn[content="05_scenes"]').addClass('ui-state-disabled noBG noBorder');
		$.each(disabledDepts, function(i, dept) {
			$('#scenes_depts').find('.deptBtn[label="'+dept+'"]').removeClass('ui-state-disabled noBG noBorder');
		});
		$('#scenes_depts').find('.deptBtn[label="'+dept+'"]').removeClass('ui-state-disabled noBorder');
	}

	$('.sceneDescriptionDiv').slimScroll({
		position: 'right',
		height: '100px',
		size: '10px',
		wheelStep: 10,
		railVisible: true
	});

	/////////////////////////////////////////////////////////////////////////// MODIFICATIONS SCÈNE

	// LOCK de scene
	$('#sectionSceneSelect').off('click', '#lockScene');
	$('#sectionSceneSelect').on('click', '#lockScene', function(){
		if (!confirm('LOCK scene ?')) return;
		var ajaxReq = "action=modifSceneLock&sceneID="+sceneID+"&lock=1";
		AjaxJson(ajaxReq, "depts/scenes_actions", retourAjaxScenes, true);
	});
	// UNLOCK de scene
	$('#sectionSceneSelect').off('click', '#unlockScene');
	$('#sectionSceneSelect').on('click', '#unlockScene', function(){
		if (!confirm('UNLOCK scene ?')) return;
		var ajaxReq = "action=modifSceneLock&sceneID="+sceneID+"&lock=0";
		AjaxJson(ajaxReq, "depts/scenes_actions", retourAjaxScenes, true);
	});
	// ARCHIVE de scene
	$('#sectionSceneSelect').off('click', '#archiveScene');
	$('#sectionSceneSelect').on('click', '#archiveScene', function(){
		if (!confirm('ARCHIVE scene ?')) return;
		var ajaxReq = "action=modifSceneArchive&sceneID="+sceneID+"&archive=1";
		AjaxJson(ajaxReq, "depts/scenes_actions", retourAjaxScenes, 'reload');
	});
	// RESTORE de scene
	$('#sectionSceneSelect').off('click', '#restoreScene');
	$('#sectionSceneSelect').on('click', '#restoreScene', function(){
		if (!confirm('RESTORE scene ?')) return;
		var ajaxReq = "action=modifSceneArchive&sceneID="+sceneID+"&archive=0";
		AjaxJson(ajaxReq, "depts/scenes_actions", retourAjaxScenes, 'reload');
	});


	// Modif du status de la scene
	$('#sectionSceneSelect').off('click', '.modifSceneStatus');
	$('#sectionSceneSelect').on('click', '.modifSceneStatus', function(){
		var idNewStatus = $(this).attr('idStatus');
		var ajaxReq = "action=modifSceneStatus&sceneID="+sceneID+"&idProj="+idProj+"&idDept="+deptID+"&idNewStatus="+idNewStatus;
		AjaxJson(ajaxReq, "depts/scenes_actions", retourAjaxScenes, true);
	});

	// Click sur modif description
	$('#sectionSceneSelect').off('click', '#modifDescrBtn');
	$('#sectionSceneSelect').on('click', '#modifDescrBtn', function(){
		$(this).hide();
		$(this).parent('div').find('.modifBtns').show();
		oldDescr = $('#sceneDescription').html();
		var oldDescrTxt = oldDescr.replace(/<br\s*\/?>/mg, "\r");
		if(oldDescrTxt == '<span class="ui-state-disabled"><i>No description</i></span>') oldDescrTxt = '';
		$('#sceneDescription').html('<textarea class="fondSect3 ui-corner-all noBorder w9p noMarge pad3" rows="5" id="newDescrScene">'+oldDescrTxt+'</textarea>');
	});
	// Validation modif descritpion
	$('#sectionSceneSelect').off('click', '#modifDescrOK');
	$('#sectionSceneSelect').on('click', '#modifDescrOK', function(){
		var newDescr   = $('#newDescrScene').val();
		if (newDescr == oldDescr) { $('#modifDescrANNUL').click(); return; }
		var ajaxReq = "action=modifSceneDescr&sceneID="+sceneID+"&newDescr="+encodeURIComponent(newDescr);
		AjaxJson(ajaxReq, "depts/scenes_actions", retourAjaxScenes, true);
	});
	// Annulation modif description
	$('#sectionSceneSelect').off('click', '#modifDescrANNUL');
	$('#sectionSceneSelect').on('click', '#modifDescrANNUL', function(){
		$(this).parent('div').hide();
		$('#modifDescrBtn').show();
		$('#sceneDescription').html(oldDescr);
	});

	// Modif (assignation) Handler
	$('#sectionSceneSelect').off('click', '#btn_HandleScene');
	$('#sectionSceneSelect').on('click', '#btn_HandleScene', function(){
		if (!authHandling) return;
		var wi = parseInt($('#scene_hungBy').width());
		var le = wi - 145;
		var sH = parseInt($('#sectionSceneSelect').height());
		$('#handlerModifBox').css({top: '20px', left: le+'px'}).show(transition, function(){
			$('.handlersList').slimScroll({position: 'right', height: sH+'px', size: '10px', wheelStep: 10, railVisible: true});
		});
	});
	$('#sectionSceneSelect').off('click', '.handlerModifLine');
	$('#sectionSceneSelect').on('click', '.handlerModifLine', function(){
		if ($(this).hasClass('ui-state-highlight')) return;
		var idNewHandler = $(this).attr('idHandler');
		var ajaxReq = "action=modifSceneHandler&idProj="+idProj+"&sceneID="+sceneID+"&idNewHandler="+idNewHandler;
		AjaxJson(ajaxReq, "depts/scenes_actions", retourAjaxScenes, true);
	});

	// Fermeture d'une modif box
	$('#sectionSceneSelect').off('click', '.closeModifBox');
	$('#sectionSceneSelect').on('click', '.closeModifBox', function(){
		$('#categModifBox').hide(transition);
		$('#handlerModifBox').hide(transition);
	});

	// BOUTON ajout d'artiste à la liste de l'équipe de la scene
	$('#sectionSceneSelect').off('click', '#showAddArtistToScene');
	$('#sectionSceneSelect').on('click', '#showAddArtistToScene', function(){
		if ($(this).find('span').hasClass('ui-icon-minus')) {
			$('#addArtistToSceneDiv').hide(transition);
			$(this).find('span').removeClass('ui-icon-minus');
		}
		else {
			$('.showModif, .infoDiv').show(); $('.modifDiv').hide();
			$('#addArtistToSceneDiv').show(transition);
			$(this).find('span').addClass('ui-icon-minus');
		}
	});
	$('#sectionSceneSelect').off('click', '#addArtistToSceneBtn');
	$('#sectionSceneSelect').on('click', '#addArtistToSceneBtn', function(){
		var artistToAdd = $('#addArtistToSceneInput').val();
		var jsonArtists = JSON.encode(artistToAdd);
		var ajaxReq = 'action=modSceneTeam&sceneID='+sceneID+'&newTeam='+jsonArtists;
		AjaxJson(ajaxReq, 'depts/scenes_actions', retourAjaxScenes, true);
		$('#showAddArtistToScene').click();
	});

	// BOUTON show modif input
	$('#sectionSceneSelect').off('click', '.showModif');
	$('#sectionSceneSelect').on('click', '.showModif', function(){
		var dataMod = $(this).attr('dataMod');
		$('.showModif, .infoDiv').show(); $('.modifDiv').hide();
		$(this).hide(); $('.infoDiv[infoDiv="'+dataMod+'"]').hide();
		$('.modifDiv[dataMod="'+dataMod+'"]').show().find('input').focus();
	});
	// BOUTON validation modif
	$('#sectionSceneSelect').off('click', '.validModif');
	$('#sectionSceneSelect').on('click', '.validModif', function(){
		var dataMod = $(this).attr('dataMod');
		var valMod  = $(this).parents('.modifDiv').find('input, select').val();
		var ajaxReq = 'action=modSceneInfo&sceneID='+sceneID+'&keyMod='+dataMod+'&valMod='+valMod;
		AjaxJson(ajaxReq, 'depts/scenes_actions', retourAjaxScenes, true);
	});
	// BOUTON annulation modif
	$('#sectionSceneSelect').off('click', '.annulModif');
	$('#sectionSceneSelect').on('click', '.annulModif', function(){
		$('.showModif, .infoDiv').show();
		$('.modifDiv').hide();
	});


	/////////////////////////////////////////////////////////////////////////// DERIVÉES

	// Show div ajout scene fille
	$('#sectionSceneSelect').off('click', '#addDerivBtn');
	$('#sectionSceneSelect').on('click', '#addDerivBtn', function(){
		if (addSceneAuth == false) return;
		$(this).hide();
		$('#addDerivDiv').show(transition);
		addSceneAuth = false;
	});

	// Bouton valide ajout derivée
	$('#sectionSceneSelect').off('click', '#addDerivValid');
	$('#sectionSceneSelect').on('click', '#addDerivValid', function(){
		var infos = getAddDerivValues();
		var ajaxStr = 'action=addScene&projID='+idProj+'&infos='+encodeURIComponent(JSON.encode(infos));
//		console.log(infos);
		AjaxJson(ajaxStr, 'depts/scenes_actions', retourAjaxMsg, true);
		$('#addDerivDiv').hide(transition);
		$('#addDerivBtn').show(transition);
		addSceneAuth = true;
	});

	// Bouton cancel ajout derivée
	$('#sectionSceneSelect').off('click', '#addDerivCancel');
	$('#sectionSceneSelect').on('click', '#addDerivCancel', function(){
		$('#addDerivDiv').hide(transition);
		$('#addDerivBtn').show(transition);
		addSceneAuth = true;
	});


	/////////////////////////////////////////////////////////////////////////// ASSETS

	var winW = $('#bigDiv').width() - 220;
	var winH = $('#bigDiv').height() - 100;

	// Bouton manage assets MASTER (adding assets)
	$('#sceneDetails').off('click', '.addAssetsBtn');
	$('#sceneDetails').on('click', '.addAssetsBtn', function(){
		var idScene = $(this).attr('sceneID');
		var titleScene = $(this).attr('sceneTitle');
		var params = {sceneID: idScene, idProj: idProj, deptID: deptID};
		$('#managersModal').load('modals/scenes_specific/scenes_assets_manager_add.php', params, function(){
			var $dialog = $('#managersModal');
			$dialog.dialog({
				autoOpen: true, height: winH, width: winW, modal: true, hide: "fade", title: "Add assets to MASTER scene <span class='marge30l colorErrText'>" + titleScene + "</span>",
				open: function() {
					$dialog.find('#projectAssetsList').slimScroll({ position: 'right', height: (winH - 125)+'px', size: '10px', wheelStep: 10, railVisible: true });
					$dialog.find('#sceneAssetsList').slimScroll({ position: 'right', height: (winH - 125)+'px', size: '10px', wheelStep: 10, railVisible: true });
				},
				buttons: {
					'OK' : function() {
						var assetSceneListStr = encodeURIComponent(JSON.encode(assetSceneList));
						var ajaxStr = 'action=modSceneAssets&sceneID='+sceneID+'&newAssetsList='+assetSceneListStr;
						AjaxJson(ajaxStr, 'depts/scenes_actions', retourAjaxScenes, 'reload');
						$(this).dialog('close');
					},
					'Cancel' : function() { assetSceneList = []; $(this).dialog('close');  }
				},
				close: function() { $dialog.html(''); }
			});
		});
	});

	// Bouton manage assets DERIVEES (excluding assets)
	$('#sectionSceneSelect').off('click', '#exclAssetsBtn');
	$('#sectionSceneSelect').on('click', '#exclAssetsBtn', function(){
		var params = {sceneID: sceneID, idProj: idProj, deptID: deptID};
		$('#managersModal').load('modals/scenes_specific/scenes_assets_manager_exclude.php', params, function(){
			var $dialog = $('#managersModal');
			$dialog.dialog({
				autoOpen: true, height: winH, width: winW, modal: true, hide: "fade", title: "Assets for derivative",
				open: function() {
					$dialog.find('#projectAssetsList').slimScroll({ position: 'right', height: (winH - 155)+'px', size: '10px', wheelStep: 10, railVisible: true });
					$dialog.find('#sceneAssetsList').slimScroll({ position: 'right', height: (winH - 155)+'px', size: '10px', wheelStep: 10, railVisible: true });
				},
				buttons: {
					'OK' : function() {
						var assetSceneListStr = encodeURIComponent(JSON.encode(assetSceneListExclude));
						var ajaxStr = 'action=modSceneAssets&sceneID='+sceneID+'&newAssetsList='+assetSceneListStr;
						AjaxJson(ajaxStr, 'depts/scenes_actions', retourAjaxScenes, 'reload');
						$(this).dialog('close');
					},
					'Cancel' : function() { $(this).dialog('close');  }
				},
				close: function() { $dialog.html(''); }
			});
		});
	});


	/////////////////////////////////////////////////////////////////////////// SHOTS

	// Show modal assignation shots depuis MASTER
	$('#sceneDetails').off('click', '.assignMasterShotsBtn');
	$('#sceneDetails').on('click', '.assignMasterShotsBtn', function(){
		var idScene = $(this).attr('sceneID');
		var titleScene = $(this).attr('sceneTitle');
		var params = {sceneID: idScene, idProj: idProj};
		$('#managersModal').load('modals/scenes_specific/scenes_master_shots_manager.php', params, function(){
			var $dialog = $('#managersModal');
			$dialog.dialog({
				autoOpen: true, height: winH, width: winW, modal: true, title: "Manage shots in derivatives of <span class='colorErrText'>" + titleScene + "</span>",
				open: function() {
					$dialog.find('#derivList').slimScroll({ position: 'right', height: (winH - 125)+'px', size: '10px', wheelStep: 10, railVisible: true });
					$dialog.find('#shotsList').slimScroll({ position: 'right', height: (winH - 125)+'px', size: '10px', wheelStep: 10, railVisible: true });
				},
				buttons: { 'OK' : function() {  $(this).dialog('close'); $('#scenes_depts').find('.deptBtn[label="'+dept+'"]').click(); } },
				close: function() { $dialog.html(''); }
			});
		});
	});

	// Show div assignation shot
	$('#sectionSceneSelect').off('click', '#assignShotBtn');
	$('#sectionSceneSelect').on('click', '#assignShotBtn', function(){
		if (assignShotAuth == false) return;
		$('.filleBoutonsDiv').hide();
		$('#assignShotDiv').show(transition);
		assignShotAuth = false;
	});

	// Choix de la séquence
	$('#sectionSceneSelect').off('change','#assignSceneSeq');
	$('#sectionSceneSelect').on('change','#assignSceneSeq', function(){
		var idSeq = $(this).val();
		if (idSeq == '0') return;
		$('.assignSceneShotDiv').hide();
		$('.assignSceneShotDiv[seqID="'+idSeq+'"]').show();
		$('#noSeqMsg').hide();
	});

	// Choix du shot
	$('#sectionSceneSelect').off('change','.assignSceneShot');
	$('#sectionSceneSelect').on('change','.assignSceneShot', function(){
		var value = $(this).val();
		if (value == '0') return;
		$('#assignShotValid').show();
	});

	// Bouton valide assignation shot
	$('#sectionSceneSelect').off('click', '#assignShotValid');
	$('#sectionSceneSelect').on('click', '#assignShotValid', function(){
		var idSeq  = $('#assignSceneSeq').val();
		var idShot = $('.assignSceneShotDiv[seqID="'+idSeq+'"]').find('.assignSceneShot').val();
		var ajaxStr = 'action=assignShot&projID='+idProj+'&sceneID='+sceneID+'&seqID='+idSeq+'&shotID='+idShot;
		AjaxJson(ajaxStr, 'depts/scenes_actions', retourAjaxScenes, 'reload');
		$('#assignShotDiv').hide(transition);
		$('.filleBoutonsDiv').show(transition);
		assignShotAuth = true;
	});

	// Bouton cancel assignation shot
	$('#sectionSceneSelect').off('click', '#assignShotCancel');
	$('#sectionSceneSelect').on('click', '#assignShotCancel', function(){
		$('#assignShotDiv').hide(transition);
		$('.filleBoutonsDiv').show(transition);
		assignShotAuth = true;
	});

	// Boutons remove assignations shots
	$('#sectionSceneSelect').off('click','.sceneRemoveAssignedShot');
	$('#sectionSceneSelect').on('click','.sceneRemoveAssignedShot', function(){
		var idSeq  = $(this).parents('.sceneShotOpener').attr('seqID');
		var idShot = $(this).parents('.sceneShotOpener').attr('shotID');
		var idScene= $(this).parents('.sceneShotOpener').attr('sceneID');
		var ajaxStr = 'action=removeShot&projID='+idProj+'&sceneID='+idScene+'&seqID='+idSeq+'&shotID='+idShot;
		AjaxJson(ajaxStr, 'depts/scenes_actions', retourAjaxScenes, 'reload');
	});


	// Ouverture d'un asset
	$('#sceneDetails').off('click', '.assetItemScene');
	$('#sceneDetails').on('click', '.assetItemScene', function(){
		if(!localStorage['lastDeptMyAsset'])
			localStorage['lastDeptMyAsset'] = 'concept';
		localStorage['lastGroupDepts_'+idProj] = "assets";
		localStorage['lastDept_'+idProj+'_GRP_assets'] = localStorage['lastDeptMyAsset'];
		localStorage['activeBtn_'+idProj]	= '06_assets';
		localStorage['lastDept_'+idProj]	= localStorage['lastDeptMyAsset'];
		localStorage['openAsset_'+idProj]	= $(this).attr('filename');
		localStorage['openAssetPath_'+idProj]	= $(this).attr('filePath');
		$('#selectDeptsList').val('assets').change();
		$('#assets_depts').find('.deptBtn[label="'+localStorage['lastDeptMyAsset']+'"]').click();
	});

	// Ouverture d'un shot
	$('#sceneDetails').off('click', '.sceneShotOpener img');
	$('#sceneDetails').on('click', '.sceneShotOpener img', function(){
		seq_ID		= $(this).parents('.sceneShotOpener').attr('seqID');
		shot_ID		= $(this).parents('.sceneShotOpener').attr('shotID');
		if(!localStorage['lastDeptMyShot']) {
			localStorage['lastTplMyShot'] = 'dept_storyboard';
			localStorage['lastDeptMyShot'] = 'storyboard';
		}
		localStorage['lastGroupDepts_'+idProj] = "shots";
		localStorage['lastDept_'+idProj+'_GRP_shots'] = localStorage['lastTplMyShot'];
		localStorage['activeBtn_'+idProj]	= localStorage['lastTplMyShot'];
		localStorage['lastDept_'+idProj]	= localStorage['lastDeptMyShot'];
		localStorage['openSeq_'+idProj]		= seq_ID;
		localStorage['openShot_'+idProj]	= shot_ID;
		$('#selectDeptsList').val('shots').change();
		$('#shots_depts').find('.deptBtn[label="'+localStorage['lastDeptMyShot']+'"]').click();
	});

	// Gestion des cameras (scènes filles seulement)
	$('#sceneDetails').off('click', '#manageCamsBtn');
	$('#sceneDetails').on('click', '#manageCamsBtn', function(){
		var $dialogCams = $('#camerasManager');
		$dialogCams.dialog({
			autoOpen: true, height: winH/1.5, width: 600, modal: true, title: "Manage cameras of derivative <span class='colorBtnFake'>" + sceneTitle + "</span>",
			open: function() {
//				$dialog.find('#derivList').slimScroll({ position: 'right', height: (winH/2 - 125)+'px', size: '10px', wheelStep: 10, railVisible: true });
//				$dialog.find('#shotsList').slimScroll({ position: 'right', height: (winH/2 - 125)+'px', size: '10px', wheelStep: 10, railVisible: true });
			},
			buttons: { 'OK' : function() { $(this).dialog('close'); $('#scenes_depts').find('.deptBtn[label="'+dept+'"]').click(); } },
			close: function() { $dialogCams.dialog('destroy'); }
		});
	});

	// Ajout de cam
	$('body').off('click', '#addCameraBtn');
	$('body').on('click', '#addCameraBtn', function(){
		$(this).hide();
		$(this).parents('#camerasManager').find('#addCameraDiv').show(transition);
	});

	// Validation ajout de cam
	$('body').off('click', '#addCameraValide');
	$('body').on('click', '#addCameraValide', function(){
		var theDiv = $(this).parents('#camerasManager');
		var newCamName = theDiv.find('#addCameraName').val();
		var ajaxStr = 'action=addCamera&projID='+idProj+'&sceneID='+sceneID+'&camName='+newCamName;
		AjaxJson(ajaxStr, 'depts/scenes_actions', retourAjaxScenes, 'addCameraToList');
		theDiv.find('#addCameraDiv').hide();
		theDiv.find('#addCameraBtn').show();
	});
	// Annulation ajout de cam
	$('body').off('click', '#addCameraCancel');
	$('body').on('click', '#addCameraCancel', function(){
		$(this).parents('#camerasManager').find('#addCameraDiv').hide();
		$(this).parents('#camerasManager').find('#addCameraBtn').show();
	});

	// Assignation de cam (menu dropdown)
	$('#sceneDetails').off('change', '.assignCamShot');
	$('#sceneDetails').on('change', '.assignCamShot', function(){
		var filleID = $(this).parents('.sceneShotOpener').attr('sceneID');
		var seqID   = $(this).parents('.sceneShotOpener').attr('seqID');
		var shotID  = $(this).parents('.sceneShotOpener').attr('shotID');
		var camID   = $(this).val();
		var ajaxStr = 'action=assignCamera&projID='+idProj+'&shotID='+shotID+'&camID='+camID;
		AjaxJson(ajaxStr, 'depts/scenes_actions', retourAjaxScenes, 'resetCam');
	});

});
// FIN document ready


var timerAlert;
function retourAjaxScenes (datas, reload) {
	clearTimeout(timerAlert);
	if (datas.error == 'OK') {
		$('#retourAjax').html('<b>'+datas.message+'</b>').removeClass('ui-state-error').addClass('ui-state-highlight').show(transition);
		if (reload == 'reload')
			$('#scenes_depts').find('.deptBtn[label="'+dept+'"]').click();
		else if (reload == 'addCameraToList') {
			$('body').find('#modalCamerasList').append(
					'<div class="ui-state-default pad5">'
						+'<div class="floatR nano">'
							+'<span class="inline mid ui-state-disabled terra"><i>No Sequence - No Shot</i></span>'
							+'<button class="inline mid marge10l bouton"><span class="ui-icon ui-icon-trash"></span></button>'
						+'</div>'
						+'<div class="gras">'+datas.newCamName+'</div>'
					+'</div>');
			$('.bouton').button();
		}
		else if (reload === true)
			openSceneRight(sceneID);
		timerAlert = setTimeout(function(){$('#retourAjax').fadeOut(transition*2, function(){$('#retourAjax').html('').removeClass('ui-state-error');});}, 4000);
	}
	else {
		if (reload == 'resetCam')
			$('#scenes_depts').find('.deptBtn[label="'+dept+'"]').click();
		$('#retourAjax').html('<div class="marge10l doigt floatR" onClick="fermeRA()"><span class="ui-icon ui-icon-close"></span></div><b>'+datas.message+'</b>').addClass('ui-state-error').show(transition);
	}
}

function fermeRA () {
	$('#retourAjax').hide();
}

function recalc_scrolls_scene(){
	var sceneViewH = $('#sceneViewLeft').height();
	$('#messagesList').slimScroll({
		position: 'right',
		height: sceneViewH+'px',
		size: '10px',
		wheelStep: 10,
		railVisible: true
	});
	$('#retakesList').slimScroll({
		position: 'left',
		height: sceneViewH+'px',
		size: '10px',
		wheelStep: 10,
		railVisible: true
	});
}