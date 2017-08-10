<?php

$tStart = microtime(true);
$customMode = false;
$l = new Liste();
switch ($table) {
	case 'saam_sequences':
		$tableName = mb_strtoupper(L_SEQUENCES, 'UTF-8');
		$realTable = $table;
		$l->addFiltre(Sequences::SEQUENCE_ID_PROJECT, "=", $idProj);
		if ($_SESSION['prodHideArchived'][$idProj] === true)
			$l->addFiltre(Sequences::SEQUENCE_ARCHIVE, "=", '0');
		$intouchables = Array('id', 'position', 'ID_creator', 'ID_project', 'update', 'updated_by', 'progress', 'archive');
		$invisibles   = Array('ID_project', 'position', 'archive');
		break;
	case 'saam_shots':
		$tableName = mb_strtoupper(L_SHOTS, 'UTF-8');
		$realTable = $table;
		$l->addFiltre(Shots::SHOT_ID_PROJECT, "=", $idProj);
		if ($_SESSION['prodHideArchived'][$idProj] === true)
			$l->addFiltre(Shots::SHOT_ARCHIVE, "=", '0');
		$intouchables = Array('id', 'position', 'ID_scene', 'ID_creator', 'ID_project', 'update', 'updated_by', 'progress', 'archive');
		$invisibles   = Array('ID_project', 'position', 'archive');
		break;
	case 'saam_scenes':
		$tableName = mb_strtoupper(L_SCENES, 'UTF-8');
		$realTable = $table;
		$l->addFiltre(Scenes::ID_PROJECT, "=", $idProj);
		$l->addFiltre(Scenes::MASTER, "=", '0');
		if ($_SESSION['prodHideArchived'][$idProj] === true)
			$l->addFiltre(Scenes::ARCHIVE, "=", '0');
		$intouchables = Array('id', 'ID_project', 'ID_creator', 'sequences', 'shots', 'derivatives', 'update', 'updated_by', 'progress', 'archive');
		$invisibles   = Array('ID_project', 'master', 'nb_frames', 'fps', 'hide', 'archive');
		break;
	case 'saam_derivatives':
		$table = TABLE_SCENES;
		$tableName = mb_strtoupper(L_DERIVATIVES, 'UTF-8');
		$realTable = $table;
		$l->addFiltre(Scenes::ID_PROJECT, "=", $idProj);
		$l->addFiltre(Scenes::MASTER, "!=", '0');
		if ($_SESSION['prodHideArchived'][$idProj] === true)
			$l->addFiltre(Scenes::ARCHIVE, "=", '0');
		$intouchables = Array('id', 'ID_project', 'ID_creator', 'master', 'sequences', 'shots', 'assets', 'assets',  'update', 'updated_by', 'progress', 'archive');
		$invisibles   = Array('ID_project', 'derivatives', 'nb_frames', 'fps', 'hide', 'archive');
		break;
	case 'saam_cameras':
		$tableName = mb_strtoupper(L_CAMERAS, 'UTF-8');
		$realTable = $table;
		$l->addFiltre(Scenes::ID_PROJECT, "=", $idProj);
		if ($_SESSION['prodHideArchived'][$idProj] === true)
			$l->addFiltre(Scenes::ARCHIVE, "=", '0');
		$intouchables = Array('id', 'ID_creator', 'ID_sequence', 'ID_shot', 'ID_scene', 'update', 'updated_by', 'archive');
		$invisibles   = Array('ID_project', 'archive');
		break;
	case 'saam_assets':
		$tableName = mb_strtoupper(L_ASSETS, 'UTF-8');
		$realTable = $table;
		$l->addFiltre(Assets::ASSET_ID_PROJECTS, "LIKE", '%"'.$idProj.'"%');
		if ($_SESSION['prodHideArchived'][$idProj] === true)
			$l->addFiltre(Assets::ASSET_ARCHIVE, "=", '0');
		$intouchables = Array('id', 'ID_projects', 'ID_creator', 'update', 'updated_by', 'progress', 'archive');
		$invisibles   = Array('ID_projects', 'archive');
		break;
	case 'saam_tasks':
		$tableName = mb_strtoupper(L_TASKS, 'UTF-8');
		$realTable = $table;
		$l->addFiltre(Tasks::ID_PROJECT_TASK, "=", $idProj);
		if ($_SESSION['prodHideArchived'][$idProj] === true)
			$l->addFiltre(Tasks::HIDE_TASK, "=", '0');
		$intouchables = Array('id', 'ID_project', 'ID_creator');
		$invisibles   = Array();
		break;
	case 'saam_dailies':
		$tableName = mb_strtoupper(L_DAILIES, 'UTF-8');
		$realTable = $table;
		$l->addFiltre(Dailies::DAILIES_PROJECT_ID, "=", $idProj);
		$intouchables = Array('id', 'ID_projects', 'date', 'groupe', 'type', 'corresp');
		$invisibles   = Array();
		break;
	case 'saam_users':
		$tableName = mb_strtoupper(L_USERS, 'UTF-8');
		$realTable = $table;
		$l->addFiltre(Users::USERS_MY_PROJECTS, "LIKE", '%"'.$idProj.'"%');
//		if ($_SESSION['prodHideArchived'][$idProj] === true)
//			$l->addFiltre(Assets::ASSET_ARCHIVE, "=", '0');
		$intouchables = Array('id', 'lang', 'theme', 'receiveMails', 'ID_creator', 'my_projects', 'my_dpts', 'my_sequences', 'my_shots', 'my_msgs', 'my_assets', 'my_tags', 'date_inscription', 'date_last_connexion', 'date_last_action', 'archive');
		$invisibles   = Array('passwd', 'my_projects', 'my_dpts', 'my_sequences', 'my_shots', 'my_msgs', 'my_assets');
		break;
	default:
		$tableName = mb_strtoupper(preg_replace('/_/', ' ', $table), 'UTF-8');
		$realTable = TABLE_PROD_CUSTOM;
		$customMode = true;
		$intouchables = Array('id');
		$invisibles   = Array();
		break;
}
try {
	if ($customMode) {
		$ct = new CustomTable($table, $idProj);
		$rowsTable	  = $ct->getRows();
		$entriesTable = $ct->getValues($_SESSION['prodHideArchived'][$idProj]);
	}
	else {
		$rel = new RelCheck($table);
		$rowsTable	  = $l->getRows($realTable);
		$entriesTable = $l->getListe($realTable);
	}
	$hidden = Array();
}
catch(Exception $e) {
	die('<div class="big gras colorErreur pad10">Table "'.$realTable.'" not found!</div>&nbsp;&nbsp;&nbsp;'. $e->getMessage().'</div>');
}

