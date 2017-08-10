
var transition		= 300;		// temps global des animations (en ms)
var tempsMessages	= 2000;		// temps global qu'un message reste affiché (en ms)

var menuleftWidth	= 12;		// largeur du menu de gauche (en %)
var menurightWidth	= 220;		// largeur du menu de droite (en %)
var footerHeight	= 130;		// hauteur du footer (en px)

var defaultShowLeftMenu = true;		// Si le menu de gauche doit être ouvert par défaut ou pas.
var topInfosHidden		= false;	// Si les infos en haut de stage doivent être cachées par défaut

var toggleBtnPlace		= '11.5%';	// @todo : améliorer le système des petites poignées (centrage, auto)

var showingHelp			= false;	// var globale pour savoir si la div de help est ouverte
var isCtrl				= false;	// var globale d'init de la touche CTRL pour les raccourcis
var isShift				= false;	// var globale d'init de la touche SHIFT pour les raccourcis
var timerSearch;					// var globale pour temporisation de la rechercher (pour éviter de surcharger le thread JS client)
var winWidth;
var winHeight;

$(function() {

	winWidth  = $(window).width();
	winHeight = $(window).height();

	// TOGGLE show / hide du menu de gauche
	if (defaultShowLeftMenu == true) {
		$('#btnLeftPanelToggle').css('left', menuleftWidth-0.5+'%');
		$('.C').css('left', menuleftWidth+'%');
		var invWidthL = 100 - menuleftWidth + 0.2;
		$('.L').css({'left': '0px', 'right': invWidthL+'%'});
		$('#footerPage').css('left', menuleftWidth+'%');
		$('#btnFooterToggle').css('left', 50+menuleftWidth/2 +'%');

		$("#btnLeftPanelToggle").toggle(
			function () {	// Cache menu de gauche
				$('.L').animate({left: '-'+menuleftWidth+'%', right: '100%'}, transition);
				$('.C').animate({left: '10px'}, transition);
				$('#footerPage').animate({left: '10px'}, transition);
				$('#btnLeftPanelToggle').animate({left: '0px'}, transition);
				$('#btnFooterToggle').animate({left: '-='+menuleftWidth/2 +'%'}, transition);
			},
			function () {	// Affiche menu de gauche
				$('#btnLeftPanelToggle').animate({left: menuleftWidth-0.5+'%'}, transition);
				$('.C').animate({left: menuleftWidth+'%'}, transition);
				$('.L').animate({left: '0px', right: 100 - menuleftWidth + 0.2+'%'}, transition);
				$('#footerPage').animate({left: menuleftWidth+'%'}, transition);
				$('#btnFooterToggle').animate({left: '+='+menuleftWidth/2 +'%'}, transition);
			}
		);
	}
	else {
		$('.L').css({'left': '-'+menuleftWidth+'%', 'right': '100%'});
		$('.C').css('left', '10px');
		$('#btnLeftPanelToggle').css('left', '0px');

		$("#btnLeftPanelToggle").toggle(
			function () {	// Affiche menu de gauche
				$('#btnLeftPanelToggle').animate({left: menuleftWidth+'%'}, transition);
				$('.C').animate({left: menuleftWidth+'%'}, transition);
				$('.L').animate({left: '0px', right: 100 - menuleftWidth + 0.2+'%'}, transition);
				$('#footerPage').animate({left: menuleftWidth+'%'}, transition);
				$('#btnFooterToggle').animate({left: '+='+menuleftWidth/2 +'%'}, transition);
			},
			function () {	// Cache menu de gauche
				$('.L').animate({left: '-'+menuleftWidth, right: '100%'}, transition);
				$('.C').animate({left: '10px'}, transition);
				$('#footerPage').animate({left: '10px'}, transition);
				$('#btnLeftPanelToggle').animate({left: '0px'}, transition);
				$('#btnFooterToggle').animate({left: '-='+menuleftWidth/2 +'%'}, transition);
			}
		);
	}

	// TOGGLE show / hide du menu de droite
	$("#btnRightPanelToggle").toggle(
		function () {	// Affiche menu de droite
			$('#btnRightPanelToggle').animate({right: menurightWidth}, transition);
			$('.C').animate({right: menurightWidth+10}, transition);
			$('.R').animate({right: '0px', width: menurightWidth}, transition);
			$('#footerPage').animate({right: menurightWidth}, transition);
			$('#btnFooterToggle').animate({left: '-='+menurightWidth/2}, transition);
			$('#version').animate({right: '10px'}).children('p').removeClass('version-vertical-text');
		},
		function () {	// Cache menu de droite
			$('.R').animate({right: '-'+menurightWidth-10}, transition);
			$('.C').animate({right: '10px'}, transition);
			$('#footerPage').animate({right: '10px'}, transition);
			$('#btnRightPanelToggle').animate({right: '0px'}, transition);
			$('#btnFooterToggle').animate({left: '+='+menurightWidth/2}, transition);
			$('#version').animate({right: '-7px'}).children('p').addClass('version-vertical-text');
		}
	);

	// TOGGLE show / hide du footer (messages)
	$("#btnFooterToggle").toggle(
		function () {	// Affiche footer
			$('#btnFooterToggle').animate({bottom: footerHeight-5}, transition);
			$('#footerPage').animate({height: footerHeight}, transition);
			$('.C').animate({bottom: footerHeight-10}, transition);
			var deltaBtn = footerHeight/2;
			$('#btnLeftPanelToggle').animate({top: '-='+deltaBtn+'px'}, transition);
			$('#btnRightPanelToggle').animate({top: '-='+deltaBtn+'px'}, transition);
			$('#shotViewFooter').animate({bottom: footerHeight+'px'}, transition);
			$('#shotView').animate({bottom: footerHeight+140+'px'}, transition);
			hideTopInfosStage();
		},
		function () {	// Cache footer
			$('#btnFooterToggle').animate({bottom: '0px'}, transition);
			$('#footerPage').animate({height: '0px'}, transition);
			$('.C').animate({bottom: '5px'}, transition);
			$('#btnLeftPanelToggle').animate({top: '50%'}, transition);
			$('#btnRightPanelToggle').animate({top: '50%'}, transition);
			$('#shotViewFooter').animate({bottom: '0px'}, transition);
			$('#shotView').animate({bottom: '140px'}, transition);
		}
	);

	// Bouton WIP (dev only)
	$('.WIPbtn').click(function(){
		var wip = $(this).attr('setWip');
		var ajaxReq = 'action=setWIP&wip='+wip;
		AjaxJson(ajaxReq, 'sql_utils_actions', retourAjaxMsg, 'all');
	});


	// Toggle des boutons de recherche
	$(document).on('click','.showSearch', function() {
		if ($(this).hasClass('ui-icon-search')) {
			$(this).removeClass('ui-icon-search');
			$(this).addClass('ui-icon-triangle-1-w');
			$(this).parent().find('.submitSearch').show(transition);
			$(this).parent().find('.searchInput').show(transition).focus();
		}
		else {
			$(this).removeClass('ui-icon-triangle-1-w');
			$(this).addClass('ui-icon-search');
			$(this).parent().find('.searchInput').val('').blur().hide(transition);
			$(this).parent().find('.submitSearch').click().hide(transition);
		}
	});
	// Input de recherche, écoute des touches spéciale et temporisation
	$(document).on('keydown','.searchInput', function(e) {
		clearTimeout(timerSearch);
		var prnt = $(this).parent();
		if (e.keyCode == 9 || e.which == 9) {
			e.preventDefault();
			prnt.find('.submitSearch').click();
		}
		if (e.keyCode == 13 || e.which == 13)
			prnt.find('.submitSearch').click();
		if (e.keyCode == 27 || e.which == 27)
			prnt.find('.showSearch').click();
		timerSearch = setTimeout(function(){ prnt.find('.submitSearch').click(); }, 300);
	});

	// hover du bouton de déconnexion
	$('#delogBtn').hover(
		function() {
			$(this).addClass('ui-state-error');
		},
		function() {
			$(this).removeClass('ui-state-error');
		}
	);

	// Affichage des boutons dev
	$('#affDevBtns').toggle(
		function() {
			$('#devBtns').show(transition);
		},
		function() {
			$('#devBtns').hide(transition);
		}
	);

	// Affichage des plugins
	$('#affPlugins').toggle(
		function() {
			$('#pluginsBtns').show(transition);
		},
		function() {
			$('#pluginsBtns').hide(transition);
		}
	);


	$('.fancybox').fancybox();
	$('.fancybox-buttons').fancybox({
		openEffect  : 'none',
		closeEffect : 'none',
		prevEffect : 'none',
		nextEffect : 'none',
		closeBtn  : false,
		helpers : { title : {type: 'inside'}, buttons: {} },
		afterLoad : function() {
			this.title = 'Image ' + (this.index + 1) + ' of ' + this.group.length + (this.title ? ' - ' + this.title : '');
		}
	});
	$('.fancybox-thumbs').fancybox({
		prevEffect : 'none',
		nextEffect : 'none',
		closeBtn  : true,
		arrows    : true,
		nextClick : true,
		helpers : { thumbs: {width: 50, height: 50} }
	});


	// init des datePickers
	$(".miniCal" ).datepicker({dateFormat: 'yymmdd', firstDay: 1, changeMonth: false, changeYear: false});		// Calendrier inline
	$(".inputCal").datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true});		// Calendrier sur focus d'input
	// pour faire des jolis boutons
	$(".bouton"  ).button();


	// Effacement du texte pré-rempli des input 'pseudo et pass'
	$('input').focus(function() {
		if ($(this).val() == 'login' || $(this).val() == 'pass')
			$(this).val('');
	});

	// Vérification de la chaine email quand on sort d'une input qui a la classe 'emailInput'
	$(document).on( 'blur' , ".emailInput" , function() {
		var email = $(this).val();
		if ( email == '' ) return ;
		if ( ! verifyEmail(email) )
			alert ('Adresse Email invalide');
	});


	// Restriction de touches pour les inputs numériques (qui ont la classe 'numericInput')
	$(document).on( 'keydown' , ".numericInput", function(event) {
			// Allow: backspace, delete, tab, escape, point
		if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 110 || event.keyCode == 190 ||
			// Allow: Ctrl+A
			(event.keyCode == 65 && event.ctrlKey === true) ||
			// Allow: Ctrl+C
			(event.keyCode == 67 && event.ctrlKey === true) ||
			// Allow: Ctrl+V
			(event.keyCode == 86 && event.ctrlKey === true) ||
			// Allow: home, end, left, right
			(event.keyCode >= 35 && event.keyCode <= 39)) {
				return;
		}
		else {
			// Vérif si c'est un nombre, et stoppe le keypress
			if ((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
				event.preventDefault();
			}
		}
	});

	// Pour faire une jolie liste de messages dans le footer
	$('#footerPage').on('hover', '.tableListe tbody tr', function (event) {
		if ($(this).hasClass('titresListe') == false) {
			if (event.type == 'mouseenter')
				$(this).addClass('fondPage');
			else $(this).removeClass('fondPage');
		}
	});

	// Raccourcis de SaaM
	$(document).keydown(function(e) {
		var lock = $('input').is(":focus") || $('textarea').is(":focus");
		switch( e.keyCode || e.which ) {
			case 16:				// touche SHIFT
				isShift = true;
				break;
			case 17:				// touche CTRL
				isCtrl = true;
				break;
			case 37:				// CTRL + left arrow
				if (isCtrl && !lock) $("#btnLeftPanelToggle").click();					// toggle menu de gauche
				break;
			case 38:				// CTRL + up arrow
				if (isCtrl && !lock) $("#proj_title").click();							// toggle proj infos en haut
				break;
			case 39:				// CTRL + right arrow
				if (isCtrl && !lock) $("#btnRightPanelToggle").click();					// toggle menu de droite
				break;
			case 40:				// CTRL + down arrow
				if (isCtrl && !lock) $("#btnFooterToggle").click();						// toggle messages en bas
				break;
			case 65:				// touche "A"
				if (!isCtrl && !lock) $('#selectDeptsList').val('assets').change();		// va dans la partie "assets" du projet ouvert
									// CTRL + "A"
				if (isCtrl && !lock) { e.preventDefault();
					$(".lien[goto='admin_panel']").click(); }							// va dans l'admin panel
				break;
			case 67:				// touche "C"
				if (!isCtrl && !lock) $(".lien[goto='config_panel']").click();			// va dans la config (dev only)
				break;
			case 69:				// touche "E"
				if (!isCtrl && !lock) $('#selectDeptsList').val('scenes').change();		// va dans la partie "scenes" du projet ouvert
				break;
			case 70:				// touche "F"
				if (!isCtrl && !lock) {
					localStorage['lastDept_'+idProj+'_GRP_shots'] = 'final';
					$('#selectDeptsList').val('shots').change();
				}
				if (isCtrl && !lock) { e.preventDefault(); $('.showSearch').click(); $('.searchInput').focus(); }// Ouvre la recherche
				break;
			case 72:				// touche "H"
				if (!isCtrl && !lock) help(transition);								// Affiche la modal d'aide
				break;
			case 78:				// touche "N"
				if (!isCtrl && !lock) $('#globalNews').slideToggle(transition);		// Affiche la modal des news
									// CTRL + "N"
				if (isCtrl && !lock) { e.preventDefault();								// va dans le gestionnaire de news
					$(".lien[goto='admin_news']").click();}
				break;
			case 79:				// touche "O"
				if (!isCtrl && !lock) $(".lien[goto='notes']").click();					// va dans les notes personnelles
				break;
			case 80:				// touche "P"
				if (!isCtrl && !lock) $(".lien[goto='prefs']").click();					// va dans les préférences utilisateur
									// CTRL + "P"
				if (isCtrl && !lock) { e.preventDefault();
					$(".lien[goto='admin_projects']").click(); }						// va dans le gestionnaire de projets
				break;
			case 82:				// touche "R"
				if (!isCtrl && !lock) $('#selectDeptsList').val('racine').change();		// va dans la partie "racine" du projet ouvert
				break;
			case 83:				// touche "S"
				if (!isCtrl && !lock) $('#selectDeptsList').val('shots').change();		// va dans la partie "shots" du projet ouvert
				break;
			case 84:				// touche "T"
				if (!isCtrl && !lock) $(".lien[goto='tags']").click();					// va dans le gestionnaire de tags
				break;
			case 85:				// CTRL + "U"
				if (isCtrl && !lock) { e.preventDefault();								// va dans le gestionnaire utilisateurs
					$(".lien[goto='admin_users']").click(); }
				break;
			case 88:				// touche "X"
				if (!isCtrl && !lock) $(".lien[goto='debug']").click();					// va sur la page debug
				break;
			case 90:				// touche "Z"
				if (!isCtrl && !lock) openProjectTab(localStorage['lastViewedProject']);// va sur le dernier onglet projet utilisé
				break;
			case 112:				// touche F1
				e.preventDefault(); window.open('indexHelp.php', '_blank');				// ouvre l'aide dans un nouvel onglet
				break;
			case 115:				// touche F4
				e.preventDefault(); $("#ongletsProjets").tabs('select', 0);				// va sur l'onglet 'HOME'
				break;
			case 116:				// touche F5
				if (!isCtrl) {e.preventDefault(); window.location = './';}				// rafraichi la page (soft)
				if (isCtrl)  {e.preventDefault(); location.reload(true); }				// rafraichi la page (hard)
				break;
			case 122:				// touche F11										// switch plein écran
				retourAjaxMsg({error:'OK', message:'Reloading SaAM, please wait...', persistant: 'persist'}, false);
				setTimeout(function(){ window.location = 'index.php' }, 3000);
				break;
									// touche F12										// firebug
			case 161:				// touche "!"
				if (!isCtrl && !lock) console.log(localStorage);							// voir le contenu du localStorage dans la console
				if (isCtrl && !lock) {localStorage.clear(); console.log(localStorage);}		// RESET localstorage
				break;
		}
	});

	// réinit des touches CTRL et SHIFT
	$(document).keyup(function (e) {
		if (e.keyCode == 16 || e.which == 16)
			isShift = false;
		if (e.keyCode == 17 || e.which == 17)
			isCtrl = false;
	});

	// Menu des my shots / my assets / my notes
	$('.myMenuHeadEntry').click(function(){
		var toShow = $(this).attr('menuLoad');
		$('#myMenu').fadeOut(transition, function(){
			hideLoader = true;
			$('#myMenu').load('modals/'+toShow+'.php', function(){
				$('#myMenu').fadeIn(transition);
				$('.myMenuHeadEntry').removeClass('ui-state-focus').addClass('ui-state-default');
				$('.myMenuHeadEntry[menuLoad="'+toShow+'"]').addClass('ui-state-focus');
				hideLoader = false;
			});
		});

	});

});
// FIN DU DOCUMENT READY


