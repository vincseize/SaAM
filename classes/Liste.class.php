<?php

class Liste {

	const TABLE_INEXIST		= 'La table n\'existe pas !' ;					// erreur si la table n'existe pas
	const FILTRE_IMPOSSIBLE = 'Impossible de filtrer selon ce champ' ;		// erreur si le champ n'existe pas

	private $bddCx;
	private $table;
	private $what;
	private $tri;
	private $ordre;
	private $filtre_key;
	private $filtre;
	private $lastLogiquefiltre;
	private $isFiltred = false;

	private $filtres ;
	private $filtreSQL ;

	private $listResult ;

	/**
	 * Initialise une requête de listing SQL
	 */
	public function __construct () {
		$this->bddCx	= new PDO(DSN, USER, PASS, array(PDO::ATTR_PERSISTENT => false));
		$this->bddCx->query("SET NAMES 'utf8'");
		$this->bddCx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$this->filtres = array() ;
	}

	public function __destruct () {
		$this->bddCx = null;
	}

	/**
	 * Récupère une liste d'entrées pour une table donnée, avec choix des champs et du tri, et filtrage
	 *
	 * @param STRING $table Le nom de la table (utiliser les constantes de table de la config)
	 * @param STRING $want les champs à récupérer, séparés par une virgule (default '*')
	 * @param STRING $tri Le champ à utiliser pour le tri (default 'id')
	 * @param STRING $ordre La direction du tri (default 'ASC')
	 * @param STRING $filtre_key Le champ à utiliser pour le filtre (default False)
	 * @param STRING $filtre_comp Le type de filtre (default '=')
	 * @param STRING $filtre La valeur pour le filtre (default null)
	 * @return MIXED FALSE si aucune donnée, ou ARRAY des datas filtrées et triées (Throws Exception si table inexistante)
	 */
	public function getListe ($table, $want = '*', $tri='id', $ordre='ASC', $filtre_key = false, $filtre_comp = '=', $filtre = null) {
		$this->what = $want;
		// Check si table existe
		if (Liste::check_table_exist ($table))
			$this->table	= $table;
		else
			throw new Exception("TABLE $table doesn't exists !" );
		// pour chaque filtre défini par Liste::addFiltre()
		if ( isset ( $this->filtres) && ! empty ( $this->filtres ) ) {
			$FM = '' ;
			foreach ( $this->filtres as $f ) $FM .= $f;
			$filtrage_multiple = trim( $FM, $this->lastLogiquefiltre );
		}
		$this->tri	 = $tri;
		$this->ordre = $ordre;
		if ($filtre_key && (string)$filtre != null ) {
			if (Liste::check_col_exist($filtre_key)) {
				$this->isFiltred  = true;
				$this->filtre_key = $filtre_key;
				$this->filtre	  = addslashes($filtre);
			}
			else return false ;
		}
		if ($this->isFiltred)
			$q = "SELECT $this->what FROM `$this->table` WHERE `$this->filtre_key` $filtre_comp '$this->filtre' ORDER BY `$tri` $ordre";
		elseif ( isset ($filtrage_multiple) )
			$q = "SELECT $this->what FROM `$this->table` WHERE $filtrage_multiple ORDER BY `$tri` $ordre";
		elseif ( isset ( $this->filtreSQL ) )
			$q = "SELECT $this->what FROM `$this->table` WHERE $this->filtreSQL ORDER BY `$tri` $ordre";
		else
			$q = "SELECT $this->what FROM `$this->table` ORDER BY `$this->tri` $this->ordre";
//		echo '<p>'.$q.'</p>';
		$q = $this->bddCx->prepare ($q) ;
		$q->execute();

		if ($q->rowCount() >= 1) {
			$result = $q->fetchAll(PDO::FETCH_ASSOC) ;
			$retour = array();
			if ( strpos($this->what, ',') == false && $this->what != '*') {
				foreach ($result as $resultOK)
					$retour[] = $resultOK[$this->what];
			}
			else {
				foreach ($result as $resultOK) {
					unset($resultOK['password']);
					$retour[] = $resultOK;
				}
			}
			$this->listResult = $retour ;
			return $retour;
		}
		else return false;
	}

	/**
	 *  Ajoute une condition au filtre pour la requête
	 *
	 * @param STRING $filtre_key Le champ à utiliser pour le filtre
	 * @param STRING $filtre_comp Le type de filtre (default '=')
	 * @param STRING $filtre La valeur pour le filtre
	 * @param STRING $logique La logique à utiliser pour l'ajout au précédent filtre (default 'AND')
	 */
	public function addFiltre($filtre_key = false, $filtre_comp = '=', $filtre = false , $logique = ' AND '){
		$filtre = addslashes($filtre);
		$this->filtres[] = " (`$filtre_key` $filtre_comp '$filtre') $logique" ;
		$this->lastLogiquefiltre = $logique;
	}

	/**
	 *  Ajoute une condition sans STRING au filtre pour la requête
	 *
	 * @param STRING $filtre_key Le champ à utiliser pour le filtre
	 * @param STRING $filtre_comp Le type de filtre (default '=')
	 * @param STRING $filtre La valeur pour le filtre
	 * @param STRING $logique La logique à utiliser pour l'ajout au précédent filtre (default 'AND')
	 */
	public function addFiltreRaw($filtre_key = false, $filtre_comp = '=', $filtre = false , $logique = ' AND '){
		$filtre = addslashes($filtre);
		$this->filtres[] = " (`$filtre_key` $filtre_comp $filtre) $logique" ;
		$this->lastLogiquefiltre = $logique;
	}

