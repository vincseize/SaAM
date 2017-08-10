
$(function () {

	// Pour griser les boutons de départements si pas d'infos pour ce shot
	if (window['shot_ID'] != undefined && window['shotAllDepts'] != undefined) {
		$('.deptBtn[content="template_1"]').addClass('ui-state-disabled noBG noBorder');
		$.each(shotAllDepts, function(i, dept) {
			$('#shots_depts').find('.deptBtn[label="'+dept+'"]').removeClass('ui-state-disabled noBG noBorder');
		});
		$('#shots_depts').find('.deptBtn[label="'+departement+'"]').removeClass('ui-state-disabled noBorder');
	}

	// toggle du topInfos
	$(document).off('click', '.projTitle');
	$(document).on('click', '.projTitle', function(){
		if (topInfosHidden == false) hideTopInfosStage(transition);
		else showTopInfosStage(transition);
	});

	// Init de l'animation des lignes de la liste des users
	$('#liste').on('mouseenter', 'li.ui-state-default', function(){$(this).addClass('ui-state-hover');});
	$('#liste').on('mouseleave', 'li.ui-state-default', function(){$(this).removeClass('ui-state-hover');});

	// init des progressBars
	$('.progBar').each(function() {
		var percent = parseInt($(this).attr('percent'));
		$(this).progressbar("destroy");
		$(this).progressbar({value: percent});
	});

	// check remplissage de champ de modif à la volée
	$(document).on('keyup', '.requiredField', function() {
		var inputTxt = $(this).val();
		var inputId = $(this).attr('id');
		if (inputTxt.length >= 4) {
			$('.requiredField#'+inputId).next().removeClass('ui-state-error').find('span').addClass('ui-icon-check');
		}
		else $('.requiredField#'+inputId).next().addClass('ui-state-error').find('span').removeClass('ui-icon-check').addClass('ui-icon-notice');
		checkAllFilled();
	});

	// Affichage de la description de la séquence on hover
	$('.seqTitle').hover(
		function() {
			var idSeq = $(this).parents('li').attr('idSeq');
			if ($('#descr_'+idSeq).hasClass('unshowable')) return;
			$('#descr_'+idSeq).show();
		},
		function() { if (! $('.seqDescr').hasClass('unhideable')) $('.seqDescr').hide(); }
	);

	// retour à la vue globale du projet
	$('#backToProj').click(function(){
		delete window.shot_ID;
		delete window.seq_ID;
		localStorage.removeItem('openShot_'+project_ID);
		$('#shots_depts').find('.deptBtn[label="'+departement+'"]').click();
	});


	// bouton refresh du plan
	$('#refreshShot').click(function(){
		if (departement == 'structure')
			loadPageContentModal('depts_shot_specific/dept_structure_shots', {projectID: project_ID, sequenceID: seq_ID, shotID: shot_ID});
		else if (departement == 'dectech')
			loadPageContentModal('depts_shot_specific/dept_dectech_shots', {projectID: project_ID, sequenceID: seq_ID, shotID: shot_ID});
		else if (departement == 'storyboard' || departement == 'final')
			loadPageContentModal('structure/structure_shots', {dept: departement, template: 'dept_'+departement, projectID: project_ID, sequenceID: seq_ID, shotID: shot_ID});
		else
			loadPageContentModal('structure/structure_shots', {dept: departement, deptID: deptID, template: deptFile, projectID: project_ID, sequenceID: seq_ID, shotID: shot_ID});
	});

	$('.seq_SB_contactSheet').click(function(){
		var idSeq = $(this).parents('li').attr('idSeq');
		var shotExcludeList = [];
		$('.shot_contactSheetActive').each(function(i,elem){
			if (!$(this).hasClass('ui-state-activeFake'))
				shotExcludeList.push($(this).parents('tr').attr('idShot'));
		});
		var shotExcludeListJ = encodeURIComponent(JSON.encode(shotExcludeList));
		var params = 'proj_ID='+project_ID+'&seq_ID='+idSeq+'&exclShot='+shotExcludeListJ+'&dept=storyboard';
		window.open('modals/sheets/storyboard_sheet.php?'+params, "ContactSheet", "menubar=yes, scrollbars=yes, width=1024");
	});

});
// FIN DU DOCUMENT READY



// retour ajax de l'ajout / modification de séquence / shot / retake
function retourAjaxStructure (datas, reload) {
	if (datas.error == 'OK') {
		$('#retourAjax').html(datas.message).addClass('ui-state-highlight').show(transition);
		addSeqWIP = false;
		modSeqWIP = false;
		setTimeout(function(){$('#retourAjax').fadeOut(transition, function(){$('#retourAjax').html('');});}, 1000);
		if (reload) {
			var activeGroupDepts = $('#selectDeptsList').val();
			if (typeof reload == 'string') {
				if (reload == 'totalReload') window.location = 'index.php';
				else if (reload.indexOf('reloadSeq') != -1){
					var rld = reload.split('_');
					var idSeq = rld[1];
					localStorage['openSeq_'+project_ID] = idSeq;
					window.location = 'index.php';
				}
				else if (reload == 'reloadShot') {
					$('#arboMenu').load('modals/menuArbo.php', {projectID: project_ID, dept: departement, deptID: deptID, template: deptFile, typeArbo: activeGroupDepts, sequenceID: seq_ID, shotID: shot_ID}).show();
					if (departement == 'structure')
						loadPageContentModal('depts_shot_specific/dept_structure_shots', {projectID: project_ID, sequenceID: seq_ID, shotID: shot_ID});
					else if (departement == 'dectech')
						loadPageContentModal('depts_shot_specific/dept_dectech_shots', {projectID: project_ID, sequenceID: seq_ID, shotID: shot_ID});
					else if (departement == 'storyboard' || departement == 'final')
						loadPageContentModal('structure/structure_shots', {dept: departement, template: 'dept_'+departement, projectID: project_ID, sequenceID: seq_ID, shotID: shot_ID});
					else
						loadPageContentModal('structure/structure_shots', {dept: departement, deptID: deptID, template: deptFile, projectID: project_ID, sequenceID: seq_ID, shotID: shot_ID});
				}
				else if (reload == 'reloadFolderShot') {
					$('#listFolderContent').load('actions/display_folder_shot.php', {path: datas.rep}, function(){
						$('.fancybox-bankShot').fancybox();
					});
				}
			}
			else {
				$('#arboMenu').load('modals/menuArbo.php', {projectID: project_ID, dept: '00_structure', deptID: deptID, template: 'structure', typeArbo: activeGroupDepts}).show();
				loadDept('00_structure', {projectID: project_ID});
			}
		}
	}
	else {
		$('#retourAjax').html('<b>'+datas.message+'</b>').addClass('ui-state-error').show(transition);
		setTimeout(function(){$('#retourAjax').fadeOut(transition*10, function(){$('#retourAjax').html('').removeClass('ui-state-error');});}, 2000);
	}
}


// vérifie que les champs "requiredFields" sont bien remplis
function checkAllFilled() {
	var r = true;
	$('.requiredField').each(function() {if ($(this).val().length < 4) r = false;});
	var dateStart = $('#date').datepicker("getDate");
	var dateEnd   = $('#deadline').datepicker("getDate");
	if (dateStart == null || dateEnd == null) r = false;
	else {
		if (dateStart > dateEnd) {
			$('.noticeDates').html('<span class="inline top ui-icon ui-icon-notice"></span>Inversion début/fin !!</span>').addClass('ui-state-error').removeClass('noBG');
			r = false;
		}
		else $('.noticeDates').html('<span class="ui-icon ui-icon-check"></span></span>').removeClass('ui-state-error');
	}
	return r;
}