// Charge l'aide dans une div et l'affiche
function help (transitTime) {
	if (!showingHelp) {
		var overElem = $(':hover').last();
		var helpDest = overElem.attr('help');
		if (helpDest == undefined) {
			var overElemParents = $(':hover').last().parents('*[help]');
			helpDest = overElemParents.attr('help');
		}
		var debugId = overElem.prop('tagName')+' > #'+overElem.prop('id')+' > class : '+overElem.prop('class');
//		console.log(debugId);
		if (helpDest != undefined) {
			var helpWord = "Help";
			if (langue == 'fr') helpWord = 'Aide';
			$('#globalHelpContent').load('help/help_'+langue+'.php #help_'+helpDest, function(){
				$(this).prepend('<h2>'+helpWord+' : '+helpDest.replace(/_/g, ' ')+'</h2>').append('<div class="margeTop10 mini"><i>Press [ h ] to Close</i></div>');
				$(this).find('.bouton').button();
			});
		}
		else $('#globalHelpContent').load('help/help_'+langue+'.php #help_raccourcis', function(){
			var noHelpMsg = "No help available for this element!";
			if (langue == 'fr') noHelpMsg = "Pas encore d'aide pour cet élément !";
			$(this).prepend('<h3>'+noHelpMsg+'</h3> <div class="margeTop10 mini"><i>('+debugId+')</i></div>').append('<div class="margeTop10 mini"><i>Press [ h ] to Close</i></div>');
		});
		$(window).on('click', function(){
			help(300);
		});
		showingHelp = true;
	}
	else {
		$(window).off('click');
		showingHelp = false;
	}

	$('#globalHelp').slideToggle(transitTime, function(){
		$('#globalHelpContent').slimScroll({
			position: 'right',
			height: '380px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		}).parent('.slimScrollDiv').width(380);
	});
}

// Ouvre l'onglet du projet spécifié
function openProjectTab (idProj) {
	var listTab = {}; var index = 0;
	$("#ongletsProjets").find('li').each(function() {
		var idP = $(this).children('a').attr('idProj');
		if (idP != undefined) listTab[idP] = index;
		index++;
	});
	var tabToGo = listTab[parseInt(idProj)];
	if (tabToGo == undefined) tabToGo = 0;
	$("#ongletsProjets").tabs('select', tabToGo);
}

// recalcule les scrolls
// myMenu
function reCalcScrollMyMenu () {
	stageHeight = $('#stage').height();
	if ($('#myMenu').parent().hasClass('slimScrollDiv'))
		$('#myMenu').parent().replaceWith($('#myMenu'));	// Si scroll existe, on le vire
	var myMenuPos = $('#myMenu').position();
	var myMenuHeight = $('#leftCol').height() - ($('#leftCol').height() / 3) - 30;
	$('#myMenu').slimScroll({
		position: 'right',
		height: myMenuHeight+'px',
		size: '10px',
		wheelStep: 10,
		railVisible: true
	});
}

// Détruit une slimScroll !
function destroySlimScroll(elem, redim) {
	var parent  = $(elem).parent();
	var parentH = parent.height();
	parent.replaceWith($(elem));
	if (redim)
		$(elem).height(parentH);
}


// Cache les infos en haut de page
function hideTopInfosStage (transiTime) {
	$('.topInfosStage').animate({height: '34px'}, transiTime);
	$('.vignetteTopInfos img').animate({height: '34px', width: '60px', 'margin-right': '205px'}, transiTime);
	$('.projTitle').addClass('noBG');
	$('.topInfosStage').find('.toHide').fadeOut(transiTime);
	$('.topInfosStage').find('.slimScrollDiv').hide();
	$('#shotView').animate({top: '34px'}, transiTime);
	$('#deptNameBG').hide();
	topInfosHidden = true;
}

// Montre les infos en haut de page
function showTopInfosStage (transiTime) {
	$('.topInfosStage').animate({height: '150px'}, transiTime);
	$('.vignetteTopInfos img').css({height: '150px', width: '270px', 'margin-right': '0px'});
	$('.projTitle').removeClass('noBG');
	$('.topInfosStage').find('.toHide').fadeIn(transiTime);
	$('.topInfosStage').find('.slimScrollDiv').show();
	$('#shotView').animate({top: '150px'}, transiTime);
	$('#deptNameBG').show();
	topInfosHidden = false;
}


// Anime les progress bars pour le remplir doucement
function animateProgBarVals (progBar, maxVal, b) {		// @params : progBar = élément, maxVal = valeur à atteindre (%)
	var iteration = 0;var offset = 0;
	itv[projN] = window.setInterval(function(){
		iteration += 0.5;
		if (iteration < maxVal  )
			$(progBar).progressbar("option", "value", iteration);
		else {
			window.clearInterval(itv[projN]);
			$(progBar).children('span').show(transition);
		}
	}, 10);
}


// Fork de la fonction php stripslashes pour virer les slashes d'une chaine.
function stripslashes (str) {
	return (str + '').replace(/\\(.?)/g, function (s, n1) {
        switch (n1) {
			case '\\':return '\\';
			case '0' :return '\u0000';
			case ''  :return '';
			default  :return n1;
		}
    });
}

// Fork de la fonction php addslashes pour ajouter des slashes dans une chaine.
function addslashes (str) {
    return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}

// fonction pour chercher si une valeur existe dans un tableau
function in_array (cherche, tableau) {
	var key='';
	for (key in tableau) {
		if (tableau[key] == cherche) return true;
	}
	return false;
}


// Utile pour vérifier les caractères autorisés
// @params:
//		noSpace : pour interdire les espaces ( )
//		noDot   : pour interdire les points (.)
//		noTiret : pour interdire les traits d'union (-)
function checkChar (evt, noSpace, noDot, noTiret) {
	var keyCode = evt.which ? evt.which : evt.keyCode;
	var interdit = 'àâäãçéèêëìîïòôöõùûüñ&*?!:;,\t#~"^¨%$£?²¤§%*+@()[]{}<>|\\/`\'';
	if (noSpace == true) interdit += ' ';
	if (noDot == true) interdit += '.';
	if (noTiret == true) interdit += '-';
	if (interdit.indexOf(String.fromCharCode(keyCode)) >= 0 && keyCode != 9) {
		return false;
	}
}

// juste une petite aide si firebug bug !!! LOL
function jsonViewer (data) {
	var jsonView = '';
	for (k in data) {
		jsonView += k +' : ' + data[k] + '<br /> ';
		if (typeof(data[k]) == "object") {
			for (l in data[k]) {jsonView += '--- > ' + l +' : ' + data[k][l] + '<br /> ';}
		}
	};
	return jsonView ;
}


// Pour effacer le contenu d'une div, puis la cacher
function clearDiv (divE) {
	$("#"+divE).html('');
	$("#"+divE).hide(transition);
}

// Vérification d'une chaine censée être un email
function verifyEmail( email ){
	var status = false;
	var emailRegEx = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
	if (email.search(emailRegEx) == -1) {
		status = false ;
	}
	else {
		status = true;
	}
	return status;
}


// fonction de déconnection
function disconnect(login){
//	console.log('Disconnecting '+login+'...');
	$.ajax({
		type: "POST",
		url: "index.php",
		data: "action=deconx&x="+login,
		success: function(msg) { window.location.href = './' ; }
	});
}