// Construction des header colonne
$theadLine = ''; $rowCount = 0;
foreach ($rowsTable as $row) {
	if (in_array($row, $invisibles))
		continue;
	$rowCount++;
	if ($customMode) {
		$star	 = ($ct->hasRelation($row)) ? '<span class="big colorBtnFake">*</span> ' : '';
		$relInfo = ($ct->getRelInfo($row) == '') ? '' : ' | Related to: '.$ct->getRelInfo($row).'';
	}
	else {
		$star = ($rel->has_relation($row)) ? '<span class="big colorBtnFake">*</span> ' : '';
		$relInfo = ($rel->get_link_info($row) == '') ? '' : ' | Related to: '.$rel->get_link_info($row).'';
	}
	if ($row == 'hide') $star = '<div class="inline mid ui-state-highlight ui-corner-all"><span class="ui-icon ui-icon-lightbulb"></span></div>';
	if ($row == 'lock') $star = '<div class="inline mid ui-state-highlight ui-corner-all"><span class="ui-icon ui-icon-locked"></span></div>';
	if ($row == 'archive') $star = '<div class="inline mid ui-state-highlight ui-corner-all"><span class="ui-icon ui-icon-trash"></span></div>';
	$titleRow = ($customMode) ?  "$realTable.$table.json($row)" : "$realTable.$row";
	$titleRow = ($row == 'id') ? 'Select all / Deselect all' : $titleRow.$relInfo;
	$style = ($row == 'id') ? '{sorter: false} idTable ui-state-highlight noBorder doigt' : '';
	$rowDisp = preg_replace('/^ID_/', '', $row);
	$rowDisp = preg_replace('/^CC_/', '', $rowDisp);
	$rowDisp = preg_replace('/_/', ' ', $rowDisp);
	$rowDisp = ($row == 'id') ? '<span class="ui-corner-all ui-icon ui-icon-radio-on"></span>' : $rowDisp;
	$rowDisp = ($row == 'assets' && $tableName == mb_strtoupper(L_SCENES, 'UTF-8')) ?
			'assets in master scene <span class="ui-state-disabled">_____________________________________________</span>' : $rowDisp;
	$rowDisp = ($row == 'assets' && $tableName == mb_strtoupper(L_DERIVATIVES, 'UTF-8')) ?
			'assets in derivative <span class="ui-state-disabled">_____________________________________________</span>' : $rowDisp;
	$rowDisp = (preg_match('/^CC_/', $row) || ($customMode && $row != 'id' && $row != 'status')) ? $rowDisp.' &nbsp;<div class="inline mid ui-state-highlight ui-corner-all"><span class="ui-icon ui-icon-trash deleteCustomRow"></span></div>' : $rowDisp;
	$theadLine .= "<th class='$style' title='$titleRow' rowName='$row' rowDisp='$rowDisp'>$star $rowDisp</th>\n";
}

