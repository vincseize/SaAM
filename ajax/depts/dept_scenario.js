
$(function () {

	// bouton sauvegarder
	$('#saveScenario').click(function(){
		var newScenar  = $('#writer').val();
		var scenarPath = $('#scenarPath').val();
		if (confirm('Sauvegarder le fichier scénario ? Sûr ?'))
			AjaxJson('action=saveScenario&scenarPath='+scenarPath+'&newScenarioTxt='+encodeURIComponent(newScenar)+'&idProj='+idProj+'&titleProj='+titleProj, 'depts/scenario_actions', retourAjaxMsg, false);
	});

	$('#undoScenario').click(function(){
		$('.deptBtn[active]').click();
	});

	$('#deleteScenario').click(function(){
		var scenarPath = $('#scenarPath').val();
		if (confirm('Éffacer le fichier scénario ? Sûr ?'))
			AjaxJson('action=delScenario&scenarPath='+scenarPath, 'depts/scenario_actions', retourAjaxMsg, true);
	});

});
// FIN DU DOCUMENT READY

