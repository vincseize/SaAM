<?php

$dumpPath = INSTALL_PATH . FOLDER_CONFIG . 'dumpSQL/';
$codeAuthentik = md5('saamSQLbackup');
global $dumpPath;		// Chemin vers les backup SQL
global $codeAuthentik;	// Code "d'authenticité" pour la création / récupération

function getTableList () {
	global $bdd;
	$q = $bdd->prepare('SHOW TABLES');
	$q->execute();
	$tablesNames = $q->fetchAll(PDO::FETCH_COLUMN);
	return $tablesNames ;
}

function getDumpList () {
	$list = array(); global $dumpPath;
	$dir = opendir($dumpPath);
	while (($fileSQL = readdir($dir)) !== false) {
		if ($fileSQL != '.' && $fileSQL != '..' && $fileSQL != '.gitignore')
			$list[] = $fileSQL;
	}
	sort($list, SORT_STRING);
	return $list;
}


// FONCTION DE DUMP SQL DANS UN FICHIER

function backup_SQL ($toBakup='all', $makeDefault=false) {						// args : 'all', ou array() des tables, ou string des tables sép. par des ','
	global $dumpPath; global $bdd; global $codeAuthentik;						//		   et $makeDefault à true pour enregistrer en tant que "BDD_default.sql"
	$now = date('Y-m-d');
	$fileSQL = array();

	if ($toBakup == 'all') {													// Si on dump TOUTES les tables
		$q = $bdd->prepare('SHOW TABLES');
		$q->execute();
		$tables = $q->fetchAll(PDO::FETCH_COLUMN);
		if ($makeDefault != false && $makeDefault != 'false')
			$fileSQL = 'BDD_default.sql';
		else $fileSQL = $now.'.SaAM_ALL_tables.sql';
	}
	else {																		// Si on dump QUE CERTAINES tables
		$tables  = is_array($toBakup) ? $toBakup : explode(',',$toBakup);
		if (count($tables) > 1) {
			$fileSQL = $now;
			foreach($tables as $tableName) {
				$fileSQL .= '.'. preg_replace('/^saam_/', '', $tableName);
			}
			$fileSQL .= '.sql';
		}
		else $fileSQL = $tables[0].'_'.$now.'.sql';								// Si on dump QU'UNE SEULE table
	}

	$output  = "\n-- BACKUP BASE DE DONNÉES -- \n";								// Création du texte du fichier SQL ( -> $output )
	$output .= "-- DATE (AA-MM-JJ): $now \n";
	$output .= "-- FAITE PAR : ".$_SESSION['user']->getUserInfos(Users::USERS_NOM)."\n";
	$output .= "-- $codeAuthentik \n\n";
	$output .= 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";';
	$output .= "\n\n";
	foreach($tables as $table) {
		$c = $bdd->prepare('SHOW CREATE TABLE `'.$table.'`');			// Récup les types de champs de la table
		try { $c->execute(); }
		catch (Exception $e) { echo "erreur SQL : $e"; return false; }	// Si la table n'existe pas, erreur et on arrête tout !
		$resultCreate = $c->fetchAll(PDO::FETCH_ASSOC);

		$r = $bdd->prepare("SELECT * FROM $table");						// Récup les valeurs des champs de la table
		$r->execute();
		$resultTable = $r->fetchAll(PDO::FETCH_ASSOC);
		$nbRec = count($resultTable);									// Compte le nombre d'enregistrements de la table

		$output .= "-- ----------------- TABLE $table ------------------------\n\n";
		$output .= "DROP TABLE IF EXISTS `$table`;\n\n";				// $output : commande de suppression de la table si déjà existante
		$output .= $resultCreate[0]['Create Table'].";\n\n";			// $output : commande de re-création de la table

		if ($nbRec != 0) {
			$output .= "INSERT INTO `$table` VALUES ";						// $output : commande d'insertion des valeurs dans la table
			$countRec = 0;
			foreach ($resultTable as $row) {
				$countRec++ ;
				$output .= "\n(";
				$nbVal = count($row);										// Compte le nombre de colonnes de la table
				$countVal = 0;
				foreach ($row as $value) {
					$countVal++ ;
					$value = addslashes($value);							// valeur : ajoute des slashes devant les caractères réservés
					$value = preg_replace("/\\r\\n/", "/\\\r\\\n/", $value);// valeur : évite que les retours à la ligne soient traduits
					if (isset($value)) $output .= "'$value'" ;				// $output : valeur à ajouter ('' si pas de valeur)
					else $output .= "''";
					if ($countVal == $nbVal) {
						$output .= ")";										// $output : ajout de la parenthèse fermée si à la fin des colonnes
						if ($countRec == $nbRec) $output .= ";";			// $output : ajout du point virgule si à la fin des enregistrements
						else $output .= ",";								// $output : ajout de la virgule si pas encore à la fin des enregistrements
					}
					else $output .= ",";									// $output : ajout de la virgule si pas encore à la fin des colonnes
				}
			}
		}
		$output .= "\n\n\n";
	}
																		// Sauvegarde de(s) fichier(s) SQL
	if (file_put_contents($dumpPath.$fileSQL, (string)$output) !== false)
		return $fileSQL;
	else return false;
}



