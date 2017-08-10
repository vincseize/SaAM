var idSelectedTask;
// Document ready
$(function(){

	// Ouverture des détails d'une task sur clic task
	$('.tasksUI').off('click', '.taskLine');
	$('.tasksUI').on('click', '.taskLine', function(){
		var taskID = $(this).attr('taskID');
		$('.taskLine').removeClass('ui-state-activeFake');
		$(this).addClass('ui-state-activeFake');
		idSelectedTask = taskID;
		localStorage['openTask_'+project_ID] = taskID;
		$('.tasksUI-content').load('modals/showOneTask.php', {idProj:idProj, taskID:taskID}, function(){
			$('.bouton').button();
		});
	});

	if (localStorage['openTask_'+project_ID])
		$('.taskLine[taskID="'+localStorage['openTask_'+project_ID]+'"]').click();
	else
		$('.taskLine').first().click();

	$('.inputCal').datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true});
	$('#assignee_task').multiselect({noneSelectedText: '<i>Nobody</i>', selectedText: '# users', selectedList: 3, checkAllText: ' ', uncheckAllText: ' '});
	$('#entity_task').multiselect({noneSelectedText: '<i>Nothing</i>', selectedText: '# enities', selectedList: 3, checkAllText: ' ', uncheckAllText: ' '});

	// Ouverture du formulaire d'ajout de task
	$("#showAddTask").click(function(){
		$(this).hide();
		$('.noTaskMsg').hide();
		$("#addTaskDiv").show();
		$('#title_task').focus();
	});

	// Annulation d'ajout de task
	$('#cancelAddTask').click(function(){
		$("#showAddTask").show();
		$("#addTaskDiv").hide();
	});

	// Confirmation d'ajout de task
	$('#confirmAddTask').click(function(){
		var title = $('#title_task').val();
		var descr = $('#descr_task').val();
		var type  = $('#type_task').val();
		var pID   = $('#project_task').val();
		var section = $('#section_task').val();
		var entity  = $('#entity_task').val();
		var start = $('#start_task').datepicker("getDate");
		var end   = $('#end_task').datepicker("getDate");
		var assignee = $('#assignee_task').val();
		var params = {'ID_project':pID, 'section':section, 'title':title, 'description':descr, 'type':type, 'hooked_entity':entity, 'start':start, 'end':end, 'assigned_to':assignee};
		var newTaskVals = encodeURIComponent(JSON.encode(params));
		AjaxJson('action=addTask&newTaskVals='+newTaskVals, 'tasks', retourAjaxTasks);
	});

	// TRI des tasks selon colonne de la liste
	$('.triTasks').click(function(){
		var table  = $(this).parents('.tasksUI-listTable');
		var triKey = $(this).attr('tri');
		var liste  = table.find('tbody .taskLine');
		var sens   = $(this).attr('sens');
		if ($(this).hasClass('selTri')) {
			if (sens == 'ASC') sens = 'DESC';
			else sens = 'ASC';
		}
		var newOrder = sortTasks(liste, triKey, sens);
		$.each(newOrder, function(i, line) {
			var tr = line.tr;
			tr.appendTo(table);
		});
		var iconTri = (sens == 'ASC') ? 'ui-icon-triangle-1-s' : 'ui-icon-triangle-1-n';
		table.find('.iconTri').appendTo($(this)).children('span').removeClass('ui-icon-triangle-1-s ui-icon-triangle-1-n').addClass(iconTri);
		table.find('.triTasks').removeClass('colorHard selTri').attr('sens', 'ASC');
		$(this).addClass('colorHard selTri').attr('sens', sens);
	});


	// Changement de status d'une task
	$('.tasksUI').off('click', '.changeTaskStatus');
	$('.tasksUI').on('click', '.changeTaskStatus', function(){
		var newStatus = $(this).attr('newStatus');
		AjaxJson('action=changeTaskStatus&taskID='+idSelectedTask+'&newStatus='+newStatus, 'tasks', retourAjaxTasks);
	});

	// Suppression de task
	$('.tasksUI').off('click', '.deleteTask');
	$('.tasksUI').on('click', '.deleteTask', function(){
		if (confirm('Delete this task? Sure?'))
			AjaxJson('action=deleteTask&taskID='+idSelectedTask, 'tasks', retourAjaxTasks);
	});

	// Modification de task
	$('.tasksUI').off('click', '.openModTask');
	$('.tasksUI').on('click', '.openModTask', function(){
		$('#modalModTask').dialog({
			title: 'Task modification',
			modal: true,
			autoOpen: true,
			width: 550,
			height: 400,
			buttons: [
				{ text: "Cancel", click: function() { $(this).dialog('close'); } },
				{ text: "SAVE", click: function() { saveModTask($(this)); } }
			],
			close: function(){
				$(this).dialog('destroy');
			}
		});
		$('.modTask').first().focus();
	});

	// Ouverture de l'entity correspondante
	$('.tasksUI').off('click', '.taskVignette');
	$('.tasksUI').on('click', '.taskVignette', function(){
		alert('TODO : ouverture de l\'entity correspondante');
	});

});
// FIN document ready

