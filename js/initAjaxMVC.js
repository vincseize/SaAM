var stageHeight = 0;

$(function () {

	// TRAITEMENT MVC POUR LOAD @PAGES
	// @important : pour que ça marche, il faut que l'élément aie la classe 'lien' ET un attribut 'goto' qui contient le nom du fichier (sans l'ext php)
	// @exemple   : <button class="bouton lien" goto="add_news">ADD NEWS</button>
	// @attention : ceci désélectionne l'onglet actif en haut.
	$(document).on('click', '.lien[goto]', function() {
		var thisLien = $(this);
		var loadPage = $(this).attr('goto');
		$('.lien').removeAttr('clicked');
		$("#ongletsProjets" ).tabs('select', 1);
		$('#stage').load('pages/'+loadPage+'.php', function(response, status, xhr) {
			if (status == "error") {
				console.log(response);
				console.log(xhr);
				alert("ERREUR lors du chargement de la page "+loadPage+" :\n\n" + xhr.status + " -- " + xhr.statusText +"\n\n"+response);
				return;
			}
			else {
				thisLien.attr('clicked', '');
				initMenuStage(loadPage);
				$('.bouton').button();
			}
		});
	});

	// TRAITEMENT MVC POUR LOAD @PAGECONTENT
	// Chargement des sous menu du header stage
	$('.C').on('click','.deptBtn', function(){
		if (!$(this).hasClass('inactiveBtn'))
			loadPageContent($(this));
	});


	// init des onglets (projects)
	initTabs();


	// Si un $_GET est défini dans l'adresse, ouvre le bon onglet (grâce à l'idProj)
	if (ongletOnLoad != false) {
		var listTab = {}; var index = 0;
		$("#ongletsProjets").find('li').each(function() {
			var idProj = $(this).children('a').attr('idProj');
			if (idProj != undefined) listTab[idProj] = index;
			index++;
		});
		$("#ongletsProjets").tabs('select', listTab[ongletOnLoad]);
	}


	$('body').jpreLoader();


	// Évènements appellés sur chaque ajax start/stop
	$('#bigDiv').ajaxStart(function(){
		if (hideLoader === true) return;
		$("#loadingIcon").show();
		$(this).css('cursor', 'wait');
	});
	$('#bigDiv').ajaxStop(function(){
		$("#loadingIcon").hide();
		$(this).css('cursor', 'auto');
		initOnAjaxLoad();
	});



	// ouverture des fenêtres de tasks
	$(document).off('click', '.showTasks');
	$(document).on('click', '.showTasks', function(){
		var section = $(this).attr('section');
		var entity  = $(this).attr('entity');
		$('#dialog-tasks').load('modals/showTasks.php', {projectID: idProj, section:section, idEntity:entity}, function(){
			$('#dialog-tasks').dialog({
				modal: true,
				autoOpen: true,
				width: winWidth-200,
				height: winHeight-100
//				buttons: [
//					{ text: "ADD TASK", click: function() {
//							console.log($(this));
//						}
//					}
//				]
			});
		});
	});

});
// FIN DU DOCUMENT READY

function initTabs() {
	$("#ongletsProjets" ).tabs({
		spinner: "<img src='gfx/ajax-loader-white.gif' />",
		load:  function(e, ui) {
			delete window.shot_ID;
			delete window.seq_ID;
			var onglet = $(ui.tab).attr('idProj');
			$('.lien').removeAttr('clicked');
			initMenuStage(onglet);
		},
		cookie: {expires: 7},
		ajaxOptions: {
			error: function( xhr, status, index, anchor ) {
				$( anchor.hash ).html("NO DATA !");
			}
		}
	});
}

// rafraîchi les onglets
function refreshTabs () {
	$('#ongletsProjets').tabs('destroy');
	$('#ongletsProjets').load('modals/tabsProjects.php', function(){initTabs();});
}

// Appellé à chaque chargement Ajax abouti (tous les load(), et toutes autres requêtes Ajax) :
function initOnAjaxLoad () {
	// init des scrollbar
	stageHeight = $('#stage').height();
	$('.pageContent:not(.noscroll)').slimScroll({
		position: 'right',
		height: (stageHeight-28)+'px',
		size: '10px',
		wheelStep: 10,
		railVisible: true
	});

	// boutons du header stage
	$('.deptBtn').removeClass('ui-state-active colorHard');
	$('.deptBtn[active]').addClass('ui-state-active colorHard');
}


