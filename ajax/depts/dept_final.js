var currentView	= 'sequences';
var seqDeroulees	= [];
var selection		= { seq: {}, shot: {} };
var videoIdx		= 0;
var videosDatas	= [];
var videosQueue	= [];
var playedOnce		= false;
var addMessageWip	= false;
var player;

$(function(){
	player = $('#footageResult').get(0);

	/////////////////////////////////////////////////////// VIEW MODE (seq/tags) ////////////////////////////////////////////////
	$('#viewMode').change(function(){
		var tagName = $(this).val();
		if (tagName == currentView) return;
		if (tagName == 'sequences') {
			currentView = 'sequences';
			var params = { projectID: project_ID};
			$('#viewContent').load('modals/depts_shot_specific/dept_final_sequences_list.php', params);
		}
		else {
			currentView = tagName;
			var params = { projectID: project_ID, tagName: tagName};
			$('#viewContent').load('modals/depts_shot_specific/dept_final_bytag_list.php', params);
		}
	});

	/////////////////////////////////////////////////////// CHOIX DES DEPTS ///////////////////////////////////////////////////
	$('#finalLeft').off('change', '#forceDeptAll');
	$('#finalLeft').on('change', '#forceDeptAll', function(){
		var dept = $(this).val();
		$('.forceDept').selectmenu("value", dept);
		modifDeptSelection('seq', 'all', dept);
		modifDeptSelection('shot', 'all', dept);
	});

	/////////////////////////////////////////////////////// COMPUTE FINAL ///////////////////////////////////////////////////
	// Bouton COMPUTE FINAL
	$('.stageContent').off('click', '.computeBtn');
	$('.stageContent').on('click', '.computeBtn', function(){
		if (isCtrl) { console.log(JSON.encode(selection)); } // For debug
		if (playedOnce && !isCtrl) { startVideo(); return; }
		if (selectionEmpty() && !isCtrl) return;
		videosQueue = [];
		player.src = '';
		$('#footageResult').hide();
		$('#msgWait').show();
		var ajaxReq = 'action=getVideosUrls&projID='+project_ID+'&selection='+encodeURIComponent(JSON.encode(selection));
		AjaxJson(ajaxReq, 'depts/final_actions', updateVideoQueue);
	});

	function updateVideoQueue(datas){
		$('#msgWait').hide();
		if (datas.error == 'OK') {
			if (isCtrl) { console.log(datas.selection, datas.newQueue); return; }	// For debug
			$('.computeBtn').removeClass('ui-state-error ui-state-focus ui-state-active ui-state-disabled').addClass('ui-state-activeFake');
			if (datas.newQueue.length >= 1) {
				videosDatas = datas.selection;
				videosQueue = datas.newQueue;
				startVideo();
				playedOnce = true;
			}
			else
				$('#nothingToWatch').html('<p>There is nothing to watch.<br />Please try again later!</p>').show();
		}
		else {
			$('#retourAjax').html('<b>'+datas.message+'</b>').addClass('ui-state-error').show(transition);
			setTimeout(function(){$('#retourAjax').fadeOut(transition*10, function(){$('#retourAjax').html('').removeClass('ui-state-error');});}, 5000);
		}
	}

	function startVideo() {
		videoIdx = 0;
		$('#msgWait').hide();
		$('#nothingToWatch').hide();
		$('#dataResult').html('<font class="micro">['+videosDatas[0][1]+']</font><br />'+videosDatas[0][0]);
		player.src = videosQueue[videoIdx];
		player.load();
		$(player).show();
		player.play();
	}

	$('#footageResult').off("ended");
	$('#footageResult').on("ended", function(){
		if (videoIdx >= videosQueue.length - 1) {
			startVideo();
//			$('.computeBtn').removeClass('ui-state-activeFake');
			return;
		}
		videoIdx++;
		$('#dataResult').html('<font class="micro">['+videosDatas[videoIdx][1]+']</font><br />'+videosDatas[videoIdx][0]);
		player.src = videosQueue[videoIdx];
		player.load();
		player.play();
	});

	/////////////////////////////////////////////////////// SELECTION MANAGER ///////////////////////////////////////////////////

	$('#saveSelectionToDisk').click(function(){
		if (selectionEmpty()) return;
		var d = new Date();
		var defaultName = userLogin + '_' + d.getFullYear() + '-' + (d.getMonth()+1) + '-' + d.getDate();
		var saveName = prompt('Save selection as', defaultName);
		if (saveName == '' || saveName == null) return;
		var ajaxReq = 'action=saveSelection&projID='+project_ID
					+'&selection='+encodeURIComponent(JSON.encode(selection))
					+'&openedSeq='+encodeURIComponent(JSON.encode(seqDeroulees))
					+'&saveName='+saveName;
		AjaxJson(ajaxReq, 'depts/final_actions', retourAjaxFinal, 'saveSel');
	});

	$('#deleteSelectionFromDisk').click(function(){
		var selName = $('#selectionHistory').val();
		if (selName == 'none') return;
		if (!confirm('Delete selection "'+selName+'" ?')) return;
		var ajaxReq = 'action=deleteSelection&projID='+project_ID+'&delName='+selName;
		AjaxJson(ajaxReq, 'depts/final_actions', retourAjaxFinal, 'delSel');
	});

	$('#selectionHistory').change(function(){
		if ($(this).val() == 'none')
			$('#loadSelectionFromDisk, #deleteSelectionFromDisk').addClass('ui-state-disabled');
		else
			$('#loadSelectionFromDisk, #deleteSelectionFromDisk').removeClass('ui-state-disabled');
	});

	$('#loadSelectionFromDisk').click(function(){
		var selName = $('#selectionHistory').val();
		if (selName == 'none') return;
		var ajaxReq = 'action=loadSelection&projID='+project_ID+'&selName='+selName;
		AjaxJson(ajaxReq, 'depts/final_actions', setSelectionFromFile);
	});


	/////////////////////////////////////////////////////// MESSAGES ////////////////////////////////////////////////////////////
	// Ajout de message
	$('#btn_addMessage').click(function(){
		if (addMessageWip) return;
		addMessageWip = true;
		$(this).hide();
		$('#messagesList').prepend(addMessageDiv());
		$('.btnM').button();
		$('#btn_addMessage').addClass('ui-state-activeFake');
	});

	// Validation de l'ajout de message
	$('#messagesList').off('click', '#addMessageValid');
	$('#messagesList').on('click', '#addMessageValid', function(){
		var messageTxt = $('#addMessageTxt').val();
		if (messageTxt == '') { alert('message vide !'); return; }
		addMessageWip = false;
		var repTo = 'false';
		if ($(this).hasClass('responseMode'))
			repTo = $(this).parents('.messageBlock').attr('idMessage');
		var ajaxReq = 'action=addMessage&projID='+project_ID+'&texte='+encodeURIComponent(messageTxt)+'&reponse='+repTo;
		AjaxJson(ajaxReq, 'depts/final_actions', retourAjaxFinal, 'msg');
	});

	// Annulation de l'ajout de message
	$('#messagesList').off('click', '#addMessageAnnul');
	$('#messagesList').on('click', '#addMessageAnnul', function(){
		addMessageWip = false;
		$('#addMessageDiv').remove();
		$('#btn_addMessage').removeClass('ui-state-activeFake');
		$('#btn_addMessage').show();
	});


	// Ajout d'une réponse à un message
	$('.addReponse').click(function(){
		if (addMessageWip) return;
		addMessageWip = true;
		$(this).parents('.messageBlock').append(addMessageDiv('reponseMode'));
		$('.btnM').button();
		$('#btn_addMessage').addClass('ui-state-activeFake');
	});

	// Suppression de message
	$('.delMessage').click(function(){
		var idComm = $(this).attr('idM');
		var ajaxReq = 'action=deleteMessage&idComm='+idComm;
		AjaxJson(ajaxReq, 'depts/final_actions', retourAjaxFinal, 'msg');
	});

});


