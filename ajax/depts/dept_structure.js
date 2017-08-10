
$(function () {

	// Réarrangement de la position des séquences
	$('#liste').sortable({
		placeholder: 'ui-state-highlight',
		forcePlaceholderSize: true,
		axis: 'y',
		helper : 'clone',
		update: function(e, ui) {
			var posArr = {}; var i = 1;
			$('li[idSeq]').each(function(){
				posArr[$(this).attr('idSeq')] = i;
				i++;
			});
			ajaxUpdateSeqPos(posArr);
		}
	});


	// Déroulage des infos des séquences
	$('.seqTable td:not(.nowrap)').click(function(){
		$('.listeShots').sortable("destroy");
		if (!$(this).parent().hasClass('ui-state-focusFake')) {
			$('tr').removeClass('ui-state-focusFake');
			$('.detailSeq').hide();
			var idSeq = $(this).parents('li').attr('idSeq');
			var params = { projectID: project_ID, sequenceID: idSeq };
			$(this).parent().addClass('ui-state-focusFake').next('tr').load('modals/depts_shot_specific/dept_structure_project_shots.php', params).show();
			$('#liste').sortable("disable");
			localStorage['openSeq_'+project_ID] = idSeq;
		}
		else {
			$('tr').removeClass('ui-state-focusFake');
			$('.detailSeq').hide();
			$('#liste').sortable("enable");
			localStorage.removeItem('openSeq_'+project_ID);
		}
	});

	// Empêche les drop de fichier dans le browser
	$(document).bind('drop dragover', function (e) {
		e.preventDefault();
	});


	//////////////////////////////////////////////////////// BOUTONS PROJET ////////////////////////////////////////////////////////////////////////////////

	$('#btn_modProj').click(function(){
		$('#selectDeptsList').val('general');
		$('.deptBtn[content="02_config"]').click();
	});

	// bouton recacluer la progression de tout les shots d'un coup
	$('#recalcProgress').click(function(){
		var ajaxReq = 'action=recalcAllShotsProgress&projID='+project_ID;
		AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, true);
	});

	// bouton AJOUT DE SÉQUENCE
	$('#btn_addSeq').click(function() {
		if (addSeqWIP != true) {
			var modele = $('#addSeq_modele').html();
			$('.seqTable td').addClass('ui-state-disabled');
			$('#liste').prepend(modele);
			$('#addSeq_start').datepicker({dateFormat:'dd/mm/yy', firstDay:1, changeMonth:true, changeYear:true});
			$('#addSeq_end').datepicker({dateFormat:'dd/mm/yy', firstDay:1, changeMonth:true, changeYear:true});
			$('#addSeq_leads').multiselect({height: '340px', selectedList: 4, noneSelectedText: 'none', selectedText: '# leads', checkAllText: ' ', uncheckAllText: ' '});
			$('#addSeq_title').focus();
			addSeqWIP = true ;
		}
	});

	// bouton VALIDATION ADD SÉQUENCE
	$('#liste').on('click', '#addSeq_valid', function() {
		var title = $('#addSeq_title').val();
		var nbShots = $('#addSeq_nbShots').val();
		var debutPick = $('#addSeq_start').datepicker('getDate');
		var debut = $.datepicker.formatDate('yy-mm-dd 00:00:00', debutPick);
		var finPick = $('#addSeq_end').datepicker('getDate');
		var fin = $.datepicker.formatDate('yy-mm-dd 00:00:00', finPick);
		var leadsArrElems = $('#addSeq_leads').multiselect("getChecked");
		var leads = '';
		$.each(leadsArrElems, function(i,lead) {
			leads += $(lead).val()+',';
		});
		leads = leads.replace(/,$/,'');
		var ajaxReq = 'action=addSeq&projID='+project_ID+'&seqInfos={"title":"'+title+'","date":"'+debut+'","deadline":"'+fin+'","lead":"'+leads+'","nbShots":"'+nbShots+'"}';
		AjaxJson(ajaxReq, 'admin/admin_sequences_actions', retourAjaxStructure, true);
	});

	// bouton ANNULATION ADD SÉQUENCE
	$('#liste').on('click', '#addSeq_annul', function() {
		$('.seqTable td').removeClass('ui-state-disabled');
		$(this).parents('#newSeq').remove();
		addSeqWIP = false;
	});



	//////////////////////////////////////////////////////// BOUTONS SÉQUENCES ////////////////////////////////////////////////////////////////////////////////


	// bouton ADD SHOT to SEQ
	$('.seq_addShot').click(function(){
		var $dialog = $('#addShot_dialog').clone(true);
		var seqID	= $(this).parents('li').attr('idSeq');
		var labelSeq= $(this).parents('li').attr('labelSeq');
		var shotTB	= $(this).parents('li').find('.sousTable');
		// init de la fenêtre d'ajout de shot
		$dialog.dialog({
			autoOpen: true, height: 500, width: 800, modal: true,
			show: "fade", hide: "fade",
			title: 'Ajouter des plans à la séquence '+labelSeq,
			open: function() {
				var lbl = shotTB.find('tr').length;
				initAddshot($(this), seqID, labelSeq, lbl);
			},
			buttons: {'Valider'  : function() {
							var valModProj = JSON.encode(getAddShotValues($(this)));
							var ajaxReq = 'action=addShot&IDproj='+project_ID+'&idSeq='+seqID+'&labelSeq='+labelSeq+'&values='+valModProj;
							console.log(ajaxReq);
							AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, 'reloadSeq_'+seqID);
							$(this).dialog('close');
					  },
					  'Annuler'  : function() { $(this).dialog('close'); $dialog.remove(); }
			},
			close: function() { $dialog.dialog('destroy'); $dialog.remove(); }
		});
		$(this).removeClass('ui-state-focus');
	});

	// bouton HIDE SÉQUENCE
	$('.seq_hide').click(function(){
		if (addSeqWIP != false || modSeqWIP != false || modPlanWIP != false) return;
		var labelSeq = $(this).parents('li').find('.seqTitle').html();
		var idSeq	 = $(this).parents('li').attr('idSeq');
		var ajaxReq  = 'action=modSeq&seqID='+idSeq;
		if ($(this).hasClass('ui-state-disabled')) {
			if (confirm('Montrer la séquence '+labelSeq+' ?')) {
				ajaxReq += '&seqInfos={"hide":"0"}';
				AjaxJson(ajaxReq, 'admin/admin_sequences_actions', retourAjaxStructure, false);
				$(this).removeClass('ui-state-disabled').removeClass('ui-state-focus');
			}
		}
		else {
			if (confirm('Cacher la séquence '+labelSeq+' ?')) {
				ajaxReq += '&seqInfos={"hide":"1"}';
				AjaxJson(ajaxReq, 'admin/admin_sequences_actions', retourAjaxStructure, false);
				$(this).addClass('ui-state-disabled doigt').removeClass('ui-state-focus');
			}
		}
	});

	// bouton LOCK SÉQUENCE
	$('.seq_lock').click(function(){
		if (addSeqWIP != false || modSeqWIP != false || modPlanWIP != false) return;
		var labelSeq = $(this).parents('li').find('.seqTitle').html();
		var idSeq	 = $(this).parents('li').attr('idSeq');
		var ajaxReq  = 'action=modSeq&seqID='+idSeq;
		if ($(this).find('span').hasClass('ui-icon-locked')) {
			if (confirm('Débloquer l\'accès à la séquence '+labelSeq+' ?')) {
				ajaxReq += '&seqInfos={"lock":"0"}';
				AjaxJson(ajaxReq, 'admin/admin_sequences_actions', retourAjaxStructure, false);
				$(this).removeClass('ui-state-focus ui-state-disabled').find('span').removeClass('ui-icon-locked').addClass('ui-icon-unlocked');
			}
		}
		else {
			if (confirm('Bloquer l\'accès à la séquence '+labelSeq+' ?')) {
				ajaxReq += '&seqInfos={"lock":"1"}';
				AjaxJson(ajaxReq, 'admin/admin_sequences_actions', retourAjaxStructure, false);
				$(this).removeClass('ui-state-focus').addClass('ui-state-disabled').find('span').removeClass('ui-icon-unlocked').addClass('ui-icon-locked');
			}
		};
	});

	// bouton ARCHIVE SÉQUENCE
	$('.seq_del').click(function(){
		if (addSeqWIP != false || modSeqWIP != false || modPlanWIP != false) return;
		var labelSeq = $(this).parents('li').find('.seqTitle').html();
		var idSeq	 = $(this).parents('li').attr('idSeq');
		if (confirm('Archiver la séquence '+labelSeq+' ?')) {
			var ajaxReq = 'action=archiveSeq&idSeq='+idSeq;
			AjaxJson(ajaxReq, 'admin/admin_sequences_actions', retourAjaxStructure, true);
		}
	});
	// bouton RESTAURE SÉQUENCE
	$('.seq_restore').click(function(){
		if (addSeqWIP != false || modSeqWIP != false || modPlanWIP != false) return;
		var labelSeq = $(this).parents('li').find('.seqTitle').html();
		var idSeq	 = $(this).parents('li').attr('idSeq');
		if (confirm('Restaurer la séquence '+labelSeq+' ?')) {
			var ajaxReq = 'action=restoreSeq&idSeq='+idSeq;
			AjaxJson(ajaxReq, 'admin/admin_sequences_actions', retourAjaxStructure, true);
		}
	});

	// bouton MODIF SÉQUENCE
	$('.seq_mod').click(function(){
		if (addSeqWIP != false || modSeqWIP != false || modPlanWIP != false) return;
		$('.seqTable td').addClass('ui-state-disabled');
		var seqDom = $(this).parents('tr');
		var idSeq = seqDom.parents('li').addClass('ui-state-active').attr('idSeq');
		var fieldTitle = seqDom.children('.seqTitle'); var oldTitle  = fieldTitle.html();
		var fieldDescr = $('#descr_'+idSeq);		   var oldDescr  = fieldDescr.html();
		if (oldDescr == '') oldDescr = 'description';
		else oldDescr = oldDescr.replace(/<br>/gm,"\r\n");
		var fieldStart = seqDom.find('.seqStart');	   var oldStart  = fieldStart.html();
		var fieldEnd   = seqDom.find('.seqEnd');	   var oldEnd    = fieldEnd.html();
		var fieldLead  = seqDom.find('.seqLead');	   var oldLeads  = fieldLead.html();
		var optionsLeads = ''; var selected = '';
		$.each(autocompleteLeads, function(i,lead){
			if (oldLeads.indexOf(lead) != -1) selected = 'selected';
			else selected = '';
			optionsLeads += '<option class="mini" value="'+lead+'" '+selected+'>'+lead+'</option>';
		});
		seqDom.children('.seqTable td').removeClass('ui-state-disabled').unbind('click').css('cursor','default');

		fieldTitle.html('<input type="text" class="noBorder ui-corner-all pad3 fondPage modSeq" value="'+oldTitle+'" id="title" />');
		fieldDescr.html('<textarea class="noBorder ui-corner-all pad3 fondPage modSeq" cols="17" rows="5" id="description">'+oldDescr+'</textarea>').addClass('unhideable').show();
		$('.seqDescr').addClass('unshowable');
		if (oldDescr == 'description') fieldDescr.children('textarea').focus(function(){$(this).val('')});
		fieldStart.html('<input type="text" class="noBorder ui-corner-all pad3 fondPage modSeq" style="width: 70px;" value="'+oldStart+'" id="date" />');
		fieldEnd.html('<input type="text" class="noBorder ui-corner-all pad3 fondPage modSeq" style="width: 70px;" value="'+oldEnd+'" id="deadline" />');
		fieldLead.html('<select class="mini noPad modSeq" title="Leads" id="lead" multiple></select>');
		fieldLead.find('#lead').append(optionsLeads);
		fieldLead.find('#lead').multiselect({height: '340px', selectedList: 4, noneSelectedText: 'Aucun', selectedText: '# leads', checkAllText: ' ', uncheckAllText: ' '});
		seqDom.find('.actionBtns').html(
			'<button class="bouton marge10r ui-state-highlight" title="sauvegarder" id="modSeq_valid"><span class="ui-icon ui-icon-check"></span></button>'+
			'<button class="bouton marge10r ui-state-error" title="annuler" id="modSeq_annul"><span class="ui-icon ui-icon-cancel"></span></button>'
		);
		$('#date').datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true});
		$('#deadline').datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true});
		$('.bouton').button();
		$('#title').focus();
		modSeqWIP = idSeq;
	});

	// bouton VALIDATION MOD SÉQUENCE
	$('#liste').on('click', '#modSeq_valid', function() {
		var ajaxReq = 'action=modSeq&seqID='+modSeqWIP+'&seqInfos={';
		$('.modSeq').each(function(){
			var row = $(this).attr('id');
			var val = $(this).val();
			if (row == 'date' || row == 'deadline') {
				var datePick = $(this).datepicker('getDate');
				val = $.datepicker.formatDate('yy-mm-dd 00:00:00', datePick);
			}
			if (row == 'description') { val = val.replace(/(\r\n|\n|\r)/gm,"<br>"); }
			ajaxReq += '"'+row+'":"'+val+'",';
		});
		ajaxReq = ajaxReq.substr(0, ajaxReq.length-1);
		ajaxReq += '}';
		AjaxJson(ajaxReq, 'admin/admin_sequences_actions', retourAjaxStructure, true);
	});

	// bouton ANNULATION MOD SÉQUENCE
	$('#liste').on('click', '#modSeq_annul', function() {
		modSeqWIP = false;
		loadDept('00_structure', {projectID: project_ID});
	});



	//////////////////////////////////////////////////////// BOUTONS PLANS //////////////////////////////////////////////////////////////////////////////////


	// bouton HIDE PLAN
	$('#liste').on('click','.plan_hide',function(){
		if (addSeqWIP != false || modSeqWIP != false || modPlanWIP != false) return;
		var titleShot = $(this).parents('tr').find('.shotTitle').html();
		var idShot = $(this).parents('tr').attr('idShot');
		var ajaxReq  = 'action=modShot&IDshot='+idShot;
		if ($(this).hasClass('ui-state-disabled')) {
			if (confirm('Montrer le plan '+titleShot+' ?')) {
				ajaxReq += '&shotInfos='+encodeURIComponent('{"hide":"0"}');
				AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, false);
				$(this).removeClass('ui-state-disabled').removeClass('ui-state-focus');
			}
		}
		else {
			if (confirm('Cacher le plan '+titleShot+' ?')) {
				ajaxReq += '&shotInfos='+encodeURIComponent('{"hide":"1"}');
				AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, false);
				$(this).addClass('ui-state-disabled doigt').removeClass('ui-state-focus');
			}
		}
	});

	// bouton LOCK PLAN
	$('#liste').on('click','.plan_lock',function(){
		if (addSeqWIP != false || modSeqWIP != false || modPlanWIP != false) return;
		var titleShot = $(this).parents('tr').find('.shotTitle').html();
		var idShot = $(this).parents('tr').attr('idShot');
		var ajaxReq = 'action=modShot&IDshot='+idShot;
		if ($(this).find('span').hasClass('ui-icon-locked')) {
			if (confirm('Débloquer l\'accès au plan '+titleShot+' ?')) {
				ajaxReq += '&shotInfos='+encodeURIComponent('{"lock":"0"}');
				AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, false);
				$(this).removeClass('ui-state-focus ui-state-disabled').find('span').removeClass('ui-icon-locked').addClass('ui-icon-unlocked');
			}
		}
		else {
			if (confirm('Bloquer l\'accès au plan '+titleShot+' ?')) {
				ajaxReq += '&shotInfos='+encodeURIComponent('{"lock":"1"}');
				AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, false);
				$(this).removeClass('ui-state-focus').addClass('ui-state-disabled').find('span').removeClass('ui-icon-unlocked').addClass('ui-icon-locked');
			}
		};
	});

	// bouton ARCHIVE PLAN
	$('#liste').on('click','.plan_archive',function(){
		if (addSeqWIP != false || modSeqWIP != false || modPlanWIP != false) return;
		var titleShot = $(this).parents('tr').find('.shotTitle').html();
		var idShot = $(this).parents('tr').attr('idShot');
		if (confirm('Archiver le plan '+titleShot+' ?')) {
			var ajaxReq = 'action=archiveShot&IDshot='+idShot;
			AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, true);
		}
	});
	// bouton RESTAURE PLAN
	$('#liste').on('click','.plan_restore',function(){
		if (addSeqWIP != false || modSeqWIP != false || modPlanWIP != false) return;
		var titleShot = $(this).parents('tr').find('.shotTitle').html();
		var idShot = $(this).parents('tr').attr('idShot');
		if (confirm('Restaurer le plan '+titleShot+' ?')) {
			var ajaxReq = 'action=restoreShot&IDshot='+idShot;
			AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, true);
		}
	});


	// bouton MODIF PLAN
	$('#liste').on('click','.plan_mod',function(){
		if (addSeqWIP != false || modSeqWIP != false || modPlanWIP != false) return;
		$('.seqTable td').addClass('ui-state-disabled');
		var planDom    = $(this).parent('td').parent('tr');
		var idPlan     = planDom.attr('idShot');
		var fieldTitle = planDom.children('.shotTitle');var oldTitle  = fieldTitle.html();
		var fieldStart = planDom.find('.shotStart');var oldStart  = fieldStart.html();
		var fieldEnd   = planDom.find('.shotEnd');var oldEnd	   = fieldEnd.html();
		var fieldArtists = planDom.find('.shotArtists');var oldArtists= fieldArtists.html();
		var optionsTeam = ''; var selected = '';
		$.each(autocompleteArtists, function(i,artist){
			if (oldArtists.indexOf(artist.pseudo) != -1) selected = 'selected';
			else selected = '';
			optionsTeam += '<option class="mini" value="'+artist.id+'" '+selected+'>'+artist.pseudo+'</option>';
		});
		planDom.parents('td').removeClass('ui-state-disabled');
		planDom.children('td').removeClass('ui-state-disabled');

		fieldTitle.html	('<input type="text" class="noBorder ui-corner-all pad3 fondPage modShotDetail" value="'+oldTitle+'" id="title" />').removeClass('openShot');
		fieldStart.html	('<input type="text" class="noBorder ui-corner-all pad3 fondPage modShotDetail" style="width: 70px;" value="'+oldStart+'" id="date" />');
		fieldEnd.html	('<input type="text" class="noBorder ui-corner-all pad3 fondPage modShotDetail" style="width: 70px;" value="'+oldEnd+'" id="deadline" />');
		fieldArtists.html('<select class="mini noPad modShotDetail" title="Artists Team" id="equipe" multiple></select>');
		fieldArtists.find('#equipe').append(optionsTeam);
		fieldArtists.find('#equipe').multiselect({height: '340px', selectedList: 4, noneSelectedText: 'Aucun', selectedText: '# artists', checkAllText: ' ', uncheckAllText: ' '});
		planDom.find('.actionBtns').html(
			'<button class="bouton marge10r ui-state-highlight" title="sauvegarder" idShot="'+idPlan+'" id="modPlan_valid"><span class="ui-icon ui-icon-check"></span></button>'+
			'<button class="bouton marge10r ui-state-error" title="annuler" id="modPlan_annul"><span class="ui-icon ui-icon-cancel"></span></button>'
		);
		$('.modShotDetail#date').datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true});
		$('.modShotDetail#deadline').datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true});
		$('.bouton').button();
		$('.modShotDetail#title').focus();
		modPlanWIP = idPlan;
	});

	// bouton VALIDATION MOD PLAN
	$('#liste').on('click', '#modPlan_valid', function() {
		var idSeq  = $(this).parents('li').attr('idSeq');
		var idPlan = $(this).attr('idShot');
		var valModShot = JSON.encode(getModShotValues());
		var ajaxReq = 'action=modShot&IDshot='+idPlan+'&shotInfos='+valModShot;
		console.log(ajaxReq);
		modPlanWIP = false;
		AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, false);
		loadDept('00_structure', {projectID: project_ID, seqID: idSeq});
	});

	// bouton ANNULATION MOD PLAN
	$('#liste').on('click', '#modPlan_annul', function() {
		var idSeq  = $(this).parents('li').attr('idSeq');
		modPlanWIP = false;
		loadDept('00_structure', {projectID: project_ID, seqID: idSeq});
	});


});
// FIN DU DOCUMENT READY



