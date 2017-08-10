<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );

extract($_POST);

$retour['error']	= 'error';
$retour['message']	= 'action undefined';

try {

	$ACL = new ACL($_SESSION['user']);
	if (!$ACL->check('VIEW_DEPT_PROD'))
		die ('{"error":"error", "message":"actions prod : Access denied."}');

	/*************************************************************************** CATÉGORIES ****************************/

	// Ajout d'une custom table
	if ($action == 'addCustomCat') {
		try {
			$ct = new CustomTable($newCat, $idProj);
			if ($ct->addTable()) {
				$retour['error'] = 'OK';
				$retour['message'] = 'Custom category added.';
				$_SESSION['lastVisitedTable'][$idProj] = $newCat;
			}
			else $retour['message'] = 'This custom category already exist!';
		}
		catch (Exception $e) {
			$retour['message'] = $e->getMessage();
		}
	}

	// Renommage de catégorie
	if ($action == 'renameCustomCat') {
		try {
			$ct = new CustomTable(urldecode($cat), $idProj);
			if ($ct->renameTable(urldecode($newName))) {
				$retour['error'] = 'OK';
				$retour['message'] = 'Custom category renamed.';
				$_SESSION['lastVisitedTable'][$idProj] = 'saam_sequences';
			}
			else $retour['message'] = 'Impossible to rename category '.$cat.'!';
		}
		catch (Exception $e) {
			$retour['message'] = $e->getMessage();
		}
	}

	/*************************************************************************** COLONNES ******************************/

	// AJOUT d'une colonne à une table
	if ($action == 'addRowToTable') {
		$rowName = preg_replace('/ /', '_', $rowName);
		if ($customMode == 'false') {
			switch($rowType) {
				case 'int':
					$typeRow = 'INT(9)';
					break;
				case 'float':
					$typeRow = 'FLOAT';
					break;
				case 'datetime':
					$typeRow = 'DATETIME';
					break;
				case 'boolean':
					$typeRow = 'TINYINT(1)';
					break;
				case 'timecode':
					$typeRow = 'VARCHAR(11)';
					break;
				case 'text':
					$typeRow = 'TEXT';
					break;
				default:
					$typeRow = 'TEXT';
					break;
			}
			if (preg_match('/^\*/', $rowType)) {
				$rel = new RelCheck($tableName);
				if (!$rel->has_relation("CC_$rowName")) {
					$infRel = explode('.', preg_replace('/^\*/', '', $rowType));
					$rel->addRelation($tableName, "CC_$rowName", $infRel[2], $infRel[0], $infRel[1], $infRel[3]);
				}
			}
			if (Infos::addNewChamp($tableName, "CC_$rowName", $typeRow, $defaultVal)) {
				$retour['error'] = 'OK';
				$retour['message'] = 'Column added.';
			}
			else
				$retour['message'] = "Adding row to table $tableName failed!";
		}
		else {
			$ct = new CustomTable($tableName, $projID);
			$ct->addRow($rowName, $rowType, $defaultVal);
			$retour['error'] = 'OK';
			$retour['message'] = 'Column added.';
		}
	}


	// SUPPRESSION d'une colonne à une table
	if ($action == 'deleteTableRow') {
		if ($customMode == 'false') {
			if (preg_match('/^CC_/', $rowName)) {
				if (Infos::removeChamp($tableName, $rowName)) {
					$retour['error'] = 'OK';
					$retour['message'] = 'Column deleted';
				}
				else
					$retour['message'] = "Deleting row to table $tableName failed!";
			}
			else $retour['message'] = "This column ($rowName) can't be deleted from table $tableName! It's a SaAM's internal system column.";
		}
		else {
			$ct = new CustomTable($tableName, $projID);
			$ct->deleteRow($rowName);
			$retour['error'] = 'OK';
			$retour['message'] = 'Column Deleted.';
		}
	}

	// Réorganisation des colonnes (custom categories only)
	if ($action == 'reorderColumns') {
		if ($customMode != 'true')
			throw new Exception('Reordering columns is restricted to custom categories!');
		$newColOrder = json_decode(urldecode($newOrder));
		$ct = new CustomTable($tableName, $projID);
		$ct->reoderRows($newColOrder);
		$retour['error'] = 'OK';
		$retour['message'] = 'Columns reordered. Reloading...';
	}

	// Retourne les colonnes utilisable d'une table donnée
	if ($action == 'getColumns') {
		$table = explode('.', preg_replace('/^\*/', '', $table));
		if (preg_match('/^CT_/', $table[0])) {
			$cTable = preg_replace('/^CT_/', '', $table[0]);
			$ct = new CustomTable($cTable, $projID);
			$rows = $ct->getRows();
		}
		else {
			$rows = Liste::getRows($table[0]);
			$fk = array_search('passwd', $rows);
			if ($fk !== false)
				unset($rows[$fk]);
		}
		$retour['error'] = 'OK';
		$retour['message'] = 'Getting columns of table '.$table;
		$retour['rows'] = urlencode(json_encode($rows));
	}

	/*************************************************************************** LIGNES ********************************/

	// Affichage des lignes archivées
	if ($action == 'showArchived') {
		$_SESSION['prodHideArchived'][$projID] = ($state == 'true') ? true : false;
		$retour['error'] = 'OK';
		$retour['message'] = ($state == 'true') ? 'Hiding Archived entries...' : 'Showing Archived entries...';
	}

	// Ajout d'une ligne
	if ($action == 'addEntryToTable') {
		$vals = json_decode(urldecode($values), true);
//		for ($i=0; $i<=10; $i++) {
		if ($customMode == 'false') {
			switch ($tableName) {
				case TABLE_SEQUENCES:
					$se = new Sequences();
					$se->setInfos($projID, $vals);
					break;
				case TABLE_SHOTS:
					$sh = new Shots();
					$sh->setInfos($projID, $vals);
					break;
				case TABLE_SCENES:
					$sc = new Scenes();
					$sc->setInfos($vals);
					break;
				case TABLE_TASKS:
					$vals[Tasks::ID_PROJECT_TASK] = (int)$projID;
					$st = new Tasks('new', $vals);
					$st->saveTask();
					break;
				case TABLE_CAMERAS:
					$cam = new Infos(TABLE_CAMERAS);
					$vals['ID_project'] = $projID;
					$vals['ID_creator'] = $_SESSION['user']->getUserInfos(Users::USERS_ID);
					$vals['update']		= date('Y-m-d H:i:s');
					$vals['updated_by'] = $_SESSION['user']->getUserInfos(Users::USERS_ID);
					foreach ($vals as $key => $val)
						$cam->addInfo($key, $val);
					$cam->save();
					break;
				case TABLE_ASSETS:
					$as = new Assets($projID, $vals['filename'], $vals['path_relative']);
					$as->setInfos($vals);
					break;
				case TABLE_USERS:
					$us = new Users();
					$vals[Users::USERS_PASSWORD]  = $vals[Users::USERS_LOGIN];
					$vals[Users::USERS_MY_PROJECTS] = '["'.$projID.'"]';
					foreach ($vals as $key => $val)
						$us->setUserInfos($key, $val);
					$us->save();
					break;
				default:
					throw new Exception("Table '$tableName' unknown or not modifiable!");
					break;
			}
			$retour['error'] = 'OK';
			$retour['message'] = "New entry added in table $tableName";
		}
		else {
			$ct = new CustomTable($tableName, $projID);
			$ct->addEntry($vals);
			$retour['error'] = 'OK';
			$retour['message'] = "New entry added in custom table $tableName";
		}
//		}
	}

	// Delete / Restore / Show / Hide / une ou plusieurs ligne(s)
	if (preg_match('/^multiLine_/', $action) && $action != 'multiLine_export') {
		$lines = json_decode(urldecode($lines), true);
		if (!is_array($lines))
			throw new Exception('Bad array for lines!');
		foreach ($lines as $lineID) {
			if ($customMode == 'false') {
				switch ($tableName) {
					case TABLE_SEQUENCES:
						$se = new Sequences($lineID);
						if ($action == "multiLine_show")
							$se->setHide(0);
						if ($action == "multiLine_hide")
							$se->setHide(1);
						if ($action == "multiLine_delete")
							$se->archiveSequence();
						if ($action == "multiLine_restore")
							$se->restoreSequence();
						$se->save();
						break;
					case TABLE_SHOTS:
						$sh = new Shots($lineID);
						if ($action == "multiLine_show")
							$sh->setHide(0);
						if ($action == "multiLine_hide")
							$sh->setHide(1);
						if ($action == "multiLine_delete")
							$sh->archiveShot();
						if ($action == "multiLine_restore")
							$sh->restoreShot();
						$sh->save();
						break;
//					case TABLE_SCENES:
//						$sc = new Scenes($lineID);
//						if ($action == "multiLine_show")
//							$sc->setHide(0);
//						if ($action == "multiLine_hide")
//							$sc->setHide(1);
//						if ($action == "multiLine_delete")
//							$sc->archiveScene($vals);
//						if ($action == "multiLine_restore")
//							$sc->restoreScene($vals);
//						$sc->save();
//						break;
					case TABLE_ASSETS:
						$as = new Assets($projID, (int)$lineID);
						if ($action == "multiLine_show")
							$as->setHide(0);
						if ($action == "multiLine_hide")
							$as->setHide(1);
						if ($action == "multiLine_delete")
							$as->archiveAsset();
						if ($action == "multiLine_restore")
							$as->restoreAsset();
						$as->save();
						break;
					case TABLE_USERS:
						throw new Exception("Users ar not modifiable from here.<br /Please use the right panel!");
					default:
						throw new Exception("Table '$tableName' unknown or not modifiable!");
				}
			}
			else {
				$ct = new CustomTable($tableName, $projID);
				if ($action == "multiLine_delete")
					$ct->archiveEntry($lineID);
				if ($action == "multiLine_restore")
					$ct->restoreEntry($lineID);
			}
			$retour['error'] = 'OK';
			$retour['message'] = "Entries modified.";
		}
	}


	// Modifie les valeurs d'une ou plusieurs ligne(s)
	if ($action == 'modifyLines') {
		$lines = json_decode(urldecode($lines));
		if ($customMode == 'false') {
			$inf = new Infos($table);
			foreach ($lines as $IDentry) {
				$inf->loadInfos('id', $IDentry);
				$inf->addInfo($row, urldecode($newVal));
				$inf->save();
			}
			$rel  = new RelCheck($table);
			$poss = $rel->get_all_possibilities($row, $projID);
		}
		else {
			$ct = new CustomTable($table, $projID);
			$ct->modifEntries($lines, $row, urldecode($newVal));
			$ct->getRows();
			$poss = $ct->getPossibilities($row);
		}
		$retour['error'] = 'OK';
		$retour['message'] = "Entries modified.";
		$retour['rowname'] = $row;
		$retour['newVal'] = $retour['realVal'] = $newVal;
		switch ($modType) {
			case 'calendar':
				$retour['newVal'] = SQLdateConvert($newVal);
				break;
			case 'boolean':
				$retour['newVal'] = ($newVal == '1') ? L_BTN_YES: L_BTN_NO;
				break;
			case 'menu_rel':
				$retour['newVal'] = '**reload**';
				if (array_key_exists($newVal, $poss))
					$retour['newVal'] = '<span class="gros colorBtnFake">*</span> '.$poss[$newVal];
				break;
			case 'tags':
				$tags = json_decode(urldecode($newVal));
				$tagsDisp = '';
				if (is_array($tags)) {
					foreach($tags as $tag)
						$tagsDisp .= '<div class="inline ui-state-highlight ui-corner-all" style="padding:1px 4px;">'. $tag .'</div> ';
				}
				else $tagsDisp = '<div class="inline ui-state-highlight ui-corner-all" style="padding:1px 4px;">'.RelCheck::NOT_FOUND_MSG.'</div>';
				$retour['newVal'] = $tagsDisp;
				break;
			case 'menu_rel_multiple':
				$valDisp = '';
				foreach (json_decode(urldecode($newVal)) as $item)
					$valDisp .= '<div class="inline ui-state-highlight ui-corner-all" style="padding:1px 4px;">'. $poss[$item] .'</div> ';
				$retour['newVal'] = $valDisp;
				break;
		}
	}


	// Exporte une ou plusieurs ligne(s) vers un fichier
	if ($action == 'multiLine_export') {
		$entries = json_decode(urldecode($lines));
		require_once('prod_exports.php');
		$exportFileName = export_table($tableName, $projID, $customMode, $fileName, $fileType, $entries);
		if ($exportFileName === false) { throw new Exception("Unable to export table '$tableName' in ".strtoupper($fileType)." format!"); }
		$retour['error'] = 'OK';
		$retour['persistant'] = 'persist';
		$message = (count($entries) >= 1) ? count($entries). " entries exported from '$tableName', in $fileType." : "Whole table '$tableName' exported in $fileType.";
		$retour['message'] = "$message <div class='center gras gros margeTop5'><u><a href='fct/downloader.php?type=prod_export&file=$exportFileName'>Download export: ".urldecode($exportFileName)."</a></u></div>.";
	}

	// Import de fichier CSV -> Création de new custom cat
	if ($action == 'createCatFromCSVFile') {
		$CSVfile = INSTALL_PATH.FOLDER_TEMP.'uploads/prod/'.$tempFileName;
		if (!file_exists($CSVfile))
			throw new Exception("File '$tempFileName' is missing in temp folder!");
		$CSVraw  = file_get_contents($CSVfile);
		$lines   = str_getcsv(trim($CSVraw), "\n");
		$CSVcols = str_getcsv(trim(array_shift($lines), ';'), ';');
		if (!is_array($CSVcols))
			throw new Exception("CSV file '$tempFileName' is not valid!");
		$CSVdata = Array();
		foreach ($lines as $nLine => $line) {
			$values = str_getcsv(trim($line, ';'), ';');
			foreach($values as $nRow => $val) {
				$col = $CSVcols[$nRow];
				if ($col == 'id')
					continue;
				$CSVdata[$nLine][$col] = $val;
			}
		}
		$ct = new CustomTable($tableName, $projID);
		$ct->addTable();
		foreach($CSVcols as $rowName) {
			if ($rowName == 'id' || $rowName == 'status')
				continue;
			$ct->addRow($rowName, 'text');
		}
		foreach($CSVdata as $lineVals)
			$ct->addEntry($lineVals);

		$retour['error'] = 'OK';
		$retour['message'] = "File imported, category $tableName created.";
		$_SESSION['lastVisitedTable'][$projID] = $tableName;
	}

}
catch(Exception $e) { $retour['message'] = $e->getMessage(); }

echo json_encode($retour);
?>