function listenChanges () {
	$('.watchSelector').off('change');
	$('.watchSelector').on('change', function(){
		var elemType  = $(this).children(':radio:checked').attr('elemType');
		var elemId	  = $(this).children(':radio:checked').attr('elemId');
		var elemWatch = $(this).children(':radio:checked').val();
		if (seqDeroulees.indexOf(elemId) > -1) {
			$('.shotTable[idSeq="'+elemId+'"]').find(':radio:checked').removeAttr('checked');
			$('.shotTable[idSeq="'+elemId+'"]').find(':radio[value="'+elemWatch+'"]').attr('checked', 'checked');
			$('.shotTable[idSeq="'+elemId+'"]').find('.watchSelector').buttonset("refresh");
		}
		if (elemType == 'shot') {
			var seqID = $(this).parents('.shotTable').attr('idSeq');
			$('.seqLine[idSeq="'+seqID+'"]').find(':radio:checked').removeAttr('checked');
			$('.seqLine[idSeq="'+seqID+'"]').find('.watchSelector').addClass('bordHi2 ui-corner-all').buttonset("refresh");
		}
		$('#watchSelectAll').find(':radio:checked').removeAttr('checked');
		$('#watchSelectAll').buttonset("refresh");
		refreshWatchesSelection();
	});
	$('.forceDept').off('change');
	$('.forceDept').on('change', function(){
		var dept = $(this).val();
		var type = $(this).attr('itemType');
		var id = $(this).attr('itemID');
		if (type == 'seq') {
			$('tr[idSeq="'+id+'"]').find('.forceDept').selectmenu("value", dept).each(function(i,e){
				modifDeptSelection('shot', $(e).attr('itemID'), dept);
			});
		}
		modifDeptSelection(type, id, dept);
	});
}

