
// construction de la liste des projet, et de l'array des IDs
var projIds = [];
for (i in projList) {
	projIds.push(i);
}
var itv=[];var projN=0; var itvPell; var nbShotsViewables=1; var shotN=0;


/// Document Ready
$(function() {
	$('.bouton').button();
	$('#calendarNav').hide();
	$('#caroussel_'+activeProj).show();
	$('.carousselContent').roundabout();

	// init des vignettes shots
	var pelliWidth = $('#pelliculeShots').width();
	nbShotsViewables = Math.floor(pelliWidth/62) - 3;
	itvPell = setInterval('addShotToPelloche()', 100);

	$('.sticky').css({width: 'auto', top: '41px'}).draggable();
//	$('.welcomeHome').children('div').hide();

	// Placement aléatoire des news
	var topNew = $('.sticky').height() + 60; // En pixels
	var leftNew = 2; // En pourcent
	var maxheight = $('#newsHome').height()- 50;
	$('.homeNew').each(function(i, elem) {
		if ($(elem).hasClass("sticky")) { return true; }
		$(elem).children('.dateNew').hide();
		topNew += Math.random() * (100*i) ;
		if (topNew >= maxheight) {
			topNew = 10;
			leftNew = 51;
		}
		$(elem).css({top: topNew, left: leftNew+'%', opacity: 1/(i*1.5)}).draggable();
		leftNew += Math.random() * 40;
	});

	// Click sur agrandissement de news pour la lire
	$('#newsHome').on('click', '.ui-icon-newwin', function () {
		$('.newShowed').children('div').hide(transition);
		$('.newShowed').children('.dateNew').hide(transition);
		if (!$(this).parent('div').hasClass('newShowed')) {
			$('#newsHome').children('div').removeClass('newShowed').css({width: 'auto', 'z-index': 100});
			$(this).parent('div')
				.addClass('newShowed')
				.css({opacity: 1, width: '49%', 'z-index': 200}, transition)
				.children('div').show(transition);
			$(this).parent('div').children('.dateNew').show(transition);
		}
		else $('#newsHome').children('div').removeClass('newShowed').css({width: 'auto', 'z-index': 100});
	});

	initBars(activeProj);			// @params : id du projet à montrer par défaut
	initInfos(activeProj);			// @params : id du projet à montrer par défaut

	drawTree (nbProjects, 1, 0);	// @params : nombre de branches, niveau de détails (0=max, 1, 2, 3=min), décalage X de la base du tronc

	// boutons projet suivant / précédent
	$('.navigOverview').click(function(){
		var idProjToAff		= $(this).attr('idProj');
		var titleProjToAff	= $(this).attr('title');

		$('#vignettesProjects').fadeOut(transition, function() {
			$('#vignettesProjects').html(vignetList[idProjToAff]);
			$('#vignettesProjects').fadeIn(transition);
		});

		$('.pBs').hide();
		$('.projInfos').hide();
		initBars(idProjToAff);
		initInfos(idProjToAff);
		shotN = 0;
		$('#pelliculeShots').html('');
		itvPell = setInterval('addShotToPelloche()', 100);

		$('.carousselContent').fadeOut(transition);
		$('.carousselContent').roundabout("setBearing", 180);
		setTimeout(function() {
			$('#caroussel_'+idProjToAff).fadeIn(transition, function(){
				$('#caroussel_'+idProjToAff).roundabout("animateToChild", 0);
			});
		}, transition);

		$('#projOverviewed').html(titleProjToAff);
		var prevP = findPrevProj(idProjToAff) ;
		var nextP = findNextProj(idProjToAff) ;
		$('#prevProj').attr('idProj', prevP).attr('title', projList[prevP]);
		$('#nextProj').attr('idProj', nextP).attr('title', projList[nextP]);
	});

	// @todo : click sur un shot de la pellicule pour l'ouvrir
	$('#pelliculeShots').on('click', '.shotVi', function(){
		var idShot = $(this).attr('idShot');
//		alert('TODO : aller sur le shot #'+idShot+' direct ? Hum, pas sûr...');
	}).on('mouseenter', '.shotVi', function(){
		$(this).addClass('opak');
	}).on('mouseleave', '.shotVi', function(){
		$(this).removeClass('opak');
	});


});
//// Fin du document Ready


function findPrevProj (idP) {
	for (i = 0; i < projIds.length; i++) {
		if (projIds[i] == idP) result = projIds[i-1];
	}
	if (result == undefined) result = projIds[projIds.length-1];
	return result;
}
function findNextProj (idP) {
	for (i = 0; i < projIds.length; i++) {
		if (projIds[i] == idP) result = projIds[i+1];
	}
	if (result == undefined) result = projIds[0];
	return result;
}

// initialise les progress bars des projets
function initBars (projID) {
	activeProj = projID;
	$('#progBarsProj_'+projID).show();
	$('.progBar[idProj="'+projID+'"]').each(function() {
		var percent = $(this).attr('percent');
		$(this).progressbar("destroy");
		$(this).progressbar({value:0, create: function(){animateProgBarVals($(this), percent);}});
		projN++;
	});
}

// initialise les infos du projet (nb seq, nb shots, nb assets)
function initInfos (projID) {
	$('#infosProj_'+projID).show();
}

// initialise et anime la pellicule des plans
function addShotToPelloche () {
	shotN++;
	if (shotN > nbShotsViewables) {
		window.clearInterval(itvPell);
		return;
	}
	if (shotsList[activeProj][shotN-1] == undefined)
		return

	var idShot		 = (shotsList[activeProj][shotN-1][0]).toString();
	var titleShot	 = (shotsList[activeProj][shotN-1][1]).toString();
	var vignetteShot = (shotsList[activeProj][shotN-1][2]).toString();

	$('#pelliculeShots').append(
		'<div	class="inline mid ui-corner-all fondSect3 shotVi" '
		+'		style="opacity: '+1/((shotN+1)/2)+'; background-image:url(\''+vignetteShot+'\');" '
		+'		title="'+titleShot+'" idShot="'+idShot+'"></div>');
}