// FONCTION DE RÉCUPÉRATION DE FICHIER SQL

function retore_SQL ($sqlFile) {
	global $dumpPath; global $bdd; global $codeAuthentik;
	if (file_exists($dumpPath.$sqlFile)) {								// Si le fichier existe
		$SQLcontent = file_get_contents($dumpPath.$sqlFile);
		if (preg_match("/-- $codeAuthentik/", $SQLcontent)) {			// Si le fichier contiens bien le hash MD5 créé lors d'une sauvegarde via le "Dump"
			$q = $bdd->prepare($SQLcontent);							// Execution de la requête du fichier
			try {$q->execute(); $retour = $sqlFile; }
			catch (Exception $e) { $retour = "erreur SQL : $e"; }
		}
		else {
			echo 'CODE de sécurité ERRONÉ ! ';
			$retour = false;
		}
	}
	else {
		echo 'FICHIER INTROUVABLE ! ';
		$retour = false ;
	}
	return $retour;
}


// Définition du mode WIP (setter table infos champ wip)
function setWIP ($wip) {
	if ($wip==null) { throw new Exception('$wip must be boolean !'); }
	$i = new Infos(TABLE_CONFIG);
	$i->loadInfos('version', SAAM_VERSION);
	$i->addInfo('wip', $wip);
	$i->save();
}



