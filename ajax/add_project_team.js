
$(function() {
	// Si les infos sont déjà définies
	if (localStorage.getItem('addProj_BaseVals')) {
		var baseInfos = JSON.decode(localStorage.getItem('addProj_BaseVals'));
		$('#team_projTitle').html(baseInfos['title']);
		var t = new Date(); var tf = t.getTime();
		$('#microVignetteHead').attr("src", localStorage.getItem('addProj_VignetteTempUrl')+'?'+tf);
	} // Sinon retour aux infos de base
	else $('.deptBtn[content="proj_add_base"]').click();

	// Si la liste est déjà définie
	if (localStorage.getItem('addProj_TeamList')) {
		var itemsToDrop = JSON.decode(localStorage.getItem('addProj_TeamList'));
		$.each(itemsToDrop, function(o, item){
			var dropItem = $('div[idartist="'+item+'"]');
			addArtist(dropItem);
		});
	}
	else addArtist($('div[idartist="'+userID+'"]'));


	// init des divs bougeables
	$('.proj_user').draggable({
		cancel: "button",
		revert: "invalid",
		containment: ".pageContent",
		helper: "clone"
	});

	// init des divs où on peut poser des gens
	$('#proj_Team').droppable({
		accept: ".proj_user",
		activeClass: "ui-state-hover",
		hoverClass: "noBG",
		drop: function( e, ui ) {
			addArtist(ui.draggable);
		}
	});
	$('#artistsList').droppable({
		accept: ".proj_user",
		hoverClass: "fondSect3",
		drop: function( e, ui ) {
			removeArtist(ui.draggable);
		}
	});

	// toggle de l'affichage des filtres
	$('#filtreToggle').toggle(
		function() {
			$(this).children('button').addClass('ui-state-error');
			$('#headerList_title').hide();
			$('#headerList_filtres').show();
		},
		function() {
			$(this).children('button').removeClass('ui-state-error');
			$('.filtreComp').removeClass('ui-state-error');
			$('.proj_user').show();
			$('#headerList_filtres').hide();
			$('#headerList_title').show();
		}
	);

	// init des filtres par compétences
	$('.filtreComp').click(function() {
		if (! $(this).hasClass('ui-state-error')) {
			$('.filtreComp').removeClass('ui-state-error');
			$(this).addClass('ui-state-error');
			var toFilter = $(this).attr('comp');
			var divsOK = $('.proj_user[comp~="'+toFilter+'"]');
			$('.proj_user').hide();
			divsOK.show();
		}
		else {
			$('.filtreComp').removeClass('ui-state-error');
			$('.proj_user').show();
		}
	});

	// boutons suivant et terminé
	$('#proj_NEXT_struct').click(function() {
		localStorage.setItem('addProj_EtapeWIP', 'struct');
		localStorage.setItem('addProj_TeamList', JSON.encode(artistsToAdd));
		$('.deptBtn[content="proj_add_struct"]').click();
	});
	$('#proj_DONE').click(function(){
		var valNewProj	= localStorage.getItem('addProj_BaseVals');
		var listDepts	= encodeURIComponent(localStorage.getItem('addProj_DeptsList'));
		var team		= encodeURIComponent(JSON.encode(artistsToAdd));
		var ajaxStr = 'action=addProject&values='+valNewProj+'&depts='+listDepts+'&team='+team;
		AjaxJson(ajaxStr, 'admin/admin_projects_actions', retourAjaxAddProj);
	});

});

var artistsToAdd = [];

// Ajout d'un artiste à l'équipe du projet
function addArtist($item) {
	$item.fadeOut(50, function() {
		var theItem = $item.addClass('inline top w150 center ui-state-active').css('display', 'inline-block');
		theItem.find('.floatR').hide();
		theItem.find('.colorSoft').hide();
		theItem.find('img').show();
		theItem.children('div').removeClass('inline top w150');
		theItem.appendTo('#proj_Team').fadeIn();
		artistsToAdd.push($item.attr('idArtist')) ;
		localStorage.setItem('addProj_TeamList', JSON.encode(artistsToAdd));
	});
	localStorage.setItem('addProj_EtapeWIP', 'struct');
	$('.submitBtns').removeClass("ui-state-disabled");
	$('.deptBtn[content="proj_add_struct"]').removeClass('ui-state-disabled inactiveBtn');
}

// Suppression d'un artiste de l'équipe du projet
function removeArtist($item) {
	$item.fadeOut(50, function() {
		var theItem = $item.removeClass('inline top w150 center ui-state-active');
		theItem.find('.floatR').show();
		theItem.find('.colorSoft').show();
		theItem.find('img').hide();
		theItem.children('div:not(.floatR)').addClass('inline top w150');
		theItem.appendTo('#artistsList').fadeIn();
		var offset = artistsToAdd.indexOf($item.attr('idArtist'));
		if (offset >= 0) artistsToAdd.splice(offset,1) ;
		localStorage.setItem('addProj_EtapeWIP', 'struct');
		localStorage.setItem('addProj_TeamList', artistsToAdd);
		if (artistsToAdd.length < 1) {
			localStorage.removeItem('addProj_EtapeWIP');
			localStorage.removeItem('addProj_TeamList');
			$('.submitBtns').addClass("ui-state-disabled");
			$('.deptBtn[content="proj_add_struct"]').addClass('ui-state-disabled inactiveBtn');
		}
	});
}