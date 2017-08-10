
$(function () {

	// bouton HIDE PROJECT
	$('.actionBtns').on('click', '.proj_hide', function(){
		var idProj = $(this).parents('li').attr('idProj');
		var titleProj = $(this).parents('li').find('.proj_title').html();
		if ($(this).hasClass('ui-state-disabled')) {
			if (confirm('Show project '+titleProj+'?'))
				AjaxJson('action=modHide&idProj='+idProj+'&hide=0', 'admin/admin_projects_actions', retourAjaxProj, 'hideOff');
		}
		else {
			if (confirm('Hide project '+titleProj+'?'))
				AjaxJson('action=modHide&idProj='+idProj+'&hide=1', 'admin/admin_projects_actions', retourAjaxProj, 'hideOn');
		}
	});

	// bouton LOCK PROJECT
	$('.actionBtns').on('click', '.proj_lock', function(){
		if ($(this).parent('td').hasClass('ui-state-disabled')) { alert('demo project: read only'); return; }
		var idProj = $(this).parents('li').attr('idProj');
		var titleProj = $(this).parents('li').find('.proj_title').html();
		if ($(this).find('span').hasClass('ui-icon-locked')) {
			if (confirm('Unlock access to project '+titleProj+'?'))
				AjaxJson('action=modLock&idProj='+idProj+'&lock=0', 'admin/admin_projects_actions', retourAjaxProj, 'lockOff');
		}
		else {
			if (confirm('Lock access to project '+titleProj+'?'))
				AjaxJson('action=modLock&idProj='+idProj+'&lock=1', 'admin/admin_projects_actions', retourAjaxProj, 'lockOn');
		};
	});

	// bouton MOD PROJECT
	$('.actionBtns').on('click', '.proj_mod', function(){
		if ($(this).parent('td').hasClass('ui-state-disabled')) { alert('demo project: read only'); return; }
		var idProj = $(this).parents('li').attr('idProj');
		var titleProj = $(this).parents('li').find('.proj_title').html();
		var newTitle = prompt('enter new title for this project:');
		if (newTitle)
			AjaxJson('action=modTitle&idProj='+idProj+'&newTitle='+newTitle, 'admin/admin_projects_actions', retourAjaxProj, 'title');
	});

	// bouton DEL PROJECT
	$('.actionBtns').on('click', '.proj_del', function(){
		if ($(this).parent('td').hasClass('ui-state-disabled')) { alert('demo project: read only'); return; }
		var idProj = $(this).parents('li').attr('idProj');
		var titleProj = $(this).parents('li').find('.proj_title').html();
		if (confirm('Archive project '+titleProj+'?'))
			AjaxJson('action=archiveProj&idProj='+idProj+'&newState=1', 'admin/admin_projects_actions', retourAjaxProj, 'archive');
	});

	// bouton ZIP PROJECT
	$('.actionBtns').on('click', '.proj_zip', function(){
		var idProj = $(this).parents('li').attr('idProj');
		var titleProj = $(this).parents('li').find('.proj_title').html();
		$('#retourAjax').html(
			'<div class="floatR doigt closeMsgAjax"><span class="ui-icon ui-icon-close"></span></div>' +
			'Please wait while creating ZIP archive for project "'+titleProj+'"...' +
			'<div class="center"><img src="gfx/ajax-loader.gif" /></div>'
		).addClass('ui-state-highlight').show(transition);setTimeout(function(){
		AjaxJson('action=zipProj&idProj='+idProj, 'admin/admin_projects_actions', function(data){
			if (data.error == 'OK') {
				$('#retourAjax').html(
					'<div class="floatR doigt closeMsgAjax"><span class="ui-icon ui-icon-close"></span></div>' +
					data.message + '<br />' +
					"<a class='big gras' href='datas/projects/"+data.zip_url+"'>Click here to download ZIP backup file.</a>"
				).addClass('ui-state-highlight').show(transition);
				$('.closeMsgAjax').click(function(){ $('#retourAjax').hide(transition).html(''); });
			}
			else {
				$('#retourAjax').html('Error! '+data.message).addClass('ui-state-error').show(transition);
				setTimeout(function(){$('#retourAjax').hide(transition);}, 10000);
			}
		});}, 2000);
	});


	// bouton DESTROY PROJECT
	$('.actionBtns').on('click', '.proj_destroy', function(){
		if ($(this).parent('td').hasClass('ui-state-disabled')) { alert('demo project: read only'); return; }
		var idProj = $(this).parents('li').attr('idProj');
		var titleProj = $(this).parents('li').find('.proj_title').html();
		if (confirm('Permanently delete the project '+titleProj+'?'))
			AjaxJson('action=destroyProj&idProj='+idProj, 'admin/admin_projects_actions', retourAjaxProj, 'blast');
	});

	// bouton RESTORE PROJECT
	$('.actionBtns').on('click', '.proj_restore', function(){
		if ($(this).parent('td').hasClass('ui-state-disabled')) { alert('demo project: read only'); return; }
		var idProj = $(this).parents('li').attr('idProj');
		var titleProj = $(this).parents('li').find('.proj_title').html();
		if (confirm('Restore project '+titleProj+'?'))
			AjaxJson('action=archiveProj&idProj='+idProj+'&newState=0', 'admin/admin_projects_actions', retourAjaxProj, 'restore');
	});
});
// FIN DU DOCUMENT READY


// fonction AJAX de réorganisation des positions des projets en BDD
function ajaxUpdateProjPos (newPosArr) {
	var newPosJson = encodeURIComponent(JSON.encode(newPosArr));
	var strAjax = 'action=modPos&newPos='+newPosJson;
	AjaxJson(strAjax, 'admin/admin_projects_actions', retourAjaxProj, 'position');
}



function retourAjaxProj (data, type) {
	if (data.error == 'OK') {
		var messageOK = '';
		if (type == undefined || type == 'position') {
			messageOK = 'Les positions des projets ont bien été mises à jour';
		}
		else if (type == 'hideOn') {
			messageOK = 'Project hidden!';
			$('li[idProj="'+data.idProj+'"]').find('.proj_hide').addClass('ui-state-disabled doigt').blur();
			refreshTabs();
			setTimeout(function(){ $(".lien[goto='admin_projects']").click() }, 1000);
		}
		else if (type == 'hideOff') {
			messageOK = 'Project showed!';
			$('li[idProj="'+data.idProj+'"]').find('.proj_hide').removeClass('ui-state-disabled').blur();
			refreshTabs();
			setTimeout(function(){ $(".lien[goto='admin_projects']").click() }, 1000);
		}
		else if (type == 'lockOn') {
			messageOK = 'Project locked!';
			$('li[idProj="'+data.idProj+'"]').find('.proj_lock').addClass('ui-state-disabled').blur().find('span').removeClass('ui-icon-unlocked').addClass('ui-icon-locked');
		}
		else if (type == 'lockOff') {
			messageOK = 'Project Unlocked!';
			$('li[idProj="'+data.idProj+'"]').find('.proj_lock').removeClass('ui-state-disabled').blur().find('span').removeClass('ui-icon-locked').addClass('ui-icon-unlocked');
		}
		else if (type == 'title') {
			messageOK = 'Project renamed!';
			$('li[idProj="'+data.idProj+'"]').find('.proj_title').html(data.newTitle);
			refreshTabs();
			setTimeout(function(){ $(".lien[goto='admin_projects']").click() }, 1000);
		}
		else if (type == 'archive') {
			messageOK = 'Project archived!';
			refreshTabs();
			setTimeout(function(){ $(".lien[goto='admin_projects']").click() }, 1000);
		}
		else if (type == 'restore') {
			messageOK = 'Project restored!';
			$('li[idProj="'+data.idProj+'"]').find('.actionBtns').html(
				 '<button class="bouton greyButton doigt proj_hide" title="montrer/cacher"><span class="ui-icon ui-icon-lightbulb"></span></button> '
				+'<button class="bouton proj_lock" title="bloquer"><span class="ui-icon ui-icon-unlocked"></span></button> '
				+'<button class="bouton marge10l proj_mod" title="modifier le titre"><span class="ui-icon ui-icon-pencil"></span></button> '
				+'<button class="bouton marge10r proj_del" title="archiver"><span class="ui-icon ui-icon-trash"></span></button>'
			);
			$('.bouton').button();
			$('.greyButton').addClass('ui-state-disabled doigt');
		}
		else if (type == 'blast') {
			messageOK = 'Project permanently deleted!<br />Rebuilding users assignations...';
			setTimeout(function(){AjaxJson('action=purgeUsersMyItems', 'admin/admin_users_actions', retourAjaxMsg);}, 1000);
			$('li[idProj="'+data.idProj+'"]').remove();
		}

		$('#retourAjax').html(messageOK).addClass('ui-state-highlight').show(transition);
		setTimeout(function(){$('#retourAjax').hide(transition);}, 1000);
	}
	else {
		$('#retourAjax').html('Error! '+data.message).addClass('ui-state-error').show(transition);
		setTimeout(function(){$('#retourAjax').hide(transition);}, 10000);
	}
}