// Sauvegarde des données d'un projet, dans un fichier SQL
function backup_SQL_project ($idProj=false, $filepath=false) {
	global $codeAuthentik;
	if (!$idProj)	 { throw new Exception ('$idProj undefined !'); }
	if (!$filepath)	 { throw new Exception ('$filepath (destination backup file) undefined!'); }
	$now = date('Y-m-d');

	$strEx  = "\n\n-- BACKUP PROJECT #$idProj -- \n";
	$strEx .= "-- DATE (AA-MM-JJ): $now \n";
	$strEx .= "-- $codeAuthentik \n\n";
	$strEx .= 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";';
	$strEx .= "\n\n";
	$strEx .= export_SQL_table(TABLE_ASSETS,			Assets::ASSET_ID_PROJECTS, $idProj);
	$strEx .= export_SQL_table(TABLE_ASSETS_DEPTS,		Comments::COMM_ID_PROJECT, $idProj);
	$strEx .= export_SQL_table(TABLE_CAMERAS,			Scenes::ID_SCENE, $idProj);
	$strEx .= export_SQL_table(TABLE_COMM_ASSET,		Comments::COMM_ID_PROJECT, $idProj);
	$strEx .= export_SQL_table(TABLE_COMM_FINAL,		Comments::COMM_ID_PROJECT, $idProj);
	$strEx .= export_SQL_table(TABLE_COMM_SCENES,		Scenes::ID_PROJECT, $idProj);
	$strEx .= export_SQL_table(TABLE_COMM_SHOT,			Comments::COMM_ID_PROJECT, $idProj);
	$strEx .= export_SQL_table(TABLE_COMM_TASKS,		Comments::COMM_ID_TASK, $idProj);
	$strEx .= export_SQL_table(TABLE_DAILIES,			Dailies::DAILIES_PROJECT_ID, $idProj);
	$strEx .= export_SQL_table(TABLE_DAILIES_SUMMARY,	Dailies::DAILIES_PROJECT_ID, $idProj);
	$strEx .= export_SQL_table(TABLE_PROD_CUSTOM,		CustomTable::PROD_ID_PROJECT, $idProj);
	$strEx .= export_SQL_table(TABLE_PROJECTS,			Projects::PROJECT_ID_PROJECT, $idProj);
	$strEx .= export_SQL_table(TABLE_SCENES,			Scenes::ID_PROJECT, $idProj);
	$strEx .= export_SQL_table(TABLE_SCENES_DEPTS,		Scenes::ID_PROJECT, $idProj);
	$strEx .= export_SQL_table(TABLE_SEQUENCES,			Sequences::SEQUENCE_ID_PROJECT, $idProj);
	$strEx .= export_SQL_table(TABLE_SHOTS,				Shots::SHOT_ID_PROJECT, $idProj);
	$strEx .= export_SQL_table(TABLE_SHOTS_DEPTS,		Shots::SHOT_ID_PROJECT, $idProj);
	$strEx .= export_SQL_table(TABLE_TASKS,				Tasks::ID_PROJECT_TASK, $idProj);

	if (!@file_put_contents(INSTALL_PATH.$filepath, $strEx))
		throw new Exception ("Unable to write $filepath!");
	return true;
}


// Retourne une string contenant les infos qu'on cherche, au format SQL
function export_SQL_table ($tableName=false, $champ=false, $val=false) {
	global $bdd;
	if (!$tableName) { throw new Exception ('$tableName undefined !'); }
	if (!$champ)	 { throw new Exception ('$champ undefined !'); }
	if (!$val)		 { throw new Exception ('$val undefined !'); }
	$output  = "\n-- BACKUP TABLE $tableName, FOR $champ = $val -- \n";

	$r = $bdd->prepare("SELECT * FROM `$tableName` WHERE `$champ` LIKE '%$val%'");
	$r->execute();
	$resultTable = $r->fetchAll(PDO::FETCH_ASSOC);
	$nbRec = count($resultTable);									// Compte le nombre d'enregistrements de la table
	if ($nbRec != 0) {
		$output .= "INSERT INTO `$tableName` VALUES ";					// $output : commande d'insertion des valeurs dans la table
		$countRec = 0;
		foreach ($resultTable as $row) {
			$countRec++ ;
			$output .= "\n(";
			$nbVal = count($row);										// Compte le nombre de colonnes de la table
			$countVal = 0;
			foreach ($row as $value) {
				$countVal++ ;
				$value = addslashes($value);							// valeur : ajoute des slashes devant les caractères réservés
				$value = preg_replace("/\\r\\n/", "/\\\r\\\n/", $value);// valeur : évite que les retours à la ligne soient traduits
				if (isset($value)) $output .= "'$value'" ;				// $output : valeur à ajouter ('' si pas de valeur)
				else $output .= "''";
				if ($countVal == $nbVal) {
					$output .= ")";										// $output : ajout de la parenthèse fermée si à la fin des colonnes
					if ($countRec == $nbRec) $output .= ";";			// $output : ajout du point virgule si à la fin des enregistrements
					else $output .= ",";								// $output : ajout de la virgule si pas encore à la fin des enregistrements
				}
				else $output .= ",";									// $output : ajout de la virgule si pas encore à la fin des colonnes
			}
		}
	}
	$output .= "\n\n\n";

	return $output;
}

?>