//'{"title":"'+title+'","date":"'+start+'","deadline":"'+end+'","equipe":"'+team+'"}'
// Construit l'array des valeurs de modif shot
function getModShotValues() {
	var arrModShotVals = {};
	$('.modShotDetail').each(function(){
		var valName = $(this).attr('id');
		if (valName == 'date' || valName == 'deadline' || valName == 'equipe') return true;	// skip les dates et la team pour traitement à part
		var newValue = $(this).val();
		if (valName != '' && newValue != '' && newValue != null)
			arrModShotVals[valName] = encodeURIComponent(newValue);
	});
	// dates & team
	arrModShotVals['date']		= $('.modShotDetail#date').datepicker("getDate");
	arrModShotVals['deadline']	= $('.modShotDetail#deadline').datepicker("getDate");
	arrModShotVals['equipe']	= $('.modShotDetail#equipe').val();

	return arrModShotVals;
}


// Init de la modal d'ajout de plan
function initAddshot($dialog, seqID, labelSeq, lastLabel) {
	$dialog.off('keyup', '#nbShotAdd');
	$dialog.on('keyup', '#nbShotAdd', function(){
		var nbAddShot = $(this).val(); var sl=0;
		if (nbAddShot > 1) $dialog.find('#addShotPluriel').html('s');
		else $dialog.find('#addShotPluriel').html('');
		var pasvide = $dialog.find('#aStitle').children('#title').val();
		if (pasvide && pasvide != '') {
			if (!confirm('Attention : changer le nombre de plan va vider les champs déjà remplis. Continuer ?')) {
				return;
			}
		}
		$dialog.find('#newShotsList').html('');
		for (sl=0; sl<nbAddShot; sl++) {
			addLineAddShot ($dialog, seqID, labelSeq, lastLabel+sl);
		}
		$dialog.find('.addShotDetail').each(function(){
//			$(this).find(".selectEquipe").multiselect({height: '340px', selectedList: 3, noneSelectedText: 'Aucun', selectedText: '# artists', checkAllText: ' ', uncheckAllText: ' '});
			$(this).find(".inputDate").datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true});		// Calendrier sur focus d'input
			$(this).find(".inputDeadline").datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true});		// Calendrier sur focus d'input
		});
	});
}

