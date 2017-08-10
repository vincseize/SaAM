
var editMode	= false;
var idLastSel	= '';
var selected	= new Array();
var importFileTempName = '';

// Document ready
$(function(){

	// Select ALL / NONE (head colonne ID)
	$('.idTable').click(function(e){
		if (selected.length != 0) {
			$('.lineVal').removeAttr("selected").children('td').removeClass('fondError');
			$('th.idTable').find('.ui-icon').addClass('ui-icon-radio-on').removeClass('ui-icon-bullet fondError');
		}
		else {
			$('.lineVal[style!="display: none;"]').attr("selected", "yes").children('td').addClass('fondError');
			$('th.idTable').find('.ui-icon').removeClass('ui-icon-radio-on').addClass('ui-icon-bullet fondError');
		}
		updateSelection();
	});

	// fermeture du menu contextuel SaAM et réabilitation du menu contextuel navigateur
	$(document).click(function(e){
		if( e.button == 0 ) {
			$('#contextMenuProd').hide();
			document.oncontextmenu = function() {return true;};
		}
	});

	// Gestion des clic-gauche et clic-droit sur la colonne des ID (sélection de ligne)
	$('table').off('mousedown', '.idEntry');
	$('table').on('mousedown', '.idEntry', function(e){
		// left-click (sélection)
		if( e.button == 0 ) {
			document.getSelection().removeAllRanges();
			var idEntrySelect = $(this).attr('realVal');
			// touche MAJ enfoncée
			if (e.shiftKey) {
				$('tr[idEntry]:not([idEntry='+idLastSel+'])').removeAttr("selected").children('td').removeClass('fondError');
				$(this).addClass('fondError').siblings().addClass('fondError').parent('tr').attr("selected", "selected");
				if ($('tr[idEntry='+idLastSel+']').prevAll('tr[idEntry='+idEntrySelect+']').length != 0)
					$('tr[idEntry='+idLastSel+']').prevUntil('tr[idEntry='+idEntrySelect+']').attr("selected", "selected").children('td').addClass('fondError');
				else
					$('tr[idEntry='+idLastSel+']').nextUntil('tr[idEntry='+idEntrySelect+']').attr("selected", "selected").children('td').addClass('fondError');
			}
			else {
				idLastSel = idEntrySelect;
				// touche CTRL enfoncée
				if (!e.ctrlKey)
					$('tr[idEntry]').removeAttr("selected").children('td').removeClass('fondError');
				if ($(this).hasClass('fondError'))
					$(this).removeClass('fondError').siblings().removeClass('fondError').parent('tr').removeAttr("selected");
				else
					$(this).addClass('fondError').siblings().addClass('fondError').parent('tr').attr("selected", "selected");
			}
			updateSelection();
			return false;
		}
		// right-click (menu contextuel)
		if( e.button == 2 ) {
			document.oncontextmenu = function() {return false;};
			if (!$(this).hasClass('fondError')) {
				$('tr[idEntry]').removeAttr("selected").children('td').removeClass('fondError');
				$(this).addClass('fondError').siblings().addClass('fondError').parent('tr').attr("selected", "selected");
			}
			updateSelection();
			$('#contextMenuProd').css({top:(e.pageY + 1), left:(e.pageX + 5)}).show();
			return false;
		}
	});

	// Traitement des actions du menu contextuel SaAM
	$('#contextMenuProd').off('click', '.ctxMenuProd_Btn');
	$('#contextMenuProd').on('click', '.ctxMenuProd_Btn', function(){
		var action  = $(this).attr('action');
		if (action == 'export') { $('#prod_exportTable').click(); return; }
		var lineIds = encodeURIComponent(JSON.encode(selected));
		var ajaxReq = 'action=multiLine_'+action+'&customMode='+customMode+'&projID='+project_ID+'&tableName='+activeTable+'&lines='+lineIds;
		AjaxJson(ajaxReq, "depts/prod_actions", retourAjaxMsg, true);
	});


	// Bouton ajout de ligne
	$('#prod_addEntry').click(function(){
		var $dialog = $('#modalAddEntry').clone(true);
		$dialog.dialog({
			autoOpen: true, height: 500, width: 500, modal: true, hide: "fade",
			open: function() {
				$(this).find('select:not([multiple])')
						.selectmenu({style: 'dropdown', width: 260})
						.parent('div').addClass('mini');
				$(this).find('select[multiple]')
						.multiselect({noneSelectedText: '<i>Nohing</i>', selectedText: '# items', selectedList: 3, checkAllText: ' ', uncheckAllText: ' ', minWidth: 260})
						.parent('div').addClass('mini');
				$(this).find('.calendarVal').datepicker({dateFormat: 'yy-mm-dd 00:00:00', firstDay: 1, changeMonth: false, changeYear: false});
			},
			buttons: {
				'OK' : function() {
					var allValues = {};
					$('.addEntryVal').each(function(){
						var row = $(this).attr('theRow');
						var val = $(this).val();
						allValues[row] = val;
					});
					$('.addEntryVal_TC').each(function(){
						var row = $(this).attr('theRow');
						var val = '';
						$(this).children('.numericInput').each(function(){
							var tci = $(this).val();
							if (tci == '') tci = '00';
							if (tci.length == 1) tci = '0'+tci;
							val += tci+':';
						});
						allValues[row] = val.replace(/\:$/g, '');
					});
					if (activeTable == 'saam_assets') {
						if ( allValues['filename'] == '' || allValues['relative_path'] == '' ) {
							alert("Missing 'filename' and/or 'relative path' !"); return false;
						}
						if (!confirm("Are you sure you want to create an asset from here??\n\nUsually, they're created from XML masterfile..."))
							return false;
					}
					var values = encodeURIComponent(JSON.encode(allValues));
					var ajaxReq = 'action=addEntryToTable&customMode='+customMode+'&projID='+project_ID+'&tableName='+activeTable+'&values='+values;
					AjaxJson(ajaxReq, "depts/prod_actions", retourAjaxMsg, true);
					$(this).dialog('close');
				},
				'Cancel' : function() { $(this).dialog('close');  }
			},
			close: function() { $dialog.dialog('destroy'); $dialog.remove(); }
		});
	});

	// Bouton AJOUT de colonne
	$('#prod_addColumn').click(function(){
		var $dialog = $('#modalAddColumn').clone(true);
		$dialog.dialog({
			autoOpen: true, height: 280, width: 550, modal: true, hide: "fade",
			open: function() {
				$(this).find('.defaultVal-datetime').datepicker({dateFormat: 'yy-mm-dd 00:00:00', firstDay: 1, changeMonth: false, changeYear: false});
				$(this).find('.dvBsel').selectmenu({style: 'dropdown', width: 300});
				$(this).find('.selectRelGrp').selectmenu({style: 'dropdown', width: 98, change:function(){
					$('.relation_COL_SelectLine').hide();
					var relGrp = $(this).val();
					$('.grpRel').hide();
					$('.grpRel_'+relGrp).show();
				}}).parent('div').addClass('mini');
				$(this).find('.selectRelTable_predefined').selectmenu({style: 'dropdown', width: 195, change:function(){
					$('.relation_COL_SelectLine').hide();
				}}).parent('div').addClass('mini');
				$(this).find('.selectRelTable_custom').selectmenu({style: 'dropdown', width: 195, change:function(){
					$('.relation_COL_SelectLine').show();
					var theTable = $(this).val();
					AjaxJson('action=getColumns&projID='+project_ID+'&table='+theTable, "depts/prod_actions", feedColumnsSelect, $dialog);
				}}).parent('div').addClass('mini');
				$(this).find('.relation_COL_SelectLine').hide();
				$(this).find('.selectRelType').selectmenu({style: 'dropdown', width: 250}).parent('div').addClass('mini');
				$(this).find('.selectType').selectmenu({
					style: 'dropdown', width: 300,
					change: function(){
						var rowType = $(this).val();
						$dialog.find('.defaultValType').html('<i>'+rowType+'</i>').parent('div').show();
						$dialog.find('.defaultValinput').hide();
						$dialog.find('.relation_GRP_SelectLine').hide();
						$dialog.find('.relation_COL_SelectLine').hide();
						if (rowType == 'relation') {
							$dialog.find('.relation_GRP_SelectLine').show();
							$dialog.find('.defaultValType').parent('div').hide();
						}
						else if (rowType == 'datetime') {
							$dialog.find('.defaultVal-datetime').show();
						}
						else if (rowType == 'boolean') {
							$dialog.find('.defaultVal-boolean').show();
						}
						else if (rowType == 'timecode') {
							$dialog.find('.defaultVal-timecode').show();
						}
						else {
							$dialog.find('.defaultVal-text').show();
						}
					}
				}).parent('div').addClass('mini');
			},
			buttons: {
				'OK' : function() {
					var colName = $(this).find('.addColName').val();
					var colType = $(this).find('.selectType').val();
					if (colName == "") { alert('Missing name of the column!'); return; }
					var defaultValue = $(this).find('.defaultVal-text').val();
					if (colType == 'datetime')
						defaultValue = $(this).find('.defaultVal-datetime').val();
					if (colType == 'boolean')
						defaultValue = $(this).find('.dvBsel').val();
					if (colType == 'timecode') {
						defaultValue = '';
						$(this).find('.defaultVal-timecode').children('.numericInput').each(function(){
							var valTC = $(this).val();
							if (valTC == '') valTC = '00';
							if (valTC.length == 1) valTC = '0'+valTC;
							defaultValue += valTC+':';
						});
						defaultValue = defaultValue.replace(/\:$/g, '');
					}
					if (colType == 'relation') {
						var relGrp   = $(this).find('.selectRelGrp').val();
						var relTable = $(this).find('.selectRelTable_'+relGrp).val();
						colType = relTable;
						if (relGrp == 'custom')
							colType = relTable + '.' + $(this).find('.selectRelType').val() + '.' + $(this).find('.selectRelCol').val();
						defaultValue = '';
					}
					var confirmed = true;
					if (customMode == 'false') {
						confirmed = confirm('Do you REALY want to add the column\n\n            "'
											+colName+'"\n\nto SaAM internal table\n\n            "'
											+activeTable+'" ??\n\n(Be aware that it will affect ALL projects)');
					}
					if (colType == '' || colType == '*') {
						alert('Please choose a table to link with this column, or change column type.');
						confirmed = false;
					}
					if (confirmed) {
						var ajaxReq = 'action=addRowToTable&customMode='+customMode+'&projID='+project_ID+'&tableName='+activeTable+'&rowType='+colType+'&rowName='+colName+'&defaultVal='+defaultValue;
						AjaxJson(ajaxReq, "depts/prod_actions", retourAjaxMsg, true);
						$(this).dialog('close');
					}
				},
				'Cancel' : function() { $(this).dialog('close');  }
			},
			close: function() { $dialog.dialog('destroy'); $dialog.remove(); }
		});
	});

	// Boutons SUPPRESSION de colonne
	$('.deleteCustomRow').click(function(e){
		e.stopPropagation();
		var colName = $(this).parents('th').attr('rowName');
		if (!confirm('Delete column "'+colName+'" ?'))
			return false;
		var ajaxReq = 'action=deleteTableRow&customMode='+customMode+'&projID='+project_ID+'&tableName='+activeTable+'&rowName='+colName;
		AjaxJson(ajaxReq, "depts/prod_actions", retourAjaxMsg, true);
		return false;
	});

	// Bouton refresh table
	$('#prod_refreshTable').click(function(){
		$('.arboItem[prodCat="'+activeTable+'"]').click();
	});

	// Upload progress bar
	$('#uploader_Masterfile').bind('fileuploadprogress', function (e, data) {
		var filename = data.files[0].name;
		var percent =  data.loaded / data.total * 100;
		$('#retourAjax').find('.uploadProg[filename="'+filename+'"]')
						.progressbar({value: percent})
						.children('span').html('speed : '+Math.round(data.bitrate / 10000)+'Kb/s => '+Math.round(percent)+' %...');
	});

	// Bouton export table
	$('#prod_exportTable').click(function(){
		var $dialog = $('#modalExportTable').clone(true);
		$dialog.dialog({
			autoOpen: true, height: 180, width: 500, modal: false, hide: "fade",
			open: function() {
				$(this).find('.exportTableFiletype').selectmenu({
					style: 'dropdown', width: 285,
					change: function(){
						var oldName = $dialog.find('.exportTableFilename').val();
						var newExt = $(this).val();
						var newName = oldName.replace(/\.[a-z]{3}$/g, '.'+newExt);
						$dialog.find('.exportTableFilename').val(newName);
					}
				}).parent('div').addClass('mini');
			},
			buttons: {
				'OK' : function() {
					var filename = $(this).find('.exportTableFilename').val();
					var fileType = $(this).find('.exportTableFiletype').val();
					var lineIds = encodeURIComponent(JSON.encode(selected));
					var ajaxReq = 'action=multiLine_export&customMode='+customMode+'&projID='+project_ID+'&tableName='+activeTable+'&lines='+lineIds+'&fileName='+filename+'&fileType='+fileType;
					AjaxJson(ajaxReq, "depts/prod_actions", retourAjaxMsg, false);
					$(this).dialog('close');
				},
				'Cancel' : function() { $(this).dialog('close'); }
			},
			close: function() { $dialog.dialog('destroy'); $dialog.remove(); }
		});
	});

	// Recherche
	$('#prod_searchTable_value').keyup(function(){
		var val = $(this).val();
		if (val == '') {
			$('.tablesorter tbody tr').show();
			$('#prod_searchTable_Clear').addClass('ui-state-disabled').removeClass('ui-state-highlight');
			return;
		}
		var row = $('#prod_searchTable_row').val();
		$('#prod_searchTable_Clear').removeClass('ui-state-disabled').addClass('ui-state-highlight');
		$('.tablesorter tbody tr').hide();
		if (row == 'all')
			$('td[searchVal*="'+val+'"]').parent('tr').show();
		else
			$('td[rowName="'+row+'"][searchVal*="'+val+'"]').parent('tr').show();
	});

	// Vidage recherche
	$('#prod_searchTable_Clear').click(function(){
		$('.tablesorter tbody tr').show();
		$('#prod_searchTable_value').val('').removeClass('ui-state-error');
		$('#prod_searchTable_Clear').addClass('ui-state-disabled').removeClass('ui-state-highlight');
	});
	$('#prod_searchTable_row').change(function(){
		$('.tablesorter tbody tr').show();
		$('#prod_searchTable_value').val('').keyup();
		$('#prod_searchTable_Clear').addClass('ui-state-disabled').removeClass('ui-state-highlight');
	});

	// Affichage des lignes archivées
	$('#prod_showArchived').click(function(){
		var showArchived = 'false';
		if ($(this).hasClass('ui-state-error'))
			showArchived = 'true';
		AjaxJson('action=showArchived&projID='+project_ID+'&state='+showArchived, "depts/prod_actions", retourAjaxMsg, true);
	});


	// Modification de data
	$("table").off('dblclick', "td:not(.idEntry)");
	$("table").on('dblclick', "td:not(.idEntry)", function(){
		if (editMode) return;
		if ($(this).attr('intouchable') == "yes") return;
		if ($(this).attr('noData') == 'noData') {
			alert('No data in this category.\n\nPlease add an entry with button "Add entry".');
			return;
		}
		editMode = true;
		var dRow  = $(this).attr('rowName');
		var tdIdx = false;
		$(this).parent('tr').find('td').each(function(idx,ui){
			if ($(ui).attr('rowName') == dRow)
				tdIdx = idx;
		});
		if (!tdIdx) { console.log('ERROR: unable to find column index!'); return; }
		var dType = $(this).attr('dataType');
		var dVal  = $(this).attr('realVal');
		$(this).load('modals/prod_specific/editVal.php', {table:activeTable, idProj:project_ID, customMode:customMode, dRow:dRow, rIdx:tdIdx, dType:dType, dVal:dVal});
	});

	// Show / hide de colonne
	$('#prod_columnShowList').toggle(
		function(){ $('#prod_columnList').show(); },
		function(){ $('#prod_columnList').hide(); }
	);

	$('input[name="prod_visibleRows"]').change(function(){
		var row = $(this).val();
		var visible = ($(this).attr('checked')) ? true : false;
		if (visible)
			$('*[rowName="'+row+'"]').show();
		else
			$('*[rowName="'+row+'"]').hide();
	});

	// Réorganisation des colonnes (mode custom only)
	if (customMode == 'true') {
		$('.prod_vRowsLine').each(function(idx, za){ originalPosRows[idx] = $(za).attr('row'); });

		$('#prod_columnList').sortable({
			update: function(){
				$('.prod_vRowsLine').each(function(idx, za){ newPosRows[idx] = $(za).attr('row'); $(za).children('.idxr').html(idx+1); });
				if (JSON.encode(newPosRows) != JSON.encode(originalPosRows)) {
					$('#prod_colListTitle').html('Save?');
					$('#prod_colListPosSaveBtns').show();
				}
				else {
					$('#prod_colListTitle').html('Organize columns');
					$('#prod_colListPosSaveBtns').hide();
				}
			}
		});

		$('#prod_annuleColPos').click(function(){
			$('.arboItem[prodCat="'+activeTable+'"]').click();
		});

		$('#prod_saveColPos').click(function(){
			if (JSON.encode(newPosRows) == JSON.encode(originalPosRows))
				return;
			var ajaxReq = 'action=reorderColumns&tableName='+activeTable+'&projID='+project_ID+'&customMode='+customMode+'&newOrder='+encodeURIComponent(JSON.encode(newPosRows));
			AjaxJson(ajaxReq, 'depts/prod_actions', retourAjaxMsg, true);
		});
	}
	else {
		$('.idxr').hide();
		$('#prod_colListTitle').html('Show/hide columns');
		$('.prod_vRowsLine').children('.curMove').removeClass('curMove').attr('title', '');
	}


});
// FIN document ready


// Update la liste des ID sélectionnées
function updateSelection() {
	selected  = new Array();
	$('tr[selected]').each(function(){
		selected.push($(this).attr('idEntry'));
	});
	if (selected.length != 0)
		$('th.idTable').find('.ui-icon').removeClass('ui-icon-radio-on').addClass('ui-icon-bullet fondError');
	else
		$('th.idTable').find('.ui-icon').addClass('ui-icon-radio-on').removeClass('ui-icon-bullet fondError');
//	console.log(selected);
}


// met à jour la liste des colonnes des relations d'une table (pour l'ajout de colonne)
function feedColumnsSelect (retour, $dialog) {
	if (retour == undefined)
		return false;
	if (retour.error == 'OK') {
		var rows = $.parseJSON(decodeURIComponent(retour.rows));
		$dialog.find('.selectRelCol').parent('div').html('<select class="selectRelCol"></select>');
		$.each(rows, function(i, row){
			var rowDisp = row.replace(/_/, ' ');
			$dialog.find('.selectRelCol').append('<option value="'+row+'">'+rowDisp+'</option>');
		});
		$dialog.find('.selectRelCol').selectmenu({style: 'dropdown', width: 250}).parent('div').addClass('mini');
	}
}



function retourModifLine (retour, lineTD) {
	if (retour == undefined)
		return false;
	if (retour.error == 'OK') {
		editMode = false;
		$('#retourAjax').html(retour.message).removeClass('ui-state-error').addClass('ui-state-highlight').show(transition);
		setTimeout(function(){ $('#retourAjax').fadeOut(transition); }, 1000);
		if (retour.newVal == '**reload**') {
			$('.arboItem[prodCat="'+activeTable+'"]').click(); return;
		}
		if (selected.length <= 1)
			$(lineTD).html(retour.newVal).attr('realVal', encodeURIComponent(retour.realVal)).attr('searchVal', encodeURIComponent(retour.newVal));
		else {
			$.each(selected, function(i,lineId) {
				var theTD = $('table tr[idEntry="'+lineId+'"]').find('td[rowName="'+retour.rowname+'"]');
				$(theTD).html(retour.newVal).attr('realVal', encodeURIComponent(retour.realVal)).attr('searchVal', encodeURIComponent(retour.newVal));
			});
			$('.idTable').click();
		}
	}
	else {
		$('#retourAjax').html(retour.error+' : '+retour.message).addClass('ui-state-error').removeClass('ui-state-highlight').show(transition);
		setTimeout(function(){ $('#retourAjax').fadeOut(transition); }, 10000);
	}
}
