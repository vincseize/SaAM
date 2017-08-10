$(document).ready(function() {
	$('#dumpSQL').click(function() {
		var tableToSave = $('#tableList').val();
		if (confirm('Save database to file?'))
			AjaxJson ('action=dump&table='+tableToSave, 'sql_utils_actions', alerteErr);
	});

	$('#restoreSQL').click(function() {
		var fileBackup = $('#dumpList').val();
		if (fileBackup != '----') {
			if (confirm('WARNING! You will perform a restore of the database with the file:\r\n'+fileBackup+'\r\nThis action is irreversible!\r\n \r\nCONTINUE anyway ?'))
				AjaxJson ('action=restore&fileBackup='+fileBackup, 'sql_utils_actions', alerteErr);
		}
		else alert('Please choose a file to restore in DB.');
	});

	$('#downloadSQL').click(function() {
		var fileBackup = $('#dumpList').val();
		if (fileBackup != '----') {
			window.open ('fct/downloader.php?type=sql&file=' + fileBackup );
		}
		else alert('Please choose a file to download.');
	});
});