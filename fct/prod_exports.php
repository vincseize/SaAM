<?php
require_once('directories.php');

// Export de tables PROD vers fichier
function export_table($table, $idProj, $customMode, $fileName, $fileType, $lines=Array()) {
	$proj = new Projects($idProj);
	$projTitle = $proj->getTitleProject();
	$partial  = (count($lines) >= 1) ? true : false;
	$fileName = ($partial) ? $projTitle.'_partial_'.$fileName : $projTitle.'_'.$fileName;
	$fileName = preg_replace('/saam_/', '', $fileName);

	if ($customMode == 'true') {
		$rel	 = new CustomTable($table, $idProj);
		$rowsTable	  = $rel->getRows();
		$entriesTable = $rel->getValues($_SESSION['prodHideArchived'][$idProj]);
	}
	else {
		$rel = new RelCheck($table);
		$l	 = new Liste();
		switch ($table) {
			case 'saam_sequences':
				$l->addFiltre(Sequences::SEQUENCE_ID_PROJECT, "=", $idProj);
				if ($_SESSION['prodHideArchived'][$idProj] === true)
					$l->addFiltre(Sequences::SEQUENCE_ARCHIVE, "=", '0');
				break;
			case 'saam_shots':
				$l->addFiltre(Shots::SHOT_ID_PROJECT, "=", $idProj);
				if ($_SESSION['prodHideArchived'][$idProj] === true)
					$l->addFiltre(Shots::SHOT_ARCHIVE, "=", '0');
				break;
			case 'saam_scenes':
				$l->addFiltre(Scenes::ID_PROJECT, "=", $idProj);
				if ($_SESSION['prodHideArchived'][$idProj] === true)
					$l->addFiltre(Scenes::ARCHIVE, "=", '0');
				break;
			case 'saam_derivatives':
				$table = TABLE_SCENES;
				$l->addFiltre(Scenes::ID_PROJECT, "=", $idProj);
				if ($_SESSION['prodHideArchived'][$idProj] === true)
					$l->addFiltre(Scenes::ARCHIVE, "=", '0');
				break;
			case 'saam_cameras':
				$l->addFiltre(Scenes::ID_PROJECT, "=", $idProj);
				if ($_SESSION['prodHideArchived'][$idProj] === true)
					$l->addFiltre(Scenes::ARCHIVE, "=", '0');
				break;
			case 'saam_assets':
				$l->addFiltre(Assets::ASSET_ID_PROJECTS, "LIKE", '%"'.$idProj.'"%');
				if ($_SESSION['prodHideArchived'][$idProj] === true)
					$l->addFiltre(Assets::ASSET_ARCHIVE, "=", '0');
				break;
			case 'saam_tasks':
				$l->addFiltre(Tasks::ID_PROJECT_TASK, "=", $idProj);
				if ($_SESSION['prodHideArchived'][$idProj] === true)
					$l->addFiltre(Tasks::HIDE_TASK, "=", '0');
				break;
			case 'saam_dailies':
				$l->addFiltre(Dailies::DAILIES_PROJECT_ID, "=", $idProj);
				break;
			case 'saam_users':
				$l->addFiltre(Users::USERS_MY_PROJECTS, "LIKE", '%"'.$idProj.'"%');
				break;
			default:
				throw new Exception("Unknown SaAM table!");
		}
		$rowsTable	  = $l->getRows($table);
		$entriesTable = $l->getListe($table);
	}
	makeTempDir('exports');
	file_put_contents(INSTALL_PATH.FOLDER_TEMP."exports/.gitignore", "*\n!.gitignore");
	$expOK = false;
	switch($fileType) {
		case 'csv':
			$expOK = format_export_CSV($customMode, $rel, $rowsTable, $entriesTable, $partial, $lines, $fileName);
			break;
		case 'xml':
			$expOK = format_export_XML($customMode, $rel, $rowsTable, $entriesTable, $partial, $lines, $fileName);
			break;
		case 'pdf':
			$expOK = format_export_PDF($customMode, $rel, $rowsTable, $entriesTable, $partial, $lines, $fileName);
			break;
		default:
			$expOK = format_export_TXT($customMode, $rel, $rowsTable, $entriesTable, $partial, $lines, $fileName);
			break;
	}
	if ($expOK === false)
		return false;
	return urlencode($fileName);
}


// Formate l'export dans un fichier CSV
function format_export_CSV ($customMode, $rel, $rowsTable, $entriesTable, $partial, $lines, $fileName) {
	$data = '';
	if (is_array($entriesTable) && count($entriesTable) >= 1) {

		foreach ($entriesTable as $entry) {
			if ($partial && !in_array($entry['id'], $lines))
				continue;
			if (count($entry) >= 1) {
				foreach ($entry as $key => $val) {
					if (!in_array($key, $rowsTable))
						continue;
					if ($customMode == 'true')
						$valDisp = $rel->getRelVal($key, $val);
					else
						$valDisp = $rel->get_rel_val($key, $val);
					$valWrite = (is_array($valDisp[1])) ? $valDisp[0] : $valDisp[1];
					if ($valWrite == RelCheck::NOT_FOUND_MSG)
						$valWrite = '';
					$data .= preg_replace('/\n/', ' ',$valWrite).";";
				}
				$data .= "\n";
			}
		}
		$wR = file_put_contents(INSTALL_PATH.FOLDER_TEMP."exports/$fileName", implode(";", $rowsTable)."\n");
		$wV = file_put_contents(INSTALL_PATH.FOLDER_TEMP."exports/$fileName", $data, FILE_APPEND);
		if ($wR >= 0 && $wV >= 0)
			return true;
	}
	return false;
}


// @TODO : Formate l'export dans un fichier XML
function format_export_XML ($customMode, $rel, $rowsTable, $entriesTable, $partial, $lines, $fileName) {
	return false;
}


// @TODO : Formate l'export dans un fichier PDF
function format_export_PDF ($customMode, $rel, $rowsTable, $entriesTable, $partial, $lines, $fileName) {
	return false;
}


// @TODO : Formate l'export dans un fichier TXT
function format_export_TXT ($customMode, $rel, $rowsTable, $entriesTable, $partial, $lines, $fileName) {
	return false;
}


?>