	/**
	 * Annule tout filtrage précédemment défini pour la requête
	 */
	public function resetFiltre() {
		$this->isFiltred  = false;
		$this->filtre_key = false;
		$this->filtre	  = null;
		$this->filtres	  = null;
		$this->lastLogiquefiltre = null;
	}

	/**
	 * Pour définir un filtre manuellement pour la requête
	 *
	 * @param STRING $filtre
	 */
	public function setFiltreSQL( $filtre ){
		$this->filtreSQL = $filtre ;
	}

	/**
	 * Renvoie un tableau trié de la liste, où l'index est $wantedInd au lieu d'un index incrémentiel (0,1,2...)
	 *
	 * @param STRING $wantedInd Le champ que l'on veut utiliser comme index. Utiliser les constantes de champs des classes. (default 'id')
	 * @return MIXED FALSE si aucune donnée, ou bien ARRAY des données indexées sur $wantedInd
	 */
	public function simplifyList($wantedInd = null ) {
		if ($this->listResult == null || empty ($this->listResult)) return false ;
		if ( $wantedInd == null ) $wantedInd = 'id' ;
		$newTableau = array();
		foreach( $this->listResult as $entry){
			$ind = $entry[$wantedInd];
			$newTableau[$ind] = $entry ;
		}
		return $newTableau ;
	}



///////////////////////////////////////////////////////////// METHODES PRIVÉES /////////////////////////////////////////////////////

	/**
	 * Vérifie si une table existe bel et bien dans la base
	 *
	 * @param STRING $table Le nom de la table à tester(utiliser les constantes de table de la config)
	 * @return BOOL TRUE si la table existe, FALSE sinon
	 */
	private function check_table_exist ($table) {
		$q = $this->bddCx->prepare("SHOW TABLES LIKE '$table'");
		$q->execute();
		if ($q->rowCount() >= 1)
			return true;
		else return false;
	}

	/**
	 * Vérifie si un champ existe bel et bien dans la table
	 *
	 * @param STRING $champ Le nom du champ à tester
	 * @return BOOL TRUE si le champ existe, FALSE sinon
	 */
	private function check_col_exist ($champ) {
		$q = $this->bddCx->prepare("SELECT `$champ` FROM `$this->table`");
		$q->execute();
		if ($q->rowCount() >= 1)
			return true;
		else return false;
	}


///////////////////////////////////////////////////////////// METHODES STATIQUES /////////////////////////////////////////////////////


	/**
	 * Retourne un tableau contenant les noms des champs d'une table
	 *
	 * @param STRING $table Le nom de la table (utiliser les constantes de table de la config)
	 * @return MIXED FALSE si table inexistante, ou bien ARRAY avec la liste des champs
	 */
	public static function getRows ( $table=false ) {
		if (!$table) return false;
		global $bdd;
		$q = $bdd->prepare("DESCRIBE `".$table."`");
		$q->execute();
		return $q->fetchAll(PDO::FETCH_COLUMN);
	}

	/**
	 * Fonction utilitaire statique pour récupérer la valeur maxi d'un champ
	 *
	 * @param STRING $table Le nom de la table (utiliser les constantes de table de la config)
	 * @param STRING $champ Le nom du champ à récupérer
	 * @return MIXED FALSE si champ inexistant, INT avec la valeur maxi du champ dans la table
	 */
	public static function getMax ($table, $champ){
		global $bdd;
		$q = $bdd->prepare("SELECT `$champ` from `$table` WHERE `$champ` = (SELECT MAX($champ) FROM `$table`)");
		$q->execute();
		if ($q->rowCount() >= 1) {
			$result = $q->fetch(PDO::FETCH_ASSOC);
			return $result[$champ];
		}
		else return false;
	}

	/**
	 * Retourne l'index du prochain auto-increment d'une table
	 *
	 * @param STRING $table Le nom de la table (utiliser les constantes de table de la config)
	 * @return MIXED FALSE si champ inexistant, INT avec la valeur de l'AI
	 */
	public static function getAIval ( $table=false ) {
		if (!$table) return false;
		global $bdd;
		$q = $bdd->prepare("SHOW TABLE STATUS LIKE '$table'");
		$q->execute();
		if ($q->rowCount() >= 1) {
			$result = $q->fetch(PDO::FETCH_ASSOC);
			$AIval = $result['Auto_increment'];
			return $AIval;
		}
		else return false;
	}


	/**
	 * Fonction utilitaire statique pour re-trier un tableau non associatif en un tableau associatif par l'id (1 seule dimension, 1 seule valeur)
	 * @TODO : amélioration du re-triage pour pouvoir mettre plusieurs valeurs
	 * @param ARRAY $arr Le tableau à re-trier
	 * @param STRING $champ Le champ à récupérer en tant que valeur
	 * @return ARRAY Le tableau associatif, de la forme (item[id]=champVal)
	 */
	public static function resortById ($arr, $champ='label') {
		if (!is_array($arr)) return false;
		$arrOK = array();
		foreach ($arr as $item) {
			if (!isset($item['id'])) return false;
			$arrOK[$item['id']] = $item[$champ];
		}
		return $arrOK;
	}

}

?>
