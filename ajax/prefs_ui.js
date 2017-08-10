var oldTime;

$(function () {
	oldTime = $('#modifDeconxTime').val();

	// Modif du temps de déconnexion automatique
	$('#modifDeconxTime').keyup(function(){
		$('#modifDeconxTimeBtns').show();
	});
	$('#modifDeconxTimeValid').click(function(){
		var newTime = $('#modifDeconxTime').val();
		modifUserVal('deconx_time', newTime);
	});
	$('#modifDeconxTimeCancel').click(function(){
		$('#modifDeconxTime').val(oldTime);
		$('#modifDeconxTimeBtns').hide();
	});

});
// FIN DU DOCUMENT READY

// éxécute la requête ajax
function modifUserVal (typeVal, newVal) {
	var ajaxReq = "action=modUserVal&row="+typeVal+"&val="+newVal;
	AjaxJson(ajaxReq, 'prefs_user_actions', retourAjaxMsg, true);
}