// Ajoute une ligne de shot dans la modal de add shot
function addLineAddShot ($dia, seqID, labelSeq, lstLbl) {
	var lbl = "000" + (lstLbl+1);
	var nextLabel = 'SHOT'+lbl.substr(lbl.length-3);
	var $modeleAddShot  = $('#addShotModele').clone(true);
	var $destAppend		= $dia.find('#newShotsList');

	$modeleAddShot.find('.shotLine').attr('labelShot', nextLabel).attr('idSeq', seqID).attr('labelSeq', labelSeq);
	$modeleAddShot.find('#label').html(nextLabel);
//	$modeleAddShot.find('.selectEquipe').multiselect({height: '340px', selectedList: 3, noneSelectedText: 'Aucun', selectedText: '# artists', checkAllText: ' ', uncheckAllText: ' '});
	$modeleAddShot.appendTo($destAppend);
}

// Construit l'array des valeurs d'ajout de shot
function getAddShotValues ($dialog) {
//	var arrAddShotVals = {"title":"yo","date":"2012-08-02 00:00:00","deadline":"2012-08-29 00:00:00","equipe":"Karlova,Polo"};
	var arrAddShotVals = [];
	$dialog.find('.shotLine').each(function(){
		var newShotVals = {};
//		newShotVals['idSeq'] = $(this).attr('idSeq');
//		newShotVals['labelSeq'] = $(this).attr('labelSeq');
		newShotVals['label'] = $(this).attr('labelShot');
		newShotVals['title'] = $(this).find('#title').val();
		newShotVals['date']  = $(this).find('.inputDate').datepicker('getDate');
		newShotVals['deadline']  = $(this).find('.inputDeadline').datepicker('getDate');
//		newShotVals['equipe']= $(this).find('#equipe').val();
		arrAddShotVals.push(newShotVals);
	});

	return arrAddShotVals;
}

// réorganise l'ordre des séquences
function ajaxUpdateSeqPos (newPosArr) {
	var newPosJson = encodeURIComponent(JSON.encode(newPosArr));
	var activeGroupDepts = $('#selectDeptsList').val();
	var strAjax = 'action=modPos&newPos='+newPosJson;
	AjaxJson(strAjax, 'admin/admin_sequences_actions', retourAjaxStructure, true);
	$('#arboMenu').load('modals/menuArbo.php', {projectID: project_ID, dept: departement, deptID: '0', template: 'structure', typeArbo: activeGroupDepts}).show();
}



