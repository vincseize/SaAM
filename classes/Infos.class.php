<?php

require_once (INSTALL_PATH . FOLDER_CONFIG  . 'common.inc' );
global $bdd;
class Infos implements Iterator {

	const ALL_DATAS			= '*' ;									// truc par défaut pour la récup de toutes les données
	const UPDATE_OK			= true;									// si un update a fonctionné, on renvoie cela
	const UPDATE_ERROR		= 'erreur SQL lors de la modif !';		// erreur, si un update n'a pas fonctionné
	const NO_INFO			= "Pas d'enregistrement";				// erreur, si aucun enregistrement trouvé dans la table
	const FILTRE_NON_UNIQUE	= "Choix du filtre dangereux : NON UNIQUE en BDD !";


	private $bddCx      ;	// instance de PDO
	private $table      ;	// table de la BDD où travailler
	private $filtre     ;	// nom de la colonne de la table, pour la recherche
	private $filtre_key ;	// valeur à rechercher

	private $datas      ;	// tableau clé/valeur de tous les champs de la table
	private $loaded     ;   // definit si la BDD est lue (update ou insert -> cf. méthode save() )


	public function __construct( $table ) {
		$this->loaded = false ;
		$this->table = $table ;
		$this->datas = array();
	}

	// destruction de l'instance PDO
	public function __destruct () {
		$this->bddCx = null;
	}

	// Charge toutes les infos de l'enregistrement en mémoire
	public function loadInfos ($filtre, $filtre_key ) {
		$this->initPDO();
		$this->filtre      = addslashes($filtre);
		$this->filtre_key  = $filtre_key;

		$sqlReq = "SELECT * FROM `$this->table` WHERE `$filtre` = '$filtre_key'";
		$q = $this->bddCx->prepare($sqlReq) ;
		$q->execute();
		$nbResults = $q->rowCount();
		// vérifie si un enregistrement a été trouvé
	    if ($nbResults == 0) { throw new Exception(Infos::NO_INFO, 404) ; }
		elseif ($nbResults == 1) $result = $q->fetch(PDO::FETCH_ASSOC) ;
		elseif ($nbResults > 1) $result = $q->fetchAll(PDO::FETCH_ASSOC) ;
		foreach ( $result as $key => $val ){
			$this->addInfo ( $key, $val) ;
		}
		$this->loaded = true ;
		$this->bddCx = null;
	}

	// oblige à updater un enregistrement lors de la sauvegarde au lieu de créer un enregistrement.				@OBSOLTETE : pas utilisé, mais conservé au cas ou
	public function forceLoaded ($etat){ $this->loaded = $etat ; }

	// Retourne true si les infos ont déjà été chargées depuis la BDD
	public function is_loaded () {
		return $this->loaded;
	}


	// Ajoute / modifie une info dans la mémoire
	public function addInfo ( $key , $val ){
		$this->datas[$key] = $val ;
	}


	// Compte le nombre d'infos en mémoire
	public function nbInfos (){
		return count ( $this->datas ) ;
	}


	// Récupération d'info en mémoire
	public function getInfo ( $champ = Infos::ALL_DATAS ){
		if ( $champ == Infos::ALL_DATAS )       { return $this->datas ; }
		if ( ! isset ( $this->datas[$champ] ) ) { return false; }
		return $this->datas[$champ] ;
	}



