
// Document ready
$(function(){
	$('.pageContent').off('click');
	// Tags globaux
	$('.pageContent').on('click','#addGlobalTagBtn', function() {
		var globTagName = encodeURIComponent($('#addGlobalTag').val());
		if (globTagName == '') return;
		var ajaxReq = "action=addGlobalTag&tagName="+globTagName;
		AjaxJson(ajaxReq, "admin/admin_tag_actions", reloadPage);
	});
	$('.pageContent').on('click','.delGlobalTagBtn', function() {
		var globTagName = encodeURIComponent($(this).attr('tagName'));
		if (globTagName == '') return;
		var ajaxReq = "action=delGlobalTag&tagName="+globTagName;
		AjaxJson(ajaxReq, "admin/admin_tag_actions", reloadPage);
	});

	// Tags de user
	$('.pageContent').on('click','#addUserTagBtn', function() {
		var userTagName = encodeURIComponent($('#addUserTag').val());
		if (userTagName == '') return;
		var ajaxReq = "action=addUserTag&tagName="+userTagName;
		AjaxJson(ajaxReq, "admin/admin_tag_actions", reloadPage);
	});
	$('.pageContent').on('click','.delUserTagBtn', function() {
		var userTagName = encodeURIComponent($(this).attr('tagName'));
		if (userTagName == '') return;
		var ajaxReq = "action=delUserTag&tagName="+userTagName;
		AjaxJson(ajaxReq, "admin/admin_tag_actions", reloadPage);
	});
	// Partage de tag
	$('.pageContent').on('click','.shareUserTagBtn', function() {
		var userTagName = encodeURIComponent($(this).attr('tagName'));
		if (userTagName == '') return;
		var userIdToShare = $(this).attr('idFriend');
		var userToShare = $(this).html();
		var ajaxReq = "action=shareUserTag&tagName="+userTagName+'&userIdToShare='+userIdToShare+'&userToShare='+userToShare;
		AjaxJson(ajaxReq, "admin/admin_tag_actions", reloadPage);
	});

	$('.pageContent').on('click','.gotoShotFromTag', function() {
		var idProj = $(this).attr('idProj');
		localStorage['lastGroupDepts_'+idProj] = "shots";
		localStorage['lastDept_'+idProj+'_GRP_shots'] = localStorage['lastDeptMyShot'];
		localStorage['activeBtn_'+idProj]	= localStorage['lastTplMyShot'];
		localStorage['lastDept_'+idProj]	= localStorage['lastDeptMyShot'];
		localStorage['openSeq_'+idProj]	= $(this).attr('idSeq');
		localStorage['openShot_'+idProj]	= $(this).attr('idShot');
		openProjectTab(idProj);
	});


});
// FIN document ready

var timerAlert;

function reloadPage(datas) {
	clearTimeout(timerAlert);
	if (datas.error == 'OK') {
		$('#retourAjax').html('<b>'+datas.message+'</b>').removeClass('ui-state-error').show(transition);
		$('.deptBtn[active]').click();
	}
	else {
		$('#retourAjax').html('<b>'+datas.message+'</b>').addClass('ui-state-error').show(transition);
	}
	timerAlert = setTimeout(function(){$('#retourAjax').fadeOut(transition*2, function(){$('#retourAjax').removeClass('ui-state-error');});}, 4000);
}