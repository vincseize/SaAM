
var oldTextNew = "";
var oldTitleNew = "";

$(function () {
	//////////////////////////////////////////////////////////////////////////// LISTE NEWS /////////////////////////////////////////////////////
	
	// clic sur un bouton de hide new
	$('.hideNews').click(function() {
		var idNewsToHide = $(this).parent().attr('idNews');
		if ($(this).hasClass('ui-state-activeFake')) {
			var ajaxStr = 'action=modNewsVis&idNews='+idNewsToHide+'&visibility=0';
			AjaxJson(ajaxStr, 'admin/admin_news_actions', retourAjaxVisNew);
		}
		else {
			var ajaxStr = 'action=modNewsVis&idNews='+idNewsToHide+'&visibility=1';
			AjaxJson(ajaxStr, 'admin/admin_news_actions', retourAjaxVisNew);
		}
		$(this).removeClass('ui-state-focus')
	});

	// clic sur un bouton de modif new
	$('.modNews').click(function(){											// @TODO : outil de mise en page à la nicEdit, et activer la tabulation ?
		var idNewsToMod  = $(this).parent().attr('idNews');
		
		var newsTitleDom = $('.titleNew[idNews="'+idNewsToMod+'"]');
		oldTitleNew = newsTitleDom.html();
		newsTitleDom.html('<input type="text" class="noBorder pad3 ui-corner-all w300 fondSect1 modTitleNew" value="'+oldTitleNew+'" idNews="'+idNewsToMod+'" />');
		
		var newsTextDom  = $('.textNew[idNews="'+idNewsToMod+'"]');
		oldTextNew = newsTextDom.html();
		var textHeight = newsTextDom.height() + 200;
		newsTextDom.html('<textarea class="inline mid w9p fondSect1 noBorder" style="height: '+textHeight+'px">'+oldTextNew+'</textarea>');
		newsTextDom.append('<div class="inline mid nano" idNews="'+idNewsToMod+'">'
								+'<button class="bouton valideModNews marge10r" title="valider les modifications"><span class="ui-icon ui-icon-check"></span></button>'
								+'<button class="bouton annuleModNews ui-state-error" title="annuler les modifications"><span class="ui-icon ui-icon-cancel"></span></button>'
							+'</div>');
		$('.bouton').button();
		$('textarea').focus();
	});

	// clic sur un bouton de delete new
	$('.delNews').click(function(){
		var idNewsToDel	   = $(this).parent().attr('idNews');
		var titreNewsToDel = $(this).parent().next('.titleNew').html();
		if (confirm('Êtes-vous certain(e) de vouloir supprimer la nouvelle "'+titreNewsToDel+'" ?')) {
			var ajaxStr = 'action=delNew&idNews='+idNewsToDel;
			AjaxJson(ajaxStr, 'admin/admin_news_actions', retourAjaxDelNew);
		}
	});

	// clic sur valider une modif de News
	$('.adminNewsNew').on('click', '.valideModNews', function(){
		var idNewsToMod = $(this).parent().attr('idNews');
		var newTitleNew = $('.modTitleNew[idNews="'+idNewsToMod+'"]').val();
		var newTextNew  = $('.textNew[idNews="'+idNewsToMod+'"]').children('textarea').val();
		var arrNewInfos = [newTitleNew, newTextNew];
		var ajaxStr = "action=modNews&idNews="+idNewsToMod+'&newTitle='+encodeURIComponent(newTitleNew)+'&newText='+encodeURIComponent(newTextNew);
		AjaxJson(ajaxStr, 'admin/admin_news_actions', retourAjaxModNew, arrNewInfos);
	});

	// clic sur annuler une modif de News
	$('.adminNewsNew').on('click', '.annuleModNews', function(){
		var idNewsToMod = $(this).parent().attr('idNews');
		if (confirm('Annuler les modifications ?')) {
			$('.titleNew[idNews="'+idNewsToMod+'"]').html(oldTitleNew);
			$('.textNew[idNews="'+idNewsToMod+'"]').html(oldTextNew);
		}
	});
	
	
	
	//////////////////////////////////////////////////////////////////////////// ADD NEWS /////////////////////////////////////////////////////
	
	// surveilllance des inputs
	$('.addNews').keyup(function(){
		if ($(this).val().length >= 5)
			$(this).prev('div').removeClass('ui-state-error').children('span').addClass('ui-icon-check');
		else $(this).prev('div').addClass('ui-state-error').children('span').removeClass('ui-icon-check');
		var r = true;
		$('.addNews').each(function() {
			if ($(this).val().length < 5)
				r = false;
		});
		if (r) $('.addNewBtns').show(transition);
		else $('.addNewBtns').hide();
	});

	// Clic sur bouton save & publish
	$('.addNewBtn_savePublish').click(function(){
		var addNewTitle = $('#news_title').val();
		var addNewText  = $('#news_text').val();
		if (addNewTitle == '' || addNewText == '') {alert('pas assez d\'info !');return;}
		var ajaxStr = 'action=addNew&title='+addNewTitle+'&text='+addNewText+'&visible=1';
		AjaxJson(ajaxStr, 'admin/admin_news_actions', retourAjaxAddNew);
	});

	// Clic sur bouton save
	$('.addNewBtn_save').click(function(){
		var addNewTitle = $('#news_title').val();
		var addNewText  = $('#news_text').val();
		if (addNewTitle == '' || addNewText == '') {alert('pas assez d\'info !');return;}
		var ajaxStr = 'action=addNew&title='+addNewTitle+'&text='+addNewText+'&visible=0';
		AjaxJson(ajaxStr, 'admin/admin_news_actions', retourAjaxAddNew);
	});

	// Clic sur bouton annuler
	$('.addNewBtn_cancel').click(function(){
		$('.deptBtn[content="news_list"]').click();
	});
	
});
// FIN DU DOCUMENT READY