	// MISE À JOUR d'un enregistrement dans la table courante
	public function save ( $filterKey = 'id', $filter='this', $addChamp=true ) {
		$this->initPDO();
		// sauvegarde, si pas d'argument on va chercher l'id, si pas d'id dans les data, on met 0 pour éviter les 'Notices' (pas grave mais propre)
		if ( $filter == 'this') {
			if (isset($this->datas[$filterKey])) $filter = $this->datas[$filterKey];
			else $filter = 0 ;
		}
		// Vérifie si tous les champs existent, sinon crée le champ
		$this->updateBDD($addChamp);
		// Construction de la chaine des clés et valeurs SQL pour la requête
		$keys   = ''; 	$vals   = '';  $up = '' ;
		foreach ( $this->datas as $k => $v ) {
			if ( is_array($v) ) continue ;
			if ( is_string($v)) $v = addslashes($v);
			$keys .= "`$k`, " ;
			$vals .= "'$v', " ;
			$up   .= "`$k`='$v', ";
		}
		// suppression de la dernière virgule
		$keys = substr($keys, 0 , strlen($keys) -2 );
		$vals = substr($vals, 0 , strlen($vals) -2 );
		$up   = substr($up,   0 , strlen($up)   -2 );

		// Insertion ou Update de l'enregistrement
		if ( $this->loaded )
			$req = "UPDATE `$this->table` SET $up WHERE `$filterKey` LIKE '$filter'";
		else
			$req = "INSERT INTO `$this->table` ($keys) VALUES ($vals)";

		$q = $this->bddCx->prepare($req) ;
		try { $q->execute() ; }
		catch (Exception $e) {
			$msg = $e->getMessage();
			if ( strpos($msg, 'SQLSTATE[23000]: Integrity constraint violation',0 ) !== false  ){
				$keyOffset = strrpos( $msg, "'", -2) ;
				$key = substr( $msg, $keyOffset  );
				throw new Exception('ERREUR SQL de Infos::save() : Entree dupliquée ' . $key);
			}

			throw new Exception('ERREUR SQL de Infos::save() : ' . $e->getMessage());
		}

		$bad = $q->errorInfo() ;
		if ($bad[0] == 0 )
			return $req ;
		else
			throw new Exception('ERREUR SQL de Infos::save() : ' . $bad[2]) ;
	}


	// efface un enregistrement de la BDD
	public function delete ( $filterKey = 'id', $filter='this', $filtrePlus = null ) {
		$this->initPDO();
		if ( $filter == 'this') $filter = $this->datas[$filterKey] ;

		$sqlReq = "DELETE FROM `$this->table` WHERE `$filterKey` = \"$filter\"";
		if ($filtrePlus != null) {
			$sqlReq .= " AND ".$filtrePlus;
		}
		$q = $this->bddCx->prepare( $sqlReq);
		$q->execute();
		$bad = $q->errorInfo() ;
		if ($bad[0] == 0 )
			return $q->rowCount() ;
		else
			throw new Exception($bad[2]) ;
	}



///////////////////////////////////////////////// METHODES PRIVÉES /////////////////////////////////////////////////


	// définition de l'objet PDO si pas encore en mémoire
	private function initPDO () {
		$this->bddCx = null;
		$this->bddCx = new PDO(DSN, USER, PASS, array(PDO::ATTR_PERSISTENT => true));
		$this->bddCx->query("SET NAMES 'utf8'");
		$this->bddCx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}


	// Lit tous les champs de la table, si une nouvelle valeur existe en mémoire on ajoute un champ
	private function updateBDD ($addChamp) {
		$this->initPDO();
		$q = $this->bddCx->prepare("SHOW COLUMNS FROM `$this->table`");
		$q->execute();
		if ($q->rowCount() >= 1) {
			$colums = $q->fetchAll();
			foreach ( $this->datas as $k => $v ){			// Si nouvelle clé ds tableau, on ajoute un champ
				$exist = false ;
				foreach ( $colums as $c => $dataC ){
					if ( $k == $dataC["Field"]) { $exist = true ; break ; }
				}
				if (!$exist && $addChamp ) $this->addChamp ( $k, $v) ;
			}
		}
	}


	// Check si un champ est un index unique en BDD (CAD si le champ peut avoir plusieurs fois la même valeur)			// non utilisé pour le moment
	private function checkFiltreUnique ($champ) {
		$this->initPDO();
		$sqlReq = "SHOW INDEXES FROM ".$this->table ;
		$q = $this->bddCx->prepare($sqlReq);
		$q->execute();
		$result = $q->fetchAll(PDO::FETCH_ASSOC);
		$is_unique = false;
		foreach ($result as $index => $param) {
			if ($param['Column_name'] == $champ) {
				if ($param['Non_unique'] == 0) { $is_unique = true; break; }
			}
		}
		return $is_unique;
	}


