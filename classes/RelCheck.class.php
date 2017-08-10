<?php

require_once (INSTALL_PATH . FOLDER_CLASSES . 'Liste.class.php' );
require_once (INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php' );

class RelCheck {

	const REL_MASTER_TABLE	= 'master_table';
	const REL_MASTER_COLUMN	= 'master_column';
	const REL_LINK_TYPE		= 'link_type';
	const REL_LINK_TABLE		= 'link_table';
	const REL_LINK_COLUMN		= 'link_column';
	const REL_DEF_RETURN		= 'link_default_return';
	const NOT_FOUND_MSG		= '<small class="ui-state-disabled"><i>No corresp. found</i></small>';

	private $master_table;
	private $relList;
	private $rowsDescription;

	// Initialisation, avec le nom d'une table
	public function __construct ($table=false) {
		if (!$table) { throw new Exception("RelCheck::construct() : Missing table!"); return; }
		$this->master_table = $table;
		$this->relList = Array();
		$l = new Liste();
		$l->addFiltre(RelCheck::REL_MASTER_TABLE, '=', $table);
		if ($l->getListe(TABLE_RELATIONS))
			$this->relList	= $l->simplifyList(RelCheck::REL_MASTER_COLUMN);
		global $bdd;
		try {
			$q = $bdd->prepare("SHOW COLUMNS FROM `$table`");
			$q->execute();
			$this->rowsDescription = false;
			if ($q->rowCount() >= 1)
				$this->rowsDescription = $q->fetchAll(PDO::FETCH_ASSOC);
		}
		catch(Exception $e) { $this->rowsDescription = false; }
	}

	// Retourne TRUE si la colonne a une relation avec une autre
	public function has_relation($key) {
		return array_key_exists($key, $this->relList);
	}

	// Retourne les noms de (table, colonne) linkées si une relation existe
	public function get_link_info($key) {
		if (array_key_exists($key, $this->relList)) {
			$linkedTable = $this->relList[$key][RelCheck::REL_LINK_TABLE];
			$linkedCol	 = $this->relList[$key][RelCheck::REL_LINK_COLUMN];
			return $linkedTable.'->'.$linkedCol;
		}
		return '';
	}

	// Retourne la valeur en relation avec la clé donnée, ou la valeur de base si pas de relation
	public function get_rel_val ($key, $val, $returnCol=false) {
		if (array_key_exists($key, $this->relList)) {
			$linkedTable = $this->relList[$key][RelCheck::REL_LINK_TABLE];
			$linkedCol	 = $this->relList[$key][RelCheck::REL_LINK_COLUMN];
			$linkType	 = $this->relList[$key][RelCheck::REL_LINK_TYPE];
			if (!$returnCol)
				$returnCol = $this->relList[$key][RelCheck::REL_DEF_RETURN];
			$result = Array($val, RelCheck::NOT_FOUND_MSG);
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
						$valArr  = json_decode($val, true);
						$valDisp = Array();
						if (is_array($valArr)) {
							foreach ($valArr as $rv) {
								try {
									if (preg_match('/^CT_/', $linkedTable)) {
										$linkedCtable = preg_replace('/^CT_/', '', $linkedTable);
										$inf = new Infos(TABLE_PROD_CUSTOM);
										$inf->loadInfos('id', $rv);
										$vals = json_decode($inf->getInfo($linkedCtable), true);
										if (array_key_exists($returnCol, $vals))
											$valDisp[] = $vals[$returnCol];
										else $valDisp[] = RelCheck::NOT_FOUND_MSG.' ('.$rv.')';
									}
									elseif ($linkedTable == TABLE_CONFIG) {
										$inf->loadInfos('version', SAAM_VERSION);
										$valDisp[] = $inf->getInfo($returnCol);
									}
									else {
										if (is_array($rv)) {
											foreach ($rv as $rva) {
												$inf->loadInfos($linkedCol, $rva);
												$valDisp[] = $inf->getInfo($returnCol);
											}
										}
										else {
											$inf->loadInfos($linkedCol, $rv);
											$valDisp[] = $inf->getInfo($returnCol);
										}
									}
								}
								catch(Exception $e) { $valDisp[] = RelCheck::NOT_FOUND_MSG.' ('.$rv.')'; }
							}
						}
						else $valDisp = Array(RelCheck::NOT_FOUND_MSG);
						$result  = Array($val, $valDisp);
						break;
					case 'json>json':
						$valArr  = json_decode($val, true);
						if (is_array($valArr)) {
							$valDisp = $valArr;
						}
						else $valDisp = Array(RelCheck::NOT_FOUND_MSG);
						$result  = Array($val, $valDisp);
						break;
				}
			}
			catch(Exception $e) { $result  = Array($val, RelCheck::NOT_FOUND_MSG); }
			return $result;
		}
		return Array($val, $val);
	}

	// Retourne toutes les valeurs possibles (id=>default_return) pour une colonne donnée dans la table reliée
	public function get_all_possibilities($key, $projID=false, $returnCol=false, $filter_by=false, $filter=false) {
		$possibilities = Array();
		if ($this->has_relation($key)) {
			$linkedTable = $this->relList[$key][RelCheck::REL_LINK_TABLE];
			$linkCol	 = $this->relList[$key][RelCheck::REL_LINK_COLUMN];
			$linkType	 = $this->relList[$key][RelCheck::REL_LINK_TYPE];
			if (!$returnCol)
				$returnCol = $this->relList[$key][RelCheck::REL_DEF_RETURN];
			if ($linkType == 'val>json' || $linkType == 'json>json') {
				if (preg_match('/^CT_/', $linkedTable))
					return Array();
				$inf = new Infos($linkedTable);
				if ($linkedTable == TABLE_CONFIG)
					$inf->loadInfos('version', SAAM_VERSION);
				else
					$inf->loadInfos($filter_by, $filter);
				$poss = json_decode($inf->getInfo($returnCol), true);
				if ($linkType == 'json>json') {
					foreach($poss as $v)
						$possibilities[$v] = $v;
				}
				else $possibilities = $poss;
			}
			else {
				$l = new Liste();
				if (preg_match('/^CT_/', $linkedTable)) {
					$linkedCtable = preg_replace('/^CT_/', '', $linkedTable);
					$l->addFiltre('deleted', '=', '0');
					if ($projID)
						$l->addFiltre('ID_project', '=', $projID);
					$lines = $l->getListe(TABLE_PROD_CUSTOM, 'id, '.$linkedCtable);
					foreach($lines as $line) {
						$listVals = json_decode($line[$linkedCtable], true);
						if (!is_array($listVals)) continue;
						$possibilities[$line['id']] = $listVals[$returnCol];
					}
				}
				else {
					if ($projID && in_array('ID_project', Liste::getRows($linkedTable)))
						$l->addFiltre('ID_project', '=', $projID);
					if ($projID && in_array('ID_projects', Liste::getRows($linkedTable)))
						$l->addFiltre('ID_projects', 'LIKE', '%"'.$projID.'"%');
					if ($filter_by && $filter)
						$l->addFiltre($filter_by, '=', $filter);
					$possibs = $l->getListe($linkedTable, $linkCol.', '.$returnCol);
					if (is_array($possibs)) {
						foreach ($possibs as $poss)
							$possibilities[$poss[$linkCol]] = $poss[$returnCol];
					}
				}
			}
		}
		return $possibilities;
	}

	// Retourne le type de données d'une valeur
	public function get_dataType ($row, $data) {
		if ($this->has_relation($row)) {
			$linkType = $this->relList[$row][RelCheck::REL_LINK_TYPE];
			if ($linkType == 'json>val' || $linkType == 'json>json')
				return 'menu_rel_multiple';
			return 'menu_rel';
		}
		if (preg_match('/[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/', $data))
			return 'calendar';
		else
			return 'textarea';
	}

	// Retourne le type de données d'une colonne
	public function get_rowType ($row, $data) {
		if ($row == 'progress')
			return 'percentage';
		if ($row == 'tags')
			return 'tags';
		if ($this->rowsDescription == false)
			{ throw new Exception("RelCheck::get_rowType() Error ! Unable to fetch rows for $this->master_table"); return false; }
		foreach ($this->rowsDescription as $r) {
			if ($r['Field'] == $row)
				$rType = $r['Type'];
		}
		switch ($rType) {
			case 'tinyint(1)':
				$rowType = 'boolean';
				break;
			case 'datetime':
				$rowType = 'calendar';
				break;
			default:
				$rowType = 'uncertain';
				break;
		}
		if ($rowType == 'uncertain')
			return $this->get_dataType($row, $data);
		return $rowType;
	}

	// Ajoute une relation
	public function addRelation ($masterTable, $masterRow, $relType, $linkTable, $linkRow, $returnRow) {
		$infRel = new Infos(TABLE_RELATIONS);
		$infRel->addInfo(RelCheck::REL_MASTER_TABLE, $masterTable);
		$infRel->addInfo(RelCheck::REL_MASTER_COLUMN, $masterRow);
		$infRel->addInfo(RelCheck::REL_LINK_TYPE, $relType);
		$infRel->addInfo(RelCheck::REL_LINK_TABLE, $linkTable);
		$infRel->addInfo(RelCheck::REL_LINK_COLUMN, $linkRow);
		$infRel->addInfo(RelCheck::REL_DEF_RETURN, $returnRow);
		$infRel->save();
	}

}
?>