// Appellé à chaque chargement depuis un onglet ou un ".lien"
function initMenuStage(from) {
	// Applique le fond à la div stage
	$('#stage').addClass('fondSect2');
	// Highlight le bouton cliqué
	$('.lien').removeClass('ui-state-focusFake colorHard');
	$('.lien[clicked]').addClass('ui-state-focusFake colorHard');

	// Si cookie présent et renvoie sur onglet caché (1= no tab selected trick), alors on ouvre home (pour éviter le bug de stage vide au chargement)
	if ($.cookie('ui-tabs-1') == '1') {
		$.cookie('ui-tabs-1', '0', {expires: 7});
		from = $('.lien[clicked]').attr('goto');
	}

	// Récupération du bouton actif la dernière fois, et activation avant load
	if (localStorage['activeBtn_'+from]) {
		var activeContent	 = localStorage['activeBtn_'+from];			// Dernier contenu ouvert pour ce projet
		var lastDept		 = localStorage['lastDept_'+from];				// Dernier dept ouvert pour ce projet
//		console.log('from = '+ from);
//		console.log('activeContent = '+ activeContent);
//		console.log('lastDept = '+ lastDept);
		$('.deptBtn').removeAttr('active');
		if (activeContent.match(/template/g))
			$('#shots_depts').find('.deptBtn[label="'+lastDept+'"]').attr('active', '');
		else if (activeContent == '05_scenes')
			$('#scenes_depts').find('.deptBtn[label="'+lastDept+'"]').attr('active', '');
		else if (activeContent == '06_assets')
			$('#assets_depts').find('.deptBtn[label="'+lastDept+'"]').attr('active', '');
		else if (activeContent == '30_tasks')
			$('#tasks_depts').find('.deptBtn[label="'+lastDept+'"]').attr('active', '');
		else
			$('.deptBtn[content="'+activeContent+'"]').attr('active', '');
	}
	else {
		$('#racine_depts').find('.deptBtn[label="daylies"]').click().attr('active', '');
	}

	// Charge un contenu au chargement du sous menu actif
	loadPageContent($('.deptBtn[active]'));

	// boutons du header stage
	$('.deptBtn').hover(
		function() {
			$(this).addClass('ui-state-hover').removeClass('colorMid');
		},
		function() {
			$(this).removeClass('ui-state-hover').addClass('colorMid');
		}
	);
}


// Charge le contenu d'une page (traitement des boutons du headerStage)
function loadPageContent(thisBtn) {
	var params = null;
	var pathToLoad = thisBtn.attr('type');
	var fileToLoad = thisBtn.attr('content');
	if (fileToLoad == undefined) return;
	var deptID	   = thisBtn.attr('idDept');
	var deptName   = thisBtn.attr('label');
	var activeGroupDepts = $('#selectDeptsList').val();					// Récup du groupe de dept actuellement ouvert
	var template   = fileToLoad;

	if (deptName == 'structure' || deptName == 'scenario' || deptName == 'dectech' || deptName == 'storyboard' || deptName == 'sound' || deptName == 'final' )
		template = 'dept_'+deptName;

	if (pathToLoad == 'depts' || pathToLoad == 'fixedDepts') {
		activeProject = $('.projIdentity').attr('projectID');
		params = {projectID: activeProject, dept: deptName, deptID: deptID, template: template, typeArbo: activeGroupDepts};
		if (window['shot_ID'] != undefined) {
			params['sequenceID'] = seq_ID;
			params['shotID'] = shot_ID;
		}
		$('#arboMenu').load('modals/menuArbo.php', params).css('visibility', 'visible');
	}
	else $('#arboMenu').load('modals/menuArbo.php');
//	console.log(pathToLoad, fileToLoad, params);
	$('.pageContent').load('pages/'+pathToLoad+'/'+fileToLoad+'.php', params, function(response, status, xhr) {
		if (status == "error") {
			console.log(params);
			console.log("réponse de /pages/"+pathToLoad+'/'+fileToLoad+".php : \n" +response);
			console.log(xhr);
			return false;
		}
		else {
			$('.deptBtn').removeAttr('active');
			thisBtn.attr('active', '');
			var from = $('.ui-tabs-selected').find('a').attr('idProj');
			if (from == undefined)
				from = $('.lien[clicked]').attr('goto');
			localStorage['activeContent'] = from;
			localStorage['activeBtn_'+from] = fileToLoad;
			if (deptName) {
				localStorage['lastDept_'+from] = deptName;
				localStorage['lastDept_'+from+'_GRP_'+activeGroupDepts] = deptName;
				if (pathToLoad == 'depts' || deptName=='dectech' || deptName=='storyboard') {
					localStorage['lastDeptMyShot'] = deptName;
					localStorage['lastTplMyShot']  = template;
				}
			}

			reCalcScrollMyMenu();
		}
	});

}

// Charge le sous contenu d'une page (depuis le dossier des modals) POUR LES SOUS PARTIES DE DEPTS (séquences, plans, etc.)
// @toLoad  : nom du fichier à charger (sans 'php') ( doit être présent dans modals/ )
// @data	: objet JS contenant les paramètres POST à envoyer !OBLIGATOIRE
function loadPageContentModal (toLoad, params) {
	if (typeof(params) !== 'object') { alert('loadPageContentModal() : invalid parameter  ! (type object expected)'); return; }
	$('.pageContent').load('modals/'+toLoad+'.php', params, function(response, status, xhr) {
		if (status == "error")
			alert("Error: "+ xhr.status + " - " + xhr.statusText);
	});
}