	// Ajout d'un champ à la table courante
	private function addChamp ($row, $val) {
		$this->initPDO();
		if ($val === null) return false;
		if (is_array($val) || ((strpos('!', $val) !== false) && (strpos('\'', $val) !== false) && (strpos('?', $val) !== false) && (strpos('#', $val) !== false)))
			return false;
		$char = '' ;
		if (is_numeric($val)) {										// check du type de valeur du champ à ajouter
			$tailleNbre = strlen((string)$val);
			$tailleChamp = (int)$tailleNbre + 2;					// taille maxi de la valeur du champ
			if (ctype_digit($val))
				$typeRow = 'INT( '.$tailleChamp.' )';				// Si c'est un nombre entier
			else $typeRow = 'FLOAT( '.$tailleChamp.' )';			// Si c'est un nombre à virgule
		}
		elseif (is_string($val)) {
			$char = "CHARACTER SET utf8 COLLATE utf8_general_ci" ;
			if (strlen($val) <= 30)
				$typeRow = 'VARCHAR(256)';							// Si c'est une petite chaîne
			else $typeRow = 'TEXT';									// Si c'est une grande chaîne
		}
		$sqlAlter = "ALTER TABLE `$this->table` ADD `$row` $typeRow $char NOT NULL" ;
		$a = $this->bddCx->prepare($sqlAlter);
		if ($a->execute()) return true;
		else return false;
	}



///////////////////////////////////////////////// METHODES STATIQUES //////////////////////////////////////////////////


	// Check si un champ existe dans la table courante
	public static function rowExist ($table, $row) {
		$pdoTmp = new PDO(DSN, USER, PASS, array(PDO::ATTR_PERSISTENT => false));
		$pdoTmp->query("SET NAMES 'utf8'");
		$pdoTmp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sqlReq = "SELECT `$row` FROM `$table`";
		try {
			$q = $pdoTmp->prepare($sqlReq);
			$q->execute();
			if ($q->rowCount() == 1) return true;
			else return false;
		}
		catch (Exception $e) { return false; }
	}

	// Ajoute un champ VARCHAR dans une table de la BDD (fonction statique, peut être appellée sans créer d'instance de Infos)
	public static function addNewChamp ($table = '', $row = '', $typeRow = 'VARCHAR(64)', $defaultVal="") {
		if ($table == '' && $row == '')		return false;
		if ($row == 'id')					return false;
		if (Infos::rowExist($table, $row))	return false;

		$extraReq = "";
		if (preg_match('/CHAR|TEXT/i', $typeRow))
			$extraReq = "CHARACTER SET utf8 COLLATE utf8_general_ci ";
		$extraReq .= "NOT NULL";
		if (!preg_match('/TEXT/i', $typeRow))
			$extraReq .= " DEFAULT '$defaultVal'";

		$pdoTmp = new PDO(DSN, USER, PASS, array(PDO::ATTR_PERSISTENT => false));
		$pdoTmp->query("SET NAMES 'utf8'");
		$pdoTmp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sqlAlter = "ALTER TABLE `$table` ADD `$row` $typeRow $extraReq" ;
		$a = $pdoTmp->prepare($sqlAlter);
		if ($a->execute()) return true;
		else return false;
		unset($pdoTmp);
	}

	// Supprime une colonne d'une table de la base de données (fonction statique, peut être appellée sans créer d'instance de Infos)
	public static function removeChamp ($table = '', $row = '') {
		if ($table == '' && $row == '') return false;
		if ($row == 'id') return false;
		$pdoTmp = new PDO(DSN, USER, PASS, array(PDO::ATTR_PERSISTENT => false));
		$pdoTmp->query("SET NAMES 'utf8'");
		$pdoTmp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sqlReq = "ALTER TABLE `$table` DROP `$row`";
		$q = $pdoTmp->prepare($sqlReq);
		if ($q->execute()) return true;
		else return false;
		unset($pdoTmp);
	}


	// Méthodes de l'iterator
	public function current() { return current ($this->datas); }
	public function key()     { return key ($this->datas) ; }
	public function next()	  {	next ( $this->datas );  	}
	public function rewind()  { reset ( $this->datas ); }
	public function valid()   { if ( current ($this->datas) === false  ) return false ; else 	return true ; }


}


?>
