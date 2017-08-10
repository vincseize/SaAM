

// Fonction Ajax - méthode de récupération HTML. Paramètres :
// @dataStr   = chaine de requête à envoyer,
// @dest      = nom du fichier php de traitement (sans l'extension 'php'),
// @reload    = auto reload, si n'est pas null ou false, recharge la page en cours,
// @divRetour = id de la div où afficher le retour
//				OU BIEN
//				nom de la modal à mettre dans l'url GET
function AjaxFct (dataStr, dest, reload, divRetour, urlToGo ) {
	if (divRetour == null || divRetour == undefined || divRetour == '')
		divRetour = 'debugAjax';
	$.ajaxq('ajaxQueue', {
		url: "./actions/"+dest+".php",
		type: "POST",
		data: dataStr,
		success: function (retour) {
			if (reload != null && reload != undefined && reload != false) {
				window.location.reload();
			}
			else {
				if (retour != '') {
					$("#"+divRetour).html('<a class="bouton" href="#" style="float:right; font-size:0.9em; top:-5px;" onclick="reloadAtUrl(\''+urlToGo+'\')" title="rafraichir la page">oki</a>'+retour);
					$("#"+divRetour).show(300);
					$("#"+divRetour).effect('pulsate', 300);
					$(".bouton").button();
				}
			}
		},
		error: function () {
			alert('Erreur Ajax ! vérifiez votre connexion à internet. Il se peut aussi que le serveur soit temporairement inaccessible... WTF!');
		}
	});
}
////////////////////////////////////////////////////////////////////////


// Fonction Ajax - méthode de récupération JSON. Paramètres :
// @dataStr  = chaine de requête à envoyer,
// @dest     = nom du fichier php de traitement des actions (sans l'extension '.php'),
// @callback = nom de la fonction qui va traiter le JSON
// @params   = un paramètre (string), ou un tableau des paramètres à envoyer à la fonction de callback si besoin
// remarque : le décodage JSON se fait ici, pas besoin de le faire après... On peut directement traiter l'objet data
function AjaxJson (dataStr, dest, callback, params) {
	var parametres = new Array();
	parametres = parametres.concat(params);
	$.ajaxq('ajaxQueue', {
		url: "actions/"+dest+".php",
		type: "POST",
		cache: false,
		data: dataStr,
		success: function (retour) {
			try {var data = jQuery.parseJSON(retour);}
			catch (err) { alert('RETOUR PHP :\n\n'+ retour); console.log('AjaxJson() ERROR : \n\n '+err); abortAjax(); }
			parametres.unshift(data);
			callback.apply(this, parametres);
		},
		error: function () {
			alert("Erreur Ajax !\n Fichier d'action inexistant, ou connexion à internet interrompue... \n merci de vérifier.");
		}
	});
}
////////////////////////////////////////////////////////////////////////



// Fonction pratique pour cas général de traitement de retour Json
// @retour : error -> si par d'erreur, chaine 'OK', sinon la chaine de l'erreur
//			 type  -> 'message', ou bien 'removeDiv'
//			 divID -> l'id de la div pour le message, ou bien à supprimer
function alerteErr (retour) {
	if (retour == undefined || retour == null) { alert('no data response !'); return; }
	if (retour.error == 'OK') {
		if (retour.type == 'message') {
			$('#'+retour.divID).html(retour.message);
			$('#'+retour.divID).show(transition);
			if (retour.reload != undefined && retour.reload != '') {
				setTimeout('loadPage("'+retour.reload+'")', tempsMessages) ;
			}
		}
		else if (retour.type == 'removeDiv') {
			$('#'+retour.divID).remove();
		}
	}
	else {
		alert(retour.error);
	}
}

// Fonction pratique pour afficher le retour json d'une action dans la div #retourAjax (qui doit être présente dans le dom)
function retourAjaxMsg (retour, reload) {
	if (!retour)
		return false;
	if (retour.error == 'OK') {
		$('#retourAjax').html(retour.message).removeClass('ui-state-error').addClass('ui-state-highlight').show(transition);
		if (reload == 'relog') { $('#retourAjax').append(' Relogging...'); }
		if (retour.persistant == 'persist')
			$('#retourAjax').prepend('<div class="floatR doigt" onClick="closeRetourAjax()"><span class="ui-icon ui-icon-close"></span></div>');
		else {
			if (reload === true) $('.deptBtn[active]').click();
			setTimeout(function(){
				closeRetourAjax ();
				if (reload != false) {
					if (reload=='relog') { window.location.href = 'index.php?action=deconx'; }
					if (reload=='all') { window.location.href = 'index.php'; }
				}
			}, 2000);
		}
	}
	else {
		$('#retourAjax').html(retour.error+' : '+retour.message).addClass('ui-state-error').removeClass('ui-state-highlight').show(transition);
		$('#retourAjax').prepend('<div class="floatR doigt" onClick="closeRetourAjax()"><span class="ui-icon ui-icon-close"></span></div>');
		if (reload === true) $('.deptBtn[active]').click();
		setTimeout(function(){
			closeRetourAjax ();
			if (reload != false) {
				if (reload=='relog') { window.location.href = 'index.php?action=deconx'; }
				if (reload=='all') { window.location.href = 'index.php'; }
			}
		}, 10000);
	}
}


function closeRetourAjax () {
	$('#retourAjax').fadeOut(transition);
}


// Fonction pratique pour rafraichir la stage, ou changer de page suite à un retour ajax
function loadPage(pageToLoad) {
	$('#stage').load('pages/'+pageToLoad+'.php', function(){
		$('.bouton').button();
	});
}

// Fonction pratique pour charger un département suite à un retour ajax
// @toLoad  : nom du fichier à charger (sans 'php') ( doit être présent dans pages/depts/ )
// @data	: objet JS contenant les paramètres POST à envoyer
function loadDept(toLoad, data) {
	var deptFolder = 'depts'
	if (toLoad == '00_structure' || toLoad == '01_daylies' || toLoad == '03_budget' || toLoad == '04_bank' || toLoad == '05_final')
		deptFolder = 'fixedDepts';
	if (data == undefined)
		$('.pageContent').load('pages/'+deptFolder+'/'+toLoad+'.php');
	else {
		if (typeof(data) != 'object') { alert('data parameter not an object'); return; }
		$('.pageContent').load('pages/'+deptFolder+'/'+toLoad+'.php', data);
	}
}




function abortAjax() {
	$('#loadingIcon').hide();
	$('#bigDiv').css('cursor', 'auto');
}