function selectionEmpty() {
	return !(Object.keys(selection['seq']).length > 0 || Object.keys(selection['shot']).length > 0);
}
function playerRunning() {
	return $('#footageResult').get(0).ended == false && $('#footageResult').get(0).src != "";
}
function playerEnded() {
	return $('#footageResult').get(0).ended == true && $('#footageResult').get(0).src != "";
}

function refreshWatchesSelection () {
	$('#sequencesListe').find(':radio:checked').each(function(){
		var elemType  = $(this).attr('elemType');
		var elemId    = $(this).attr('elemId');
		if (seqDeroulees.indexOf(elemId) > -1) return;
		var elemWatch = $(this).val();
		var dept	  = $(this).parents('.'+elemType+'Table').find('.forceDept').val();
		if (elemWatch == 'watch')   addWatchToSelection(elemType,elemId,dept);
		if (elemWatch == 'unwatch') removeWatchToSelection(elemType,elemId);
		modifDeptSelection(elemType, elemId, dept);
	});
	if (!selectionEmpty()) {
		$('#nothingToWatch').html(
			'<p><br /><br /><br /><br /><br /><br /><br /><br />'
			+	'<span class="giant "><button class="bouton computeBtn"><span class="ui-icon ui-icon-play"></span></button></span>'
			+'</p>');
		$('#finalVideo').css('background-image', 'none');
		$('.bouton').button();
	}
	else {
		if (!playerRunning()) {
			$('#finalVideo').css('background-image', "url('../gfx/novignette/novignette_video.png')");
			$('#nothingToWatch').html('<p>Please choose <br />some sequences to watch.</p>');
		}
	}
}

function addWatchToSelection (type, id, dept) {
	if (!selection[type][id])
		selection[type][id] = dept;
}
function removeWatchToSelection (type, id) {
	delete selection[type][id];
}
function modifDeptSelection (type, id, dept) {
	$('#selectionHistory').selectmenu('value', 'none');
	if (selectionEmpty()) {
		$('#saveSelectionToDisk').addClass('ui-state-disabled');
		if (playerRunning()) {
			$('.computeBtn').removeClass('ui-state-disabled ui-state-error').addClass('ui-state-activeFake');
			playedOnce = true;
		}
		else
			$('.computeBtn').removeClass('ui-state-activeFake ui-state-error').addClass('ui-state-disabled');
	}
	else {
		$('#saveSelectionToDisk').removeClass('ui-state-disabled');
		if (playerRunning())
			$('.computeBtn').removeClass('ui-state-disabled ui-state-activeFake').addClass('ui-state-error');
		else
			$('.computeBtn').removeClass('ui-state-activeFake ui-state-disabled ui-state-error');
		playedOnce = false;
	}
	if (id == 'all') {
		$.each(selection[type], function(_id,i){
			selection[type][_id] = dept;
		});
		return;
	}
	if (selection[type][id])
		selection[type][id] = dept;
}

function setSelectionFromFile (datas) {
	if (datas.error == 'OK') {
		console.log('LOADING selection "'+datas.selName+'"...');
		var dataLoaded = $.parseJSON(datas.selectionData);
		if (dataLoaded == null) {
			$('#retourAjax').html('<b>Invalid JSON data!</b>').addClass('ui-state-error').show(transition);
			setTimeout(function(){$('#retourAjax').fadeOut(transition*10, function(){$('#retourAjax').html('').removeClass('ui-state-error');});}, 5000);
			return;
		}
		$('#loadSelectionFromDisk').hide();
		$('#loadSelWaiter').show();
		$('.detailSeq').hide();
		$('tr').removeClass('ui-state-focusFake');
		selection = { seq: {}, shot: {} };
		$('#sequencesListe').find(':radio[value="watch"]').removeAttr('checked');
		$('#sequencesListe').find(':radio[value="unwatch"]').attr('checked', 'checked');
		$('#sequencesListe').find('.watchSelector').buttonset("refresh");
		var openedSeq = dataLoaded.openSeq;
		var selLoaded = dataLoaded.selection;
		$.each(selLoaded.seq, function(seq,dept){
			$('.seqLine[idSeq="'+seq+'"]').find(':radio[value="watch"]').attr('checked', 'checked');
			$('.seqLine[idSeq="'+seq+'"]').find('.watchSelector').buttonset("refresh");
			$('.seqLine[idSeq="'+seq+'"]').find('.forceDept').selectmenu("value", dept);
			addWatchToSelection('seq', seq, dept);
			modifDeptSelection('seq', seq, dept);
		});
		if (openedSeq.length == 0) {
			$('#selectionHistory').selectmenu('value', datas.selName);
			$('#loadSelectionFromDisk').show();
			$('#loadSelWaiter').hide();
			return;
		}
		isShift = true;
		$.each(openedSeq, function(i,seq){
			$('.seqLine[idSeq="'+seq+'"]').children('td').first().click();
		});
		setTimeout(function(){
			isShift = false;
			$.each(selLoaded.shot, function(shot,dept){
				$('.shotTable[idShot="'+shot+'"]').find(':radio[value="watch"]').attr('checked', 'checked');
				$('.shotTable[idShot="'+shot+'"]').find('.watchSelector').buttonset("refresh");
				$('.shotTable[idShot="'+shot+'"]').find('.forceDept').selectmenu("value", dept);
				addWatchToSelection('shot', shot, dept);
				modifDeptSelection('shot', shot, dept);
			});
			$('#selectionHistory').selectmenu('value', datas.selName);
			$('#loadSelectionFromDisk').show();
			$('#loadSelWaiter').hide();
			console.log('DONE');
		}, 1500);
	}
	else {
		$('#retourAjax').html('<b>'+datas.message+'</b>').addClass('ui-state-error').show(transition);
		setTimeout(function(){$('#retourAjax').fadeOut(transition*10, function(){$('#retourAjax').html('').removeClass('ui-state-error');});}, 5000);
	}
}


function addMessageDiv(mode) {
	var widthTxt = '380px';
	var margeTxt = '';
	var classBtn = '';
	if (mode !== undefined && mode == 'reponseMode') {
		widthTxt = '330px';
		margeTxt = 'marge30l';
		classBtn = 'responseMode';
	}
	return '<div class="margeTop10" id="addMessageDiv">'
		+	'<textarea class="ui-corner-all ui-corner-all fondSect3 noBorder pad3 '+margeTxt+'" style="width:'+widthTxt+'; resize:none;" rows="6" id="addMessageTxt"></textarea>'
		+	'<div class="inline bot nano">'
		+		'<p class="pad3 enorme ui-state-disabled">Écrivez votre<br />message,<br />puis validez.</p>'
		+		'<button class="btnM '+classBtn+'" id="addMessageValid"><span class="ui-icon ui-icon-check" title="valider"></span></button>'
		+		'<button class="btnM '+classBtn+'" id="addMessageAnnul"><span class="ui-icon ui-icon-cancel" title="annuler"></span></button>'
		+	'</div>'
		+'</div>';
}

function retourAjaxFinal (datas, type) {
	if (datas.error == 'OK') {
		if (type == 'msg')
			loadPageContent($('.deptBtn[label="final"]'));
		if (type == 'saveSel') {
			$('#retourAjax').html(datas.message).removeClass('ui-state-error').addClass('ui-state-highlight').show(transition);
			$('#selectionHistory').append('<option value="'+datas.saveName+'" selected>'+datas.saveName+'</option>').selectmenu('destroy').selectmenu({style: 'dropdown'});
			setTimeout(function(){ $('#retourAjax').fadeOut(transition); }, 2000);
		}
		if (type == 'delSel') {
			$('#retourAjax').html(datas.message).removeClass('ui-state-error').addClass('ui-state-highlight').show(transition);
			$('#selectionHistory').find('option[value="'+datas.delName+'"]').remove();
			$('#selectionHistory').selectmenu('destroy').selectmenu({style: 'dropdown'});
			$('#loadSelectionFromDisk, #deleteSelectionFromDisk').addClass('ui-state-disabled');
			setTimeout(function(){ $('#retourAjax').fadeOut(transition); }, 2000);
		}
	}
	else {
		$('#retourAjax').html('<b>'+datas.message+'</b>').addClass('ui-state-error').show(transition);
		setTimeout(function(){$('#retourAjax').fadeOut(transition*10, function(){$('#retourAjax').html('').removeClass('ui-state-error');});}, 5000);
	}
}