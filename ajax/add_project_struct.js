
$(function() {
	// Si les infos sont déjà définies
	if (localStorage.getItem('addProj_BaseVals') && localStorage.getItem('addProj_TeamList')) {
		var baseInfos = JSON.decode(localStorage.getItem('addProj_BaseVals'));
		$('#struct_projTitle').html(baseInfos['title']);
		var t = new Date(); var tf = t.getTime();
		$('#microVignetteHead').attr("src", localStorage.getItem('addProj_VignetteTempUrl')+'?'+tf);
		var nomenc = baseInfos['nomenclature'].split(nomencSepa);
		nomencSeq = nomenc[0];
	}
	else if (localStorage.getItem('addProj_BaseVals'))
		$('.deptBtn[content="proj_add_team"]').click();
	// Sinon retour aux infos de base
	else $('.deptBtn[content="proj_add_base"]').click();


	// gestion nombre de séquences
	$('#proj_nbSeq').focus(function() {
		if ($(this).val() == 0)
			$(this).val('');
	});
	$('#proj_nbSeq').keyup(function(){
		var nbSeq = $(this).val();
		$('#seqList').html('');
		if (nbSeq > 1) $('#seqPluriel').html('s');
		else $('#seqPluriel').html('');
		for (it = 1; it <= nbSeq; it++) {
			var numSeq = it.toString();
			if (numSeq.length < 3) numSeq = ('000' + numSeq).slice(-3);
			var labelSeq = nomencSeq.replace('###', numSeq);
			var lineSeq = '<div class="ui-state-default ui-corner-all pad5 sequenceLine" labelSeq="'+labelSeq+'">'
						+	'<b>'+labelSeq+'</b> '
						+   '<span class="marge30l">Title : </span><input type="text" class="noBorder ui-corner-all pad3 w300 fondSect3 inputSeqTitle" value="'+labelSeq+'" />'
						+   '<span class="marge30l">Number of shots : </span><input type="text" class="numericInput noBorder ui-corner-all pad3 fondSect3 inputSeqNbShots" size="3" value="1" />'
						+	'<div class="inline mid ui-state-error noBG noBorder"><span class="ui-icon ui-icon-notice"></span></div>'
						+ '</div>';
			if (isDemoMode && it > maxDemoSeq) return false;
			$('#seqList').append(lineSeq);
		}
		if (checkAllFilled())
			$('.submitBtns').removeClass('ui-state-disabled');
		else $('.submitBtns').addClass('ui-state-disabled');
	});
	$('#proj_nbSeq').blur(function(){
		if ($(this).val() == '') {$(this).val('0');$('#seqPluriel').html('');return;}
		nbSeq = $(this).val();
		localStorage.setItem('addProj_NbSeq', nbSeq);
	});


	if (localStorage.getItem('addProj_NbSeq') > 0) {
		$('#proj_nbSeq').val(localStorage.getItem('addProj_NbSeq')).focus().keyup();
	}


	// gestion nombre de plans pour chaque séquence
	$('#seqList').on('keyup', '.inputSeqNbShots', function() {
		var thisSeq = $(this).parent().attr('refSeq');
		var nbShot = $(this).val();
		if (!isNaN(nbShot) && nbShot > 0)
			$(this).next().removeClass('ui-state-error').children('span').addClass('ui-icon-check');
		else $(this).next().addClass('ui-state-error').children('span').removeClass('ui-icon-check');
		if (checkAllFilled())
			$('.submitBtns').removeClass('ui-state-disabled');
		else $('.submitBtns').addClass('ui-state-disabled');
	});

	// évite la propagation du tab sur le reste de l'interface
	$('#seqList').on('keypress', '.inputSeqNbShots:last',function(e) {
		if (e.keyCode == 9)
			e.preventDefault();
	});

	// Bouton terminé
	$('#proj_DONE').click(function(){
		var sequencesList = {}; var i = 0;
		$('.sequenceLine').each(function(){
			var label = $(this).attr('labelSeq');
			var title = $(this).find('.inputSeqTitle').val();
			var nbShots = $(this).find('.inputSeqNbShots').val();
			sequencesList[i] = [label, title, nbShots];
			i++;
		});
		var valNewProj = localStorage.getItem('addProj_BaseVals');
		var teamList   = localStorage.getItem('addProj_TeamList');
		var deptsVals  = encodeURIComponent(localStorage.getItem('addProj_DeptsList'));
		var structVals = encodeURIComponent(JSON.encode(sequencesList));
		var ajaxStr = 'action=addProject&values='+valNewProj+'&team='+teamList+'&depts='+deptsVals+'&struct='+structVals;
		AjaxJson(ajaxStr, 'admin/admin_projects_actions', retourAjaxAddProj);
	});
});


// vérifie que le nombre de plans pour chaque séquence est renseigné
function checkAllFilled() {
	var r = true;
	$('.inputSeqNbShots').each(function() {
		var nbShot = $(this).val();
		if(isNaN(nbShot) || nbShot < 1) r = false;
	});
	return r;
}
// FIN DU DOCUMENT READY



