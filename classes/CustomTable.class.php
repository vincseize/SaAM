<?php

require_once (INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php' );

class CustomTable {

	const PROD_ID_PROJECT = "ID_project";

	private $tableName;
	private $projectID;
	private $rowsTable;
	private $rowsTypes;
	private $rowsRelations;
	private $entriesTable;

	public function __construct ($tableName=false, $projID=false) {
		if (!$tableName) { throw new Exception("CustomTable::construct() : Missing table name!"); return; }
		if (!$projID) { throw new Exception("CustomTable::construct() : Missing project ID!"); return; }
		$this->tableName = $tableName;
		$this->projectID = $projID;
		$this->rowsTable = Array();
		$this->rowsType  = Array();
		$this->rowsRelations  = Array();
	}


	// Ajout de custom table
	public function addTable() {
		try {
			$l = new Liste();
			$existingTables = $l->getRows(TABLE_PROD_CUSTOM);
			$inf = new Infos(TABLE_PROD_CUSTOM);
			$inf->loadInfos('id', 0);
			if (in_array($this->tableName, $existingTables)) {
				$descrTable = json_decode($inf->getInfo($this->tableName), true);
				$descrTable[0][] = $this->projectID;	// @TODO : check si table existe pas déjà pour ce projet
			}
			else {
				$descrTable = Array(
					Array($this->projectID),
					Array("id"=>"int", "status"=>"*saam_config.default_status.val>json.default_status")
				);
			}
			$inf->addInfo($this->tableName, json_encode($descrTable));
			$inf->save();
			return true;
		}
		catch(Exception $e) {
			throw new Exception("CustomTable::addTable() : " . $e->getMessage());
			return false;
		}
	}

	// Renommage de custom table
	public function renameTable ($newName) {
		global $bdd;
		try {
			$newNameOK = preg_replace('/ /', '_', $newName);
			$q = $bdd->prepare("ALTER TABLE `".TABLE_PROD_CUSTOM."` CHANGE `$this->tableName` `$newNameOK` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
			$q->execute();
			return true;
		}
		catch(Exception $e) {
			throw new Exception("CustomTable::renameTable() : " . $e->getMessage());
			return false;
		}
	}


	// Récupère toutes les colonnes d'une table
	public function getRows() {
		try {
			$infTable = new Infos(TABLE_PROD_CUSTOM);
			$infTable->loadInfos('id', 0);
			$rows	  = json_decode($infTable->getInfo($this->tableName));
			if (!is_array($rows[0]))
				return false;
			if (in_array($this->projectID, $rows[0]))
				$realRows = $rows[1];
			else
				return false;
			foreach($realRows as $r => $rType) {
				$this->rowsTable[] = $r;
				$rowType = $rType;
				if (preg_match('/^\*/', $rType)) {
					$relStr = preg_replace('/^\*/', '', $rType);
					if ($relStr == '')
						$this->rowsRelations[$r] = Array('ERROR','ERROR','direct','ERROR');
					else
						$this->rowsRelations[$r] = explode('.', $relStr);
					$rowType = 'menu_rel';
				}
				if (preg_match('/\.json>(val|json)\./', $rType))
					$rowType = 'menu_rel_multiple';
				if (preg_match('/global_tags/', $rType))
					$rowType = 'tags';
				if ($rType == 'datetime')
					$rowType = 'calendar';
				$this->rowsTypes[$r] = $rowType;
			}
			return $this->rowsTable;
		}
		catch(Exception $e) {
			return false;
		}
	}
	// Récupère les types de toutes les colonnes
	public function getRowsTypes () {
		return $this->rowsTypes;
	}

	// Récupère le type d'une colonne
	public function getRowType($row) {
		return $this->rowsTypes[$row];
	}

	// Retourne True si une relation existe pour cette colonne
	public function hasRelation($row) {
		if (isset($this->rowsRelations[$row])) return true;
		return false;
	}

	// Retourne la valeur associée pour cette colonne (si relation, sinon la valeur de base)
	public function getRelVal($row, $val, $returnCol=false) {
		if ($this->hasRelation($row)) {
			$linkedTable = $this->rowsRelations[$row][0];
			$linkedCol	 = $this->rowsRelations[$row][1];
			$linkType	 = $this->rowsRelations[$row][2];
			if (!$returnCol)
				$returnCol = $this->rowsRelations[$row][3];
			try {
				$inf = new Infos($linkedTable);
				switch ($linkType) {
					case 'direct':
						if (preg_match('/^CT_/', $linkedTable)) {
							$linkedCtable = preg_replace('/^CT_/', '', $linkedTable);
							$inf = new Infos(TABLE_PROD_CUSTOM);
							$inf->loadInfos('id', $val);
							$vals = json_decode($inf->getInfo($linkedCtable), true);
							if (array_key_exists($returnCol, $vals))
								$result = Array($val, $vals[$returnCol]);
							else $result = Array($val, RelCheck::NOT_FOUND_MSG);
						}
						else {
							$inf->loadInfos($linkedCol, $val);
							$result = Array($val, $inf->getInfo($returnCol));
						}
						break;
					case 'val>json':
						if ($linkedTable == TABLE_CONFIG)
							$inf->loadInfos('version', SAAM_VERSION);
						else
							$inf->loadInfos($linkedCol, $val);
						$respArr = json_decode($inf->getInfo($returnCol), true);
						$valDisp = (isset($respArr[$val])) ? $respArr[$val] : RelCheck::NOT_FOUND_MSG;
						$result  = Array($val, $valDisp);
						break;
					case 'json>val':
						if (is_array($val)) {
							$realVal = json_encode($val);
							$valDisp = Array();
							foreach ($val as $rv) {
								if (preg_match('/^CT_/', $linkedTable)) {
									$linkedCtable = preg_replace('/^CT_/', '', $linkedTable);
									$inf = new Infos(TABLE_PROD_CUSTOM);
									$inf->loadInfos('id', $rv);
									$vals = json_decode($inf->getInfo($linkedCtable), true);
									if (array_key_exists($returnCol, $vals))
										$valDisp[] = $vals[$returnCol];
									else $valDisp[] = RelCheck::NOT_FOUND_MSG.' ('.$rv.')';
								}
								elseif ($linkedTable == TABLE_CONFIG)  {
									$inf->loadInfos('version', SAAM_VERSION);
									$valDisp[] = $inf->getInfo($returnCol);
								}
								else {
									$inf->loadInfos($linkedCol, $rv);
									$valDisp[] = $inf->getInfo($returnCol);
								}
							}
						}
						else {
							$realVal = '';
							$valDisp = Array(RelCheck::NOT_FOUND_MSG);
						}
						$result  = Array($realVal, $valDisp);
						break;
					case 'json>json':
						if (is_array($val)) {
							$realVal = json_encode($val);
							$valDisp = $val;
						}
						else {
							$realVal = '';
							$valDisp = Array(RelCheck::NOT_FOUND_MSG);
						}
						$result = Array($realVal, $valDisp);
						break;
				}
			}
			catch(Exception $e) { $result  = Array($val, RelCheck::NOT_FOUND_MSG); }
			return $result;
		}
		return Array($val, $val);
	}

	// Retourne les infos d'une relation pour une row donnée
	public function getRelInfo($row) {
		if (!isset($this->rowsRelations[$row]))
			return '';
		return $this->rowsRelations[$row][0].'->'.$this->rowsRelations[$row][1];
	}

	// Récupère les possibilités pour une relation
	public function getPossibilities($row, $projID=false, $returnCol=false, $filter_by=false, $filter=false) {
		try {
			if (!isset($this->rowsRelations[$row]))
				return Array();
			$linkedTable = $this->rowsRelations[$row][0];
			$linkedCol	 = $this->rowsRelations[$row][1];
			$linkType	 = $this->rowsRelations[$row][2];
			$possibilities = Array();
			if (!$returnCol)
				$returnCol = $this->rowsRelations[$row][3];
			if ($linkType == 'val>json' || $linkType == 'json>json') {
				if (preg_match('/^CT_/', $linkedTable))
					return Array();
				else {
					$inf = new Infos($linkedTable);
					if ($linkedTable == TABLE_CONFIG)
						$inf->loadInfos('version', SAAM_VERSION);
					else
						$inf->loadInfos($filter_by, $filter);
					$possibilities = json_decode($inf->getInfo($returnCol), true);
				}
			}
			else {
				$l = new Liste();
				if (preg_match('/^CT_/', $linkedTable)) {
					$linkedCtable = preg_replace('/^CT_/', '', $linkedTable);
					$l->addFiltre('deleted', '=', '0');
					if ($projID)
						$l->addFiltre(CustomTable::PROD_ID_PROJECT, '=', $projID);
					$lines = $l->getListe(TABLE_PROD_CUSTOM, 'id, '.$linkedCtable);
					foreach($lines as $line) {
						$listVals = json_decode($line[$linkedCtable], true);
						if (@array_key_exists($returnCol, $listVals))
							$possibilities[$line['id']] = $listVals[$returnCol];
					}
				}
				else {
					if (in_array('archive', Liste::getRows($linkedTable)))
						$l->addFiltre('archive', '=', '0');
					if ($projID && in_array(CustomTable::PROD_ID_PROJECT, Liste::getRows($linkedTable)))
						$l->addFiltre(CustomTable::PROD_ID_PROJECT, '=', $projID);
					if ($projID && in_array('ID_projects', Liste::getRows($linkedTable)))
						$l->addFiltre('ID_projects', 'LIKE', '%"'.$projID.'"%');
					if ($filter_by && $filter)
						$l->addFiltre($filter_by, '=', $filter);
					foreach ($l->getListe($linkedTable, 'id, '.$returnCol) as $poss)
						$possibilities[$poss['id']] = $poss[$returnCol];
				}
			}
			return $possibilities;
		}
		catch(Exception $e) {
			throw new Exception("CustomTable::getPossibilities() : " . $e->getMessage());
			return Array();
		}
	}

	// Récupère toutes les valeurs d'une table
	public function getValues($hideArchive=true) {
		try {
			$lsTable = new Liste();
			$lsTable->addFiltre(CustomTable::PROD_ID_PROJECT, '=', $this->projectID);
			$lsTable->addFiltre($this->tableName, '!=', '');
			if ($hideArchive === true)
				$lsTable->addFiltre('deleted', '=', '0');
			$lsTable->getListe(TABLE_PROD_CUSTOM, "id, deleted, $this->tableName");
			$fakeTable = $lsTable->simplifyList('id');
			if (!is_array($fakeTable))
				return Array();
			$this->entriesTable = Array();
			foreach($fakeTable as $id => $entry) {
				$values = json_decode($entry[$this->tableName], true);
				if ($values == Null) continue;
				$values = Array('id'=>$id)+$values+Array('archive'=>$entry['deleted']);
				$this->entriesTable[] = array_merge(array_flip($this->rowsTable), $values);
			}
			return $this->entriesTable;
		}
		catch(Exception $e) {
			return Array();
		}
	}

	// Ajout de colonne à la custom table
	public function addRow($rowName, $rowType, $defaultVal='') {
		try {
			$rowName = preg_replace('/ /', '_', $rowName);
			$infTable  = new Infos(TABLE_PROD_CUSTOM);
			$infTable->loadInfos('id', 0);
			$existRows = json_decode($infTable->getInfo($this->tableName), true);
			if (!is_array($existRows[1]))
				throw new Exception("Table's columns definition is broken.");
			if (array_key_exists($rowName, $existRows[1]))
				throw new Exception("Column '$rowName' already exist!");
			$existRows[1][$rowName] = $rowType;
			$infTable->addInfo($this->tableName, json_encode($existRows));
			$infTable->save();
			// Met à jour les valeurs avec la val par défaut
			$l = new Liste();
			$l->addFiltre(CustomTable::PROD_ID_PROJECT, '=', $this->projectID);
			$l->addFiltre('id', '!=', '0');
			$l->addFiltre($this->tableName, '!=', '');
			$lines = $l->getListe(TABLE_PROD_CUSTOM, "id, $this->tableName");
			if (is_array($lines)) {
				$infVal = new Infos(TABLE_PROD_CUSTOM);
				foreach ($lines as $line) {
					$oldJson = json_decode($line[$this->tableName], true);
					if (isset($oldJson[$rowName]) && $defaultVal == '')
						continue;
					$oldJson[$rowName] = $defaultVal;
					$newJson = json_encode($oldJson);
					$infVal->loadInfos('id', $line['id']);
					$infVal->addInfo($this->tableName, $newJson);
					$infVal->save();
				}
				unset($infTable, $l, $infVal);
			}
		}
		catch(Exception $e) {
			throw new Exception("CustomTable::addRow() : " . $e->getMessage());
			return false;
		}
	}

	// Ajout de colonne à la custom table
	public function deleteRow($rowName, $deleteVal=false) {
		try {
			$infTable  = new Infos(TABLE_PROD_CUSTOM);
			$infTable->loadInfos('id', 0);
			$existRows = json_decode($infTable->getInfo($this->tableName), true);
			if (!is_array($existRows[1]))
				throw new Exception("Table's columns definition is broken.");
			if (!array_key_exists($rowName, $existRows[1]))
				throw new Exception("Column '$rowName' doesn't exist!");
			unset($existRows[1][$rowName]);
			$infTable->addInfo($this->tableName, json_encode($existRows));
			$infTable->save();
			if (!$deleteVal)
				return true;
			// Met à jour les valeurs en supprimant les valeurs de la colonne à supprimée
			$l = new Liste();
			$l->addFiltre(CustomTable::PROD_ID_PROJECT, '=', $this->projectID);
			$l->addFiltre('id', '!=', '0');
			$l->addFiltre($this->tableName, '!=', '');
			$lines = $l->getListe(TABLE_PROD_CUSTOM, "id, $this->tableName");
			$infVal = new Infos(TABLE_PROD_CUSTOM);
			foreach ($lines as $line) {
				$oldJson = json_decode($line[$this->tableName], true);
				unset($oldJson[$rowName]);
				$newJson = json_encode($oldJson);
				$infVal->loadInfos('id', $line['id']);
				$infVal->addInfo($this->tableName, $newJson);
				$infVal->save();
			}
			unset($infTable, $l, $infVal);
		}
		catch(Exception $e) {
			throw new Exception("CustomTable::addRow() : " . $e->getMessage());
			return false;
		}
	}

	// Reorganise les colonnes d'une custom table
	public function reoderRows ($newOrder) {
		if (!is_array($newOrder)) { throw new Exception('CustomTable::reoderRows() : $newOrder not an array!'); return false; }
		try {
			$infTable  = new Infos(TABLE_PROD_CUSTOM);
			$infTable->loadInfos('id', 0);
			$tableInfos = json_decode($infTable->getInfo($this->tableName), true);
			if (!in_array($this->projectID, $tableInfos[0]))
				{ throw new Exception('CustomTable::reoderRows() : this project is not defined for this category!'); return false; }
			$newTableInfo = Array("id"=>"int");
			foreach ($newOrder as $row) {
				$newTableInfo[$row] = $tableInfos[1][$row];
			}
			$tableInfos[1] = $newTableInfo;
			$infTable->addInfo($this->tableName, json_encode($tableInfos));
			$infTable->save();
		}
		catch(Exception $e) {
			throw new Exception("CustomTable::addRow() : " . $e->getMessage());
			return false;
		}
	}

	// Ajout de colonne à la custom table
	public function addEntry($values) {
		try {
			if (!is_array($values))
				throw new Exception ('$infos is not an array !');
			$infTable  = new Infos(TABLE_PROD_CUSTOM);
			$infTable->addInfo(CustomTable::PROD_ID_PROJECT, $this->projectID);
			$infTable->addInfo($this->tableName, json_encode($values));
			$infTable->save();
		}
		catch(Exception $e) {
			throw new Exception("CustomTable::addRow() : " . $e->getMessage());
			return false;
		}
	}

	public function modifEntries($lines, $row=false, $newVal=false) {
		try {
			if (!is_array($lines))	throw new Exception ('$lines is not an array !');
			if ($row === false)		throw new Exception ('$row is missing !');
			if ($newVal === false)	throw new Exception ('$row is missing !');
			$newValOK = (json_decode($newVal, true) !== null) ? json_decode($newVal, true) : $newVal;

			$infTable  = new Infos(TABLE_PROD_CUSTOM);
			foreach ($lines as $IDentry) {
				$infTable->loadInfos('id', $IDentry);
				$infos = json_decode($infTable->getInfo($this->tableName), true);
				$infos[$row] = $newValOK;
				$infTable->addInfo($this->tableName, json_encode($infos));
				$infTable->save();
			}
		}
		catch(Exception $e) {
			throw new Exception("CustomTable::modifEntries() : " . $e->getMessage());
			return false;
		}

	}

	// Supprime (archive) une ligne
	public function archiveEntry($idEntry) {
		try {
			$infTable  = new Infos(TABLE_PROD_CUSTOM);
			$infTable->loadInfos('id', $idEntry);
			$infTable->addInfo('deleted', 1);
			$infTable->save();
		}
		catch(Exception $e) {
			throw new Exception("CustomTable::archiveEntry() : " . $e->getMessage());
			return false;
		}
	}

	// Restaure une ligne
	public function restoreEntry($idEntry) {
		try {
			$infTable  = new Infos(TABLE_PROD_CUSTOM);
			$infTable->loadInfos('id', $idEntry);
			$infTable->addInfo('deleted', 0);
			$infTable->save();
		}
		catch(Exception $e) {
			throw new Exception("CustomTable::archiveEntry() : " . $e->getMessage());
			return false;
		}
	}


}

?>
