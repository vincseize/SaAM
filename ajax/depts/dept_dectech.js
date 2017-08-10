
// Document ready
$(function(){

	// Click sur le bouton "planche contact" en haut du projet
	$('#global_contactSheet').click(function(){
		var seqExcludeList = [];
		$('.seq_contactSheetActive').each(function(i,elem){
			if (!$(this).hasClass('ui-state-activeFake'))
				seqExcludeList.push($(this).parents('li').attr('idSeq'));
		});
		var seqExcludeListJ = encodeURIComponent(JSON.encode(seqExcludeList));
		console.log(seqExcludeList);
		var params = 'proj_ID='+project_ID+'&exclSeq='+seqExcludeListJ;
		window.open('modals/sheets/dectech_sheet.php?'+params, "ContactSheet", "menubar=no, scrollbars=yes, width=1200");
	});

	// Click sur le bouton "planche contact" sur ligne séquence
	$('.seq_contactSheet').click(function(){
		var idSeq = $(this).parents('li').attr('idSeq');
		var shotExcludeList = [];
		$('.shot_contactSheetActive').each(function(i,elem){
			if (!$(this).hasClass('ui-state-activeFake'))
				shotExcludeList.push($(this).parents('tr').attr('idShot'));
		});
		var shotExcludeListJ = encodeURIComponent(JSON.encode(shotExcludeList));
		var params = 'proj_ID='+project_ID+'&seq_ID='+idSeq+'&exclShot='+shotExcludeListJ;
		window.open('modals/sheets/dectech_sheet.php?'+params, "ContactSheet", "menubar=no, scrollbars=yes, width=1200");
	});

	// Click sur un bouton "planche contact" sur ligne séquence
	$(document).off('click', '.shot_contactSheetActive, .seq_contactSheetActive');
	$(document).on('click', '.shot_contactSheetActive, .seq_contactSheetActive', function(){
		if ($(this).hasClass('ui-state-activeFake')) {
			$(this).removeClass('ui-state-activeFake');
		}
		else {
			$(this).addClass('ui-state-activeFake');
		}
	});

});
// FIN document ready