// Traitement des réponses JSON
function retourAjaxTasks (retour) {
//	console.log(retour);
	$('#retourAjaxTasks').removeClass('ui-state-highlight ui-state-error');
	$('#retourAjaxTasks').html(retour.message).show(transition);
	if (retour.error == 'OK') {
		if (retour.newTask) {
			$("#addTaskDiv").hide(transition);
			$("#showAddTask").show();
			addNewTaskToList(retour.newTask);
		}
		if (retour.moveTask)
			moveTaskStatus(retour.moveTask, retour.newStatus);
		if (retour.delTask)
			removeTask(retour.delTask);
		if (retour.changeTask) {
			$('.taskLine[taskID="'+retour.changeTask+'"]').find('td')
				.first().html(retour.modTaskVals.title)
				.next().html(retour.modTaskVals.team)
				.next().html(retour.modTaskVals.from)
				.next().html(retour.modTaskVals.type)
				.next().html(retour.modTaskVals.start)
				.next().html(retour.modTaskVals.end);
			$('.taskLine[taskID="'+retour.changeTask+'"]').click();
		}
//			console.log(retour.changeTask);
		$('#retourAjaxTasks').addClass('ui-state-highlight');
		setTimeout(function(){ $('#retourAjaxTasks').hide(transition); }, 3000);
	}
	else
		$('#retourAjaxTasks').addClass('ui-state-error');
}

// TRI d'une liste de tasks selon une colonne
function sortTasks (liste, triKey, sens) {
	var oldOrder = [];
	$.each(liste, function(i, tr){
		var champ = $(tr).find('td[col='+triKey+']');
		var value = champ.html();
		if (triKey == 'team') {
			var span = champ.find('span').first();
			if (span.get(0)) value = span.html();
			else value = (sens == 'ASC') ? 'zzzzzz' : 'aaaaaa';
		}
		else if (triKey == 'start' || triKey == 'end') {
			value = parseInt(champ.attr('timeStamp'));
			if (value < 0) value = (sens == 'ASC') ? -value : value;;
		}
		if (value == '') value = (sens == 'ASC') ? 'zzzzzz' : 'aaaaaa';;
		oldOrder.push({val:value, tr:$(tr)});
	});
	var newOrder = oldOrder.slice(0);
	if (triKey == 'start' || triKey == 'end') {
		newOrder.sort(function(a,b){ return a.val - b.val; });
		if (sens == 'DESC')
			newOrder.sort(function(a,b){ return b.val - a.val; });
	}
	else {
		newOrder.sort(function(a,b){
			var x = a.val.toLowerCase();
			var y = b.val.toLowerCase();
			if (sens == 'DESC')
				return x < y ? 1 : x > y ? -1 : 0;
			return x < y ? -1 : x > y ? 1 : 0;
		});
	}
	return newOrder;
}

// Ajout d'une task dans la liste après retour ajax
function addNewTaskToList (newTaskInfos) {
	var taskLine =
		'<tr class="ui-state-focus doigt taskLine" taskID="'+newTaskInfos['id']+'">'
			+'<td class="pad5 petit">'+newTaskInfos['title']+'</td>'
			+'<td class="pad5 petit">'+newTaskInfos['team']+'</td>'
			+'<td class="pad5 petit">'+newTaskInfos['from']+'</td>'
			+'<td class="pad5 petit">'+newTaskInfos['type']+'</td>'
			+'<td class="pad5 colorSoft petit">'+newTaskInfos['start']+'</td>'
			+'<td class="pad5 colorErrText petit">'+newTaskInfos['end']+'</td>'
		+'</tr>';
	$('#taskTable-s0').append(taskLine).show();
	var oldNbTask = parseInt($('#taskTable-s0').find('.nbTask').html());
	$('#taskTable-s0').find('.nbTask').html(oldNbTask+1);
}

// Déplacement d'une task d'un status à un autre
function moveTaskStatus (taskID, newStatus) {
	var taskLine = $('tr[taskID="'+taskID+'"]');
	var taskTable= taskLine.parents('.tasksUI-listTable');
	var oldNbTask = parseInt(taskTable.find('.nbTask').html());
	taskTable.find('.nbTask').html(oldNbTask-1);
	if ((oldNbTask-1) == 0)
		taskTable.hide();
	var newNbTask = parseInt($('#taskTable-s'+newStatus).find('.nbTask').html());
	$('#taskTable-s'+newStatus).find('.nbTask').html(newNbTask+1);
	taskLine.removeClass('ui-state-default ui-state-focus');
	taskLine.addClass('ui-state-default');
	$('#taskTable-s'+newStatus).show();
	taskLine.detach().appendTo($('#taskTable-s'+newStatus));
	taskLine.click();
}

// Modification de tâche
function saveModTask (modal) {
	var taskNewVals = {};
	$('.modTask').each(function(i, input){
		var key   = $(input).attr('key');
		var value = $(input).val();
		if ($(input).hasClass('inputCal'))
			value = $(input).datepicker('getDate');
		taskNewVals[key] = value;
	});
	var modTaskVals = encodeURIComponent(JSON.encode(taskNewVals));
	AjaxJson('action=modifyTask&modTaskVals='+modTaskVals, 'tasks', retourAjaxTasks);
	modal.dialog('close');
}

// Suppression d'une task du DOM
function removeTask (taskID) {
	var taskLine = $('tr[taskID="'+taskID+'"]');
	var nextLine = taskLine.next('tr');
	var prevLine = taskLine.prev('tr');
	if (nextLine.get(0))
		nextLine.click();
	else {
		if (prevLine.get(0) && prevLine.hasClass('taskLine'))
			prevLine.click();
		else
			$('.tasksUI-content').html('<p class="colorDiscret marge30l">Select a task to show.</p>');
	}
	var taskTable = taskLine.parents('.tasksUI-listTable');
	var oldNbTask = parseInt(taskTable.find('.nbTask').html());
	taskTable.find('.nbTask').html(oldNbTask-1);
	taskLine.remove();
	if ((oldNbTask-1) == 0)
		taskTable.hide();
}