// Construction de la table
$trList = '';
if (is_array($entriesTable) && count($entriesTable) >= 1) {
	foreach($entriesTable as $entry) {
		$classTr = (
				@$entry['status'] == (count($_SESSION['CONFIG']['DEFAULT_STATUS'])-2) ||								// Status VALID
				@$entry['CC_status'] == '["VALID"]'
				) ? ' fondAchieved' : '';
		$classTr = (
				@$entry['status'] == (count($_SESSION['CONFIG']['DEFAULT_STATUS'])-1) ||								// Status Disabled
				@$entry['CC_status'] == '["Disabled"]'
				) ? ' fondWarning' : $classTr;
		$classTr = (@$entry['progress'] == 100) ? ' fondAchieved' : $classTr;											 // progress 100%
		$classTr = (@$entry['archive']  == 1)	? ' fondWarning'  : $classTr;											 // Archived

		$trList .= '<tr class="lineVal '.$classTr.'" idEntry="'.@$entry['id'].'">';

		$masterAssets = false;
		if ($tableName == mb_strtoupper(L_DERIVATIVES, 'UTF-8')) {
			try {
				$scM = new Scenes((int)$entry['master']);
				$masterAssets = $scM->getSceneAssets(true);
			}
			catch(Exception $e) {  }
		}

		$filledRowCount = 0;
		if (count($entry) >= 1) {
			foreach ($entry as $key => $val) {
				if (in_array($key, $invisibles))
					continue;
				if (!in_array($key, $rowsTable))
					continue;
				$style	 = ($key == 'id') ? 'class="idEntry doigt" style="width:25px;"' : '';
				if ($customMode) {
					$dataType = $ct->getRowType($key);
					$valDisp  = $ct->getRelVal($key, $val);
					$titleTD  = ($ct->hasRelation($key)) ? 'Stored value ('.$ct->getRelInfo($key).') = '.preg_replace('/"/', '\'', $valDisp[0]).' ('.$dataType.')' : '('.$dataType.')';		// for debug purpose
					$star	  = ($ct->hasRelation($key)) ? '<span class="gros colorBtnFake">*</span> ' : '';
				}
				else {
					$dataType = $rel->get_rowType($key, $val);
					$valDisp  = $rel->get_rel_val($key, $val);
					$titleTD  = ($rel->has_relation($key)) ? 'Stored value ('.$rel->get_link_info($key).') = '.preg_replace('/"/', '\'', $valDisp[0]).' ('.$dataType.')' : '('.$dataType.')';		// for debug purpose
					$star	  = ($rel->has_relation($key)) ? '<span class="gros colorBtnFake">*</span> ' : '';
					$style	  = ($rel->get_link_info($key) == 'saam_config->default_status' && $val == '["Disabled"]') ? 'class="fondWarning" ' : $style;
					$style	  = ($rel->get_link_info($key) == 'saam_config->default_status' && $val == '["VALID"]')	  ? 'class="fondAchieved" ' : $style;
				}
				$filledRowCount++;
				$touch	 = (in_array($key, $intouchables)) ? 'intouchable="yes"' : '';
				$realVal = urlencode($valDisp[0]);

				if ($dataType == 'calendar') {
					$valCal  = SQLdateConvert($valDisp[1]);
					$tdDispClass = '';
					if (compare_dates($valDisp[1], date(DATE_ATOM), 'date2SUPdate1') && ($key == 'deadline' || $key == 'end'))
						$tdDispClass = 'class="colorErreur"';
					if ($valCal == "?") {
						$realVal = urlencode(date(DATE_FORMAT) . ' 00:00:00');
						$tdDispClass = 'class="ui-state-disabled"';
					}
					$tdSearch = $valCal;
					$tdDisp = "<span $tdDispClass>$valCal</span>";
				}
				elseif ($dataType == 'boolean' ) {
					$tdSearch =
					$tdDisp	  = ($valDisp[1] == '1') ? L_BTN_YES : L_BTN_NO;
				}
				elseif ($dataType == 'percentage') {
					$tdSearch = $valDisp[1];
					$tdDisp   = $valDisp[1] . ' %';
				}
				elseif ($dataType == 'tags' || $dataType == 'menu_rel_multiple') {
					$tdDisp = ''; $tdSearch = '';
					if (is_array($valDisp[1])) {
						if ($tableName == mb_strtoupper(L_DERIVATIVES, 'UTF-8') && $key == 'assets' ) {
							if (is_array($masterAssets)) {
								foreach ($masterAssets as $aID) {
									$ast = new Assets((int)$idProj, (int)$aID);
									$item = $ast->getName();
									$tdSearch .= $item.' ';
									$styleItem = (in_array($item, $valDisp[1])) ? 'ui-state-error' : 'ui-state-highlight' ;
									$titleItem = (in_array($item, $valDisp[1])) ? 'Asset EXCLUDED from this derivative' : 'Asset INCLUDED in the master scene' ;
									$tdDisp   .= '<div class="inline '.$styleItem.' ui-corner-all" style="padding:1px 4px;" title="'.$titleItem.'">'. $item .'</div> ';
								}
							}
							else {
								foreach ($valDisp[1] as $item) {
									$tdSearch .= $item.' ';
									$tdDisp   .= '<div class="inline ui-state-error ui-corner-all" style="padding:1px 4px;">'. $item .'</div> ';
								}
							}
						}
						else {
							foreach ($valDisp[1] as $item) {
								$tdSearch .= $item.' ';
								$tdDisp   .= '<div class="inline ui-state-highlight ui-corner-all" style="padding:1px 4px;">'. $item .'</div> ';
							}
						}
					}
				}
				else {
					if ((int)$valDisp[0] >= 1300000000) // Si c'est un timestamp on le converti en date
						$valDisp[1] = date("<b>".DATE_FORMAT."</b>\n H:i", $valDisp[0]);
					if ($key == 'my_tags') {
						$mytags = json_decode(urldecode($val));
						if (is_array($mytags)) {
							$valDisp[1] = '';
							foreach ($mytags as $tag) {
								$valDisp[1] .= '<div class="inline ui-state-highlight ui-corner-all" style="padding:1px 4px;">'.$tag.'</div>';
							}
						}
						else $valDisp[1] = '<i class="ui-state-disabled">No tag</i>';
					}
					if ($key == 'version')
						$valDisp[1] = sprintf('%03d', $val);

					$tdSearch = $valDisp[1];
					$tdDisp   = $star. nl2br($valDisp[1]);
				}


				$trList .= "<td $style $touch dataType='$dataType' rowName='$key' "
								.'realVal="'.$realVal.'" searchVal="'.urlencode($tdSearch).'" '
//								.'title="'.$titleTD.'"'		// for debug purpose
							.'>'
								.$tdDisp
							.'</td>';
			}
		}
		else {
			for($i=1; $i<=$rowCount; $i++)
				$trList .= "<td realVal='0' noData='noData' dataType='text'><i class='colorDiscret'>no data</i></td>";
		}
		if ($filledRowCount != $rowCount) {
			$missingRows = $rowCount - $filledRowCount;
			for($i=1; $i<=$missingRows; $i++)
				$trList .= "<td realVal='0' dataType='text'></td>";
		}
		$trList .= '</tr>';
	}
}
else {
	$trList .= "<tr>";
	for($i=1; $i<=$rowCount; $i++)
		$trList .= "<td realVal='0' noData='noData' dataType='text'><i class='colorDiscret'>no data</i></td>";
	$trList .= "</tr>";
}

$lct = new Liste();
$cTablesList = $lct->getRows(TABLE_PROD_CUSTOM);
?>
<link type="text/css" href="css/tableSorter.css" rel="stylesheet" />
<link type="text/css" href="css/tablesorter.pager.css" rel="stylesheet" />

<script type="text/javascript" src="js/jquery.metadata.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.pager.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>

<script>
	var customMode  = "<?php echo ($customMode) ? 'true' : 'false'; ?>";
	var activeTable = "<?php echo $table; ?>";
	var originalPosRows = [];
	var newPosRows = [];

	$(function(){

		var maxProdHeight = $('#stageProd').height() - 28;
		$('#contentProd').slimScroll({
			position: 'right',
			height: maxProdHeight+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		}).css('overflow-x', 'auto');

		try {
			$("table.tablesorter").tablesorter({textExtraction: sortByAttr, widthFixed: false, sortList: [[1,0]]})
								  .tablesorterPager({size: 50, container: $("#pager"), positionFixed:false});
		}
		catch(err) {
			alert('Some error(s) occured in this table\'s values! Please call developers to check it out.\n\nError: '+err);
			console.log(err);
		}

		$('#contentProd').scroll(function() {
			$("#fixedTH").width($('table.tablesorter').width());
			var thWidthList = [];
			$("table.tablesorter").find('th').each(function(){ thWidthList.push($(this).width()); });
			$("#fixedTH").find('th').each(function(i,e){
				$(this).width(thWidthList[i]);
			});
			var scpos = $(this).scrollTop();
			if (scpos < 25)
				$("#tableFixedHead").hide();
			else
				$("#tableFixedHead").show();
		});
	});

	function sortByAttr(node) {
		var realVal  = $(node).attr('realVal');
		var dataType = $(node).attr('dataType');
		if (realVal == '')
			return 'zzzzzzzzzzzzzzzzzz';
		if (realVal == 0 && dataType != 'int' && dataType != 'boolean')
			return '999999';
		if (dataType == 'calendar') {
			try {
				var d = decodeURIComponent(realVal.replace(/\+/g, ' ')).match(/\d+/g);
				return +new Date(d[0], d[1] - 1, d[2], d[3], d[4], d[5]);
			}
			catch(err) {
				console.log('Unable to convert value "'+realVal+'" into a date()! ');
				return +new Date();
			}
		}
		return decodeURIComponent(realVal.replace(/\+/g, ' '));
	}
</script>
<script type="text/javascript" src="ajax/depts/dept_prod.js"></script>

<div class="ui-state-active ui-corner-all w150 hide" id="prod_columnList">
	<div class="floatR pico hide" id="prod_colListPosSaveBtns">
		<button class="bouton ui-state-highlight" id="prod_saveColPos"><span class="ui-icon ui-icon-check"></span></button>
		<button class="bouton ui-state-error"  id="prod_annuleColPos"><span class="ui-icon ui-icon-cancel"></span></button>
	</div>
	<div class="center marge5bot" id="prod_colListTitle">Organize columns</div>
	<?php $idx = 0;
	foreach($rowsTable as $row) :
		if ($row == 'id') continue;
		if (in_array($row, $invisibles)) continue;
		$idx++;
		$checked = (in_array($row, $hidden)) ? '' : 'checked="checked"';
		$rowDisp = preg_replace('/^ID_/', '', $row);
		$rowDisp = preg_replace('/_/', ' ', $rowDisp); ?>
		<div class="prod_vRowsLine" row="<?php echo $row; ?>">
			<div class="inline mid colorDark idxr">
				<?php echo $idx; ?>
			</div>
			<div class="inline mid" title="Toggle show/hide column">
				<input class="doigt" type="checkbox" <?php echo $checked; ?> name="prod_visibleRows" value="<?php echo $row; ?>" />
			</div>
			<div class="inline mid curMove" title="Drag & drop to reorganize columns order">
				<?php echo $rowDisp; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<div class="gros bordBottom bordProd" id="headProd">
	<div class="floatR pico">
		<div class="bouton ui-state-highlight marge10r" id="prod_addColumn">
			<div class="inline mid"><span class="ui-icon ui-icon-plusthick"></span></div>
			<div class="inline mid" style="font-size:4em;">Add column</div>
		</div>
		<div class="inline mid" id="prod_columnShowList">
			<button class="bouton ui-state-highlight" title="Show/Hide columns"><span class="ui-icon ui-icon-gear"></span></button>
		</div>
		&nbsp;&nbsp;
	</div>
	<div class="noPad">
		<div class="floatL fondHigh gros padV10 marge10r" style="height: 23px; padding-top: 2px;">
			<?php echo $tableName; ?>
		</div>

		<div class="inline mid pico marge10r">
			<div class="bouton ui-state-highlight" id="prod_addEntry">
				<div class="inline mid"><span class="ui-icon ui-icon-plus"></span></div>
				<div class="inline mid" style="font-size:4em;">Add entry&nbsp;</div>
			</div>
		</div>

		<div class="Vseparator fondBlanc marge10r" style="height: 22px;"></div>

		<div class="inline mid pico marge10r">
			<button class="bouton ui-state-highlight marge10r" title="Refresh table" id="prod_refreshTable"><span class="ui-icon ui-icon-refresh"></span></button>
			<button class="bouton ui-state-highlight" title="Export table (csv, xml, pdf)" id="prod_exportTable"><span class="ui-icon ui-icon-extlink"></span></button>
		</div>

		<div class="Vseparator fondBlanc marge10r" style="height: 22px;"></div>

		<div class="inline mid ui-state-disabled mini"><?php echo count($entriesTable) ?> results.</div>

		<div class="inline mid micro marge10r">
			<select title="filter by" id="prod_searchTable_row">
				<option selected value="all">Filter by</option>
				<?php foreach($rowsTable as $row) {
					if ($row == 'id') continue;
					if (in_array($row, $invisibles)) continue;
					$rowDisp = preg_replace('/^ID_/', '', $row);
					$rowDisp = preg_replace('/_/', ' ', $rowDisp);
					echo "<option value='$row'>$rowDisp</option>";
				} ?>
			</select> >
			<div class="inline mid gros">
				<input class="ui-state-highlight ui-corner-all pad3 margeTop1" type="text" style="width:65px;" value="" id="prod_searchTable_value" />
			</div>
			<div class="inline mid ui-state-disabled ui-corner-all margeTop1 doigt" style="padding:1px;" id="prod_searchTable_Clear">
				<span class="ui-icon ui-icon-cancel"></span>
			</div>
		</div>

		<div class="Vseparator fondBlanc marge10r" style="height: 22px;"></div>

		<div class="inline mid micro marge10r pager" id="pager">
			<form>
				<div class="inline mid ui-state-highlight ui-corner-all doigt first" title="first page" style="padding:1px;">
					<span class="ui-icon ui-icon-arrowthickstop-1-w"></span>
				</div><div class="inline mid ui-state-highlight ui-corner-all doigt prev" title="previous page" style="padding:1px;">
					<span class="ui-icon ui-icon-arrowthick-1-w"></span>
				</div>
				<input type="text" class="noBG noBorder fleche pad3 margeTop1 pagedisplay" title="current page" style="width:20px;" />
				<div class="inline mid ui-state-highlight ui-corner-all doigt next" title="next page" style="padding:1px;">
					<span class="ui-icon ui-icon-arrowthick-1-e"></span>
				</div><div class="inline mid ui-state-highlight ui-corner-all doigt last" title="last page" style="padding:1px;">
					<span class="ui-icon ui-icon-arrowthickstop-1-e"></span>
				</div>
				<select class="noBorder fondBlanc pagesize" id="pageSizeSel">
					<!--<option selected="selected" value="5">5</option>-->
					<option value="20">20</option>
					<option selected="selected" value="50">50</option>
					<option value="100">100</option>
					<option value="5000">all</option>
				</select>
			</form>
		</div>

		<div class="Vseparator fondBlanc marge10r" style="height: 22px;"></div>

		<div class="inline mid pico marge10r">
			<?php $stateArchive = ($_SESSION['prodHideArchived'][$idProj] === true) ? 'ui-state-highlight' : 'ui-state-error'; ?>
			<button class="bouton <?php echo $stateArchive; ?>" title="Show archived" id="prod_showArchived"><span class="ui-icon ui-icon-lightbulb"></span></button>
		</div>
	</div>
</div>

<div id="contentProd">
	<div id="tableFixedHead">
		<table id="fixedTH">
			<thead>
				<tr>
					<?php echo $theadLine; ?>
				</tr>
			</thead>
		</table>
	</div>

	<table class="tablesorter">
		<thead>
			<tr>
				<?php echo $theadLine; ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<?php echo $theadLine; ?>
			</tr>
		</tfoot>
		<tbody>
			<?php echo $trList; ?>
		</tbody>
	</table>
	<div class="petit marge10l margeTop10">
		<span class="big colorBtnFake">*</span> data linked to another table <i>(hover the column header to see which one)</i>.
	</div>
	<div class="marge10l mini ui-state-disabled margeTop10">
		<?php
		$deltaT = number_format(microtime(true) - $tStart, 3);
		echo "Processed in <b>$deltaT</b> s." ;
		?>
	</div>
	<p>.</p>



	<div class="fondProdInv ui-corner-all w100" id="contextMenuProd">
		<?php if (!$customMode && $table != 'saam_users'): ?>
			<div class="ui-state-highlight noBorder ui-corner-top ctxMenuProd_Btn" action="show">
				<div class="inline mid"><span class="ui-icon ui-icon-lightbulb"></span></div>
				<div class="inline mid">Show</div>
			</div>
			<div class="ui-state-highlight noBorder ui-corner-bottom ctxMenuProd_Btn" action="hide">
				<div class="inline mid"><span class="ui-icon ui-icon-lightbulb"></span></div>
				<div class="inline mid">Hide</div>
			</div>
		<?php endif;
			if ($table != 'saam_users'): ?>
		<div class="ui-state-highlight noBorder ui-corner-top margeTop1 ctxMenuProd_Btn" action="delete">
			<div class="inline mid"><span class="ui-icon ui-icon-trash"></span></div>
			<div class="inline mid">Delete</div>
		</div>
		<?php endif;
			if ($_SESSION['prodHideArchived'][$idProj] === false): ?>
			<div class="ui-state-highlight noBorder ui-corner-bottom ctxMenuProd_Btn" action="restore">
				<div class="inline mid"><span class="ui-icon ui-icon-refresh"></span></div>
				<div class="inline mid">Restore</div>
			</div>
		<?php endif; ?>
		<div class="ui-state-highlight noBorder ui-corner-all margeTop1 ctxMenuProd_Btn" action="export">
			<div class="inline mid"><span class="ui-icon ui-icon-extlink"></span></div>
			<div class="inline mid">Export</div>
		</div>
	</div>



	<div class="hide" title="Add entry to <?php echo $tableName; ?>" id="modalAddEntry">
		<?php
		foreach ($rowsTable as $row) :
			if (in_array($row, $invisibles) || in_array($row, $intouchables))
				continue;
			try {
				if ($customMode) {
					$dataType = $ct->getRowType($row);
					$poss	  = $ct->getPossibilities($row, $idProj);
					$relName  = $ct->getRelInfo($row);
				}
				else {
					$dataType = $rel->get_rowType($row, '0');
					$poss	  = $rel->get_all_possibilities($row, $idProj);
					$relName  = $rel->get_link_info($row);
				}
			}
			catch(Exception $e) { echo '<span class="colorErreur gras">'.$e->getMessage().'</span>'; } ?>
			<div class="inline mid margeTop1 w100 colorBtnFake">
				<?php echo $row; ?>
			</div>
			<div class="inline mid margeTop1">
			<?php if ($dataType == 'menu_rel'): ?>
				<select class="addEntryVal" theRow="<?php echo $row; ?>">
					<option value="999" class="ui-state-disabled"><?php echo $relName; ?></option>
					<?php foreach($poss as $pId => $p):
						$selected = ($pId == 0) ? 'selected="selected"' : "" ; ?>
						<option value="<?php echo $pId; ?>" <?php echo $selected; ?>><?php echo $p; ?></option>
					<?php endforeach; ?>
				</select>
			<?php elseif ($dataType == 'tags' || $dataType == 'menu_rel_multiple'): ?>
				<select class="addEntryVal" multiple="multiple" theRow="<?php echo $row; ?>">
					<?php foreach($poss as $pId => $p): ?>
						<option value="<?php echo $pId; ?>"><?php echo $p; ?></option>
					<?php endforeach; ?>
				</select>
			<?php elseif ($dataType == 'calendar'): ?>
				<input type="text" class="ui-corner-all noBorder pad3 fondSect3 addEntryVal calendarVal" theRow="<?php echo $row; ?>" size="30" />
			<?php elseif ($dataType == 'boolean'): ?>
					<select class="addEntryVal" theRow="<?php echo $row; ?>">
						<option value="" selected="selected"><?php echo L_NOTHING; ?></option>
						<option value="0"><?php echo L_BTN_NO;  ?> (0)</option>
						<option value="1"><?php echo L_BTN_YES; ?> (1)</option>
					</select>
			<?php elseif ($dataType == 'timecode'): ?>
				<div class="addEntryVal_TC" theRow="<?php echo $row; ?>">
					<input type="text" class="ui-corner-all noBorder pad3 fondSect3 numericInput" size="2" maxlength="2" /> :
					<input type="text" class="ui-corner-all noBorder pad3 fondSect3 numericInput" size="2" maxlength="2" /> :
					<input type="text" class="ui-corner-all noBorder pad3 fondSect3 numericInput" size="2" maxlength="2" /> :
					<input type="text" class="ui-corner-all noBorder pad3 fondSect3 numericInput" size="2" maxlength="2" />
				</div>
			<?php else: ?>
				<input type="text" class="ui-corner-all noBorder pad3 fondSect3 addEntryVal" theRow="<?php echo $row; ?>" size="30" />
			<?php endif; ?>
			</div>
		<br />
		<?php endforeach; ?>
	</div>



	<div class="hide" title="Add a column to table <?php echo $tableName; ?>" id="modalAddColumn">
		<div class="margeTop5 show colNameLine">
			<div class="inline mid w150 colorBtnFake">
				Column name
			</div>
			<div class="inline mid">
				<input type="text" class="ui-corner-all noBorder pad3 fondSect3 addColName" size="35" />
			</div>
		</div>
		<div class="margeTop5 show typeSelectLine">
			<div class="inline mid w150 colorBtnFake">
				Column type
			</div>
			<div class="inline mid">
				<select class="selectType">
					<option value="text">Text</option>
					<option value="int">Integer</option>
					<option value="float">Float</option>
					<option value="datetime">Date</option>
					<option value="boolean">Boolean</option>
					<option value="timecode">TimeCode</option>
					<option value="relation">Related to another table</option>
				</select>
			</div>
		</div>
		<div class="margeTop5 hide relation_GRP_SelectLine">
			<div class="inline mid w150 colorBtnFake">
				Column related to
			</div>
			<div class="inline mid ui-state-error-text noBG noBorder">
				<select class="selectRelGrp">
					<option value="predefined" selected="selected">Predefined</option>
					<option value="custom">Custom</option>
				</select>
			</div>
			<div class="inline mid">
				<div class="grpRel grpRel_predefined">
					<select class="selectRelTable_predefined">
						<option value="*saam_config.global_tags.json>json.global_tags">Global Tags</option>
						<option value="*saam_config.default_status.json>json.default_status">Global Status</option>
						<option value="*" class="ui-state-disabled">---</option>
						<option value="*saam_users.id.direct.pseudo">One User</option>
						<option value="*saam_users.id.json>val.pseudo">Mulitple Users</option>
						<option value="*" class="ui-state-disabled">---</option>
						<option value="*saam_sequences.id.direct.title">One Sequence</option>
						<option value="*saam_sequences.id.json>val.title">Multiple Sequences</option>
						<option value="*saam_shots.id.direct.title">One Shot</option>
						<option value="*saam_shots.id.json>val.title">Multiple Shots</option>
						<!--<option value="*saam_scenes.id.direct.title">One Scene</option>
						<option value="*saam_scenes.id.json>val.title">Multiple Scenes</option>-->
						<option value="*saam_assets.id.direct.filename">One Asset</option>
						<option value="*saam_assets.id.json>val.filename">Multiple Assets</option>
					</select>
				</div>
				<div class="grpRel grpRel_custom hide">
					<select class="selectRelTable_custom">
						<option value="*" class="ui-state-disabled">---</option>
						<?php foreach($cTablesList as $cTable):
							if ($cTable == 'id' || $cTable == 'ID_project' || $cTable == 'deleted')
								continue;
							$ct = new CustomTable($cTable, $idProj);
							if ($ct->getRows() === false)
								continue; ?>
							<option value="*CT_<?php echo $cTable; ?>.id"><?php echo preg_replace('/_/', ' ', $cTable); ?></option>
						<?php endforeach; ?>
						<option value="*" class="ui-state-disabled">---</option>
						<option value="*saam_sequences.id">SaAM <?php echo L_SEQUENCES; ?></option>
						<option value="*saam_shots.id">SaAM <?php echo L_SHOTS; ?></option>
						<!--<option value="*saam_scenes.id">SaAM <?php // echo L_SCENES; ?></option>-->
						<option value="*saam_assets.filename">SaAM <?php echo L_ASSETS; ?></option>
						<option value="*saam_users.id">SaAM <?php echo L_USERS; ?></option>
					</select>
				</div>
			</div>
		</div>
		<div class="margeTop5 hide relation_COL_SelectLine">
			<div class="inline mid w200 colorDiscret">
				Relation return column
			</div>
			<div class="inline mid">
				<select class="selectRelCol"></select>
			</div>
			<br />
			<div class="inline mid w200 colorDiscret">
				Relation type
			</div>
			<div class="inline mid">
				<select class="selectRelType">
					<option value="direct">One value</option>
					<!--<option value="val>json">One value from list *</option>-->
					<option value="json>val">List of values</option>
					<!--<option value="json>json">List of values from list *</option>-->
				</select>
				<br />
				<!--<span class="ui-state-disabled">* "from list" => the column must have json data</span>-->
			</div>
		</div>
		<div class="margeTop5 show defaultValLine">
			<div class="inline mid w150 colorDiscret">
				Default <span class="petit defaultValType"><i>text</i></span> value
			</div>
			<div class="inline mid">
				<input type="text" class="ui-corner-all noBorder pad3 fondSect3 defaultValinput defaultVal-text" size="35" />
				<input type="text" class="ui-corner-all noBorder pad3 fondSect3 defaultValinput hide defaultVal-datetime" size="35" />
				<div class="defaultValinput hide mini defaultVal-boolean">
					<select class="dvBsel">
						<option value=""><?php echo L_NOTHING; ?></option>
						<option value="0"><?php echo L_BTN_NO;  ?> (0)</option>
						<option value="1"><?php echo L_BTN_YES; ?> (1)</option>
					</select>
				</div>
				<div class="defaultValinput hide defaultVal-timecode">
					<input type="text" class="ui-corner-all noBorder pad3 fondSect3 numericInput default-TC-H" size="2" maxlength="2" /> :
					<input type="text" class="ui-corner-all noBorder pad3 fondSect3 numericInput default-TC-M" size="2" maxlength="2" /> :
					<input type="text" class="ui-corner-all noBorder pad3 fondSect3 numericInput default-TC-S" size="2" maxlength="2" /> :
					<input type="text" class="ui-corner-all noBorder pad3 fondSect3 numericInput default-TC-I" size="2" maxlength="2" />
				</div>
			</div>
		</div>
	</div>


	<div class="hide" title="Export table <?php echo $tableName; ?>" id="modalExportTable">
		<div class="margeTop5">
			<div class="inline mid w150 colorBtnFake">
				Export type
			</div>
			<div class="inline mid">
				<select class="exportTableFiletype">
					<option value="csv" selected="selected">CSV (Semicolon-separated values)</option>
					<option value="xml">XML (Extensible Markup Language)</option>
					<option value="pdf">PDF (Portable Document Format)</option>
					<option value="txt">Plain text file</option>
				</select>
			</div>
		</div>
		<div class="margeTop5">
			<div class="inline mid w150 colorBtnFake">
				Download filename
			</div>
			<div class="inline mid">
				<input type="text" class="ui-corner-all noBorder pad3 fondSect3 exportTableFilename" size="33" value="<?php echo $table; ?>.csv" />
			</div>
		</div>
	</div>


</div>