// retour d'info après modif de visibilité de news
function retourAjaxVisNew (data) {
	if (data.error == 'OK') {
		var newState = 'publiée';
		if (data.newState == 0 ) {
			newState = 'cachée';
			$('div[idNews="'+data.idNew+'"]').parent('.adminNewsNew').attr('visible', '0').find('.hideNews').removeClass('ui-state-activeFake').blur();
		}
		else $('div[idNews="'+data.idNew+'"]').parent('.adminNewsNew').attr('visible', '1').find('.hideNews').addClass('ui-state-activeFake').blur();
		redraw_last_news();
		$('#retourAjax').html('Nouvelle '+newState+' !').addClass('ui-state-highlight').show(transition);
		setTimeout(function(){$('#retourAjax').hide(transition);}, 2000);
	}
	else $('#retourAjax').html('erreur ! <br />'+data.message).addClass('ui-state-error').show(transition);
}

// retour d'info après modif de news
function retourAjaxModNew (data, newTitle, newText) {
	if (data.error == 'OK') {
		$('#retourAjax').html('Nouvelle modifiée !').addClass('ui-state-highlight').show(transition);
		$('.titleNew[idNews="'+data.idNew+'"]').html(newTitle);
		$('.textNew[idNews="'+data.idNew+'"]').html(newText);
		$('.dateNew[idNews="'+data.idNew+'"]').html('<i>'+data.dateNew+'</i>');
		setTimeout(function(){$('#retourAjax').hide(transition);}, 2000);
		$('.titleNew[idNews="'+data.idNew+'"]').parent('.adminNewsNew').prependTo('#listeNews');
		redraw_last_news();
	}
	else $('#retourAjax').html('erreur ! <br />'+data.message).addClass('ui-state-error').show(transition);
}


// retour d'info après ajout de news
function retourAjaxAddNew (data) {
	if (data.error == 'OK') {
		$('#retourAjax').html('Nouvelle ajoutée !').addClass('ui-state-highlight').show(transition);
		setTimeout(function(){
			$('#retourAjax').hide(transition, function(){
				$('.deptBtn[content="news_list"]').click();
			}); 
		}, 2000);
	}
	else $('#retourAjax').html('erreur ! <br />'+data.message).addClass('ui-state-error').show(transition);
}


// retour d'info après delete de news
function retourAjaxDelNew (data) {
	if (data.error == 'OK') {
		$('#retourAjax').html('Nouvelle supprimée !').addClass('ui-state-highlight').show(transition);
		$('.titleNew[idNews="'+data.idNew+'"]').parent('.adminNewsNew').remove();
		redraw_last_news();
		setTimeout(function(){$('#retourAjax').hide(transition);}, 2000);
	}
	else $('#retourAjax').html('erreur ! <br />'+data.message).addClass('ui-state-error').show(transition);
}


// redessine les surbrillances des news
function redraw_last_news () {
	var t = new Date();
	var timeNow = parseInt($.datepicker.formatDate('@', t) / 1000);
	var n = 1;
	$('.adminNewsNew:not(.sticky)').removeClass('fondPage bordHi').addClass('fondSect3');
	$('.adminNewsNew').each(function(){
		var idNew = $(this).children('.dateNew').attr('idNews');
//		if (idNew == 1) return true;
		var timeNew = $(this).children('.dateNew').attr('timeNew');
		var visible = $(this).attr('visible');
		if (timeNew >= timeNow - age_MAX_NEWS && n <= home_MAX_NEWS-1  && visible == '1') {
			$('.titleNew[idNews="'+idNew+'"]').parent('.adminNewsNew').removeClass('fondSect3').addClass('fondPage bordHi');
			console.log('New #'+n+' ('+idNew+') = '+timeNew);
			n++;
		}
	});
}