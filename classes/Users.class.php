<?php

require_once (INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php' );

class Users implements Iterator {

	const UPDATE_ERROR_DATA = 'donnée invalide' ;					// erreur si pseudo ou autre donnée ne correspond pas
	const UPDATE_OK			= 'donnée modifiée, OK !' ;				// message si une modif BDD a réussi
	const INFO_ERREUR		= 'Users::Impossible de lire les infos en BDD';// erreur de la méthode Infos::loadInfos()
	const INFO_DONT_EXIST	= 'donnée inexistante' ;				// erreur si champs inexistant dans BDD lors de récup d'info
	const INFO_FORBIDDEN	= 'donnée interdite' ;					// erreur si info est une donnée sensible (ici, password)
	const SAVE_LOSS			= 'Champs manquants';					// erreur si il manques des données essentielles à sauvegarder dans la BDD
	const NO_HABILITATION	= 'Vous n\'êtes pas habilité !';		// erreur si niveau d'habilitation insuffisant

	const USERS_OK          = true ;					// retour général, si la fonction a marché
	const USERS_ERROR       = false ;					// retour général, si la fonction n'a pas marché
	const USERS_ERROR_PASSSHORT     = 'Mot de passe trop court';

	CONST USERS_STATUS_ROOT			= 9 ;						// niveau d'habilitation ROOT
	CONST USERS_STATUS_DEV			= 8 ;						// niveau d'habilitation DEVELOPPER
	CONST USERS_STATUS_MAGIC		= 7 ;						// niveau d'habilitation MAGIC
	CONST USERS_STATUS_DIRPROD		= 6 ;						// niveau d'habilitation PROD DIRECTOR
	CONST USERS_STATUS_SUPERVISOR	= 5 ;						// niveau d'habilitation SUPERVISOR
	CONST USERS_STATUS_LEAD			= 4 ; 						// niveau d'habilitation LEAD
	CONST USERS_STATUS_ARTIST		= 3 ;						// niveau d'habilitation ARTIST
	CONST USERS_STATUS_DEMO			= 2 ;						// niveau d'habilitation DEMO
	CONST USERS_STATUS_VISITOR		= 1 ;						// niveau d'habilitation VISITOR

	const USERS_ID				= 'id' ;
	const USERS_CREATOR			= 'ID_creator' ;
	const USERS_LOGIN			= 'login' ;
	const USERS_PASSWORD		= 'passwd' ;
	const USERS_STATUS			= 'status' ;
	const USERS_PSEUDO			= 'pseudo' ;
	const USERS_NOM				= 'nom' ;
	const USERS_PRENOM			= 'prenom' ;
	const USERS_COMPETENCES		= 'competences' ;
	const USERS_MAIL				= 'mail' ;
	const USERS_VCARD			= 'vcard' ;
	const USERS_LANG			= 'lang' ;
	const USERS_THEME			= 'theme' ;
	const USERS_ACTIVE			= 'actif' ;
	const USERS_RECEIVE_MAILS	= 'receiveMails' ;
	const USERS_RECEIVE_NOTIFS	= 'receiveNotifs' ;
	const USERS_MY_PROJECTS		= 'my_projects' ;
	const USERS_MY_SEQS			= 'my_sequences' ;
	const USERS_MY_SHOTS		= 'my_shots' ;
	const USERS_MY_SCENES		= 'my_scenes' ;
	const USERS_MY_ASSETS		= 'my_assets' ;
	const USERS_MY_TAGS			= 'my_tags' ;
	const USERS_MY_MSGS			= 'my_msgs' ;
	const USERS_PROJECTS_PATH	= 'projects_local_path';
	const USERS_PROJECTS_URL		= 'projects_distant_url';
	const USERS_DATE_INSCRIPTION= 'date_inscription' ;
	const USERS_DATE_LASTCON	= 'date_last_connexion' ;
	const USERS_DATE_LASTACTION = 'date_last_action' ;
	const USERS_DECONX_TIME		= 'deconx_time' ;



	private $hide_datas ;                               // tableau contenant les champs qu'on ne peut modifier ds la BDD
	private $ID_user ;									// ID de l'utilisateur
	private $login  ;									// login (= identifiant de l'user, quand il se loggue)
	private $pseudo  ;									// pseudo (= nom interne de l'user, UNIQUE en BDD aussi)
	private $infos  ;									// instance de Infos (pour récup et update)
	private $infosArrays = array(						// Description (liste) des infos qui doivent être des array()
		Users::USERS_MY_PROJECTS,
		Users::USERS_COMPETENCES,
		Users::USERS_MY_SEQS,
		Users::USERS_MY_ASSETS,
		Users::USERS_MY_SHOTS,
		Users::USERS_MY_TAGS,
		Users::USERS_MY_MSGS
	);


	public function __construct ($ident = 'new', $typeSearch = Users::USERS_LOGIN) {
		$this->hide_datas = array ( "passwd", "date_inscription", "date_last_connexion", "date_last_action") ;
		$this->infos = new Infos( TABLE_USERS ) ;
		if ( $ident == 'new' ) return ;
		// si un LOGIN ou un ID est specifié, on lit l'enregistrement dans la base de données
		if (is_int($ident)) {
			$this->ID_user = $ident;
			$this->loadFromBD( Users::USERS_ID , $this->ID_user ) ;
			$this->login = $this->infos->getInfo(Users::USERS_LOGIN);
		}
		else {
			if ($typeSearch == Users::USERS_LOGIN) {
				$this->login = $ident ;
				$this->loadFromBD( Users::USERS_LOGIN , $this->login ) ;
				$this->pseudo = $this->infos->getInfo(Users::USERS_PSEUDO);
			}
			elseif ($typeSearch == Users::USERS_PSEUDO) {
				$this->pseudo = $ident;
				$this->loadFromBD( Users::USERS_PSEUDO , $this->pseudo ) ;
				$this->login = $this->infos->getInfo(Users::USERS_LOGIN);
			}
			$this->ID_user = $this->infos->getInfo(Users::USERS_ID);
		}
	}

	// Charge les infos d'un user
	public function loadFromBD ( $keyFilter , $value ) {
		try { $this->infos->loadInfos( $keyFilter, $value ); }
		catch (Exception $e) { throw new Exception(Users::INFO_ERREUR.' pour : '.$keyFilter.' = '.$value); }

	}

																	// GETTERS  //////////////////////////////////////

	// Retourne une valeur de l'objet Infos
	public function getUserInfos ($what='') {
		if ($what == '') {
			try { $info = $this->infos->getInfo(); }							// Récup toutes les infos dans la BDD
			catch (Exception $e) { return $e->getMessage(); }
			unset($info['passwd']);												// Pour virer le pass de la liste d'infos (il s'agit d'une info spécifique à Users, bien sûr)
		}
		elseif ($what == 'passwd') {
			return Users::INFO_FORBIDDEN;										// Pour être sûr que le pass ne puisse pas être récupéré ;)
		}
		else {
			try { $info = $this->infos->getInfo($what); }						// Récup une seule info
			catch (Exception $e) { return $e->getMessage(); }					// Si existe pas, récup de l'erreur
		}
		return $info;
	}

	public function getCompetences () {
		return json_decode($this->infos->getInfo(Users::USERS_COMPETENCES));
	}
	public function getUserProjects () {
		$usrP = json_decode($this->infos->getInfo(Users::USERS_MY_PROJECTS));
		if (!is_array($usrP))
			$usrP = Array(1);
		return $usrP;
	}
	public function getUserSequences () {
		return json_decode($this->infos->getInfo(Users::USERS_MY_SEQS));
	}
	public function getUserShots () {
		return json_decode($this->infos->getInfo(Users::USERS_MY_SHOTS));
	}
	public function getUserScenes () {
		return json_decode($this->infos->getInfo(Users::USERS_MY_SCENES));
	}
	public function getUserTags ($finalFilter=false) {
		$tagsList = json_decode($this->infos->getInfo(Users::USERS_MY_TAGS));
		if ($finalFilter && is_array($tagsList)) {
			foreach($tagsList as $k=>$tag) {
				if (!preg_match('/^#FT_/', $tag))
					unset($tagsList[$k]);
			}
		}
		return $tagsList;
	}

	// retourne la liste des messages de l'user
	public function getUserMsgs () {
		return json_decode($this->infos->getInfo(Users::USERS_MY_MSGS));
	}

	// retourne la liste des assets de l'user
	public function getUserAssets ($type='ID') {
		$userAssetsID = json_decode($this->infos->getInfo(Users::USERS_MY_ASSETS));
		if (!is_array($userAssetsID))
			return array();
		if ($type == 'ID')
			return $userAssetsID;
		elseif ($type == 'name') {
			$userAssets = array();
			foreach($userAssetsID as $idAsset) {
				$a = new Assets(false, (int)$idAsset);
				$userAssets[$idAsset] = $a->getName();
			}
			return $userAssets;
		}
	}

	// Retourne un tableau des local path des projets de l'utilisateur
	public function getUserProjectPath () {
		$EPpaths = json_decode($this->infos->getInfo(Users::USERS_PROJECTS_PATH), true);
		if (is_array($EPpaths)) {
			foreach($EPpaths as $pid => $path) {
				if ($path == '') unset($EPpaths[$pid]);
			}
		}
		else $EPpaths = Array();
		return $EPpaths;
	}

	// Retourne un tableau des url des projets de l'utilisateur
	public function getUserProjectUrl () {
		$EPuri = json_decode($this->infos->getInfo(Users::USERS_PROJECTS_URL), true);
		if (is_array($EPuri)) {
			foreach($EPuri as $pid => $url) {
				if ($url == '') unset($EPuri[$pid]);
			}
		}
		return (is_array($EPuri)) ? $EPuri : Array();
	}

	// retourne le password de l'user, en utilisant le code ci-dessous
	public function getPassword ($authToGetPassword = false) {
		if ($authToGetPassword != md5('YawollJesuiSbiEnMoIMêm')) return 'noPass';
		return $this->infos->getInfo(Users::USERS_PASSWORD);
	}

	// check si l'user a un projet dans ses my_projects
	public function hasProject($idProj=false) {
		if (!$idProj) { throw new Exception ('Users::hasProject() : missing $idProj !'); return; }
		$usrProjs = $this->getUserProjects();
		if (in_array($idProj, $usrProjs))
			return true;
		else return false;
	}

	// Verifie si la syntaxe d'un email est valide
	public function checkEmail ( $addresse ) {
		if (count($addresse) == 0) return false ;
		$validMail = filter_var($addresse, FILTER_VALIDATE_EMAIL);
		return $validMail;
	}

	// Check si l'user a le niveau ROOT
	public function isRoot () {
		if ($this->infos->getInfo(Users::USERS_STATUS) >= Users::USERS_STATUS_ROOT) {
			return true;
		}
		else return false;
	}
	// Check si l'user a le niveau DEV
	public function isDev () {
		if ($this->infos->getInfo(Users::USERS_STATUS) >= Users::USERS_STATUS_DEV) {
			return true;
		}
		else return false;
	}
	// Check si l'user a le niveau MAGIC
	public function isMagic () {
		if ($this->infos->getInfo(Users::USERS_STATUS) >= Users::USERS_STATUS_MAGIC) {
			return true;
		}
		else return false;
	}
	// Check si l'user a le niveau SUPERVISEUR
	public function isDirProd () {
		if ($this->infos->getInfo(Users::USERS_STATUS) >= Users::USERS_STATUS_DIRPROD) {
			return true;
		}
		else return false;
	}
	// Check si l'user a le niveau SUPERVISEUR
	public function isSupervisor () {
		if ($this->infos->getInfo(Users::USERS_STATUS) >= Users::USERS_STATUS_SUPERVISOR) {
			return true;
		}
		else return false;
	}
	// Check si l'user a le niveau LEAD
	public function isLead () {
		if ($this->infos->getInfo(Users::USERS_STATUS) >= Users::USERS_STATUS_LEAD) {
			return true;
		}
		else return false;
	}
	// Check si l'user a le niveau ARTIST
	public function isArtist () {
		if ($this->infos->getInfo(Users::USERS_STATUS) >= Users::USERS_STATUS_ARTIST) {
			return true;
		}
		else return false;
	}
	// Check si l'user a le niveau DEMO (ET SEULEMENT le status demo)
	public function isDemo () {
		if ($this->infos->getInfo(Users::USERS_STATUS) == Users::USERS_STATUS_DEMO) {
			return true;
		}
		else return false;
	}
	// Check si l'user a le niveau VISITOR
	public function isVisitor () {
		if ($this->infos->getInfo(Users::USERS_STATUS) == Users::USERS_STATUS_VISITOR) {
			return true;
		}
		else return false;
	}
	// Vérifie si un champ doit être un array
	public function row_must_be_array($row) {
		if (in_array($row, $this->infosArrays)) return true;
		else return false;
	}

																	// SETTERS  //////////////////////////////////////

	// ajoute / modifie une info et la sauvegarde directement
	public function updateInfo ($typeInfo, $newInfo) {
		if (count($newInfo) == 0) return Users::UPDATE_ERROR_DATA ;
		if ($typeInfo == 'pseudo') {
			if ($this->checkEmail($newInfo) === false) return Users::UPDATE_ERROR_DATA ;
			$_SESSION[COOKIE_NAME_LOG] = $newInfo;
			setcookie(COOKIE_NAME_LOG, $newInfo, COOKIE_PEREMPTION, "/");
		}
		elseif ($typeInfo == 'password') {
			$newInfo = md5(SALT_PASS.$newInfo);
			$_SESSION[COOKIE_NAME_PASS] = $newInfo;
			setcookie(COOKIE_NAME_PASS, $newInfo, COOKIE_PEREMPTION, "/");
		}
		try { $this->infos->update($typeInfo, $newInfo); return "Mise à jour de $typeInfo effectuée !"; }
		catch (Exception $e) { return $e->getMessage(); }
	}


	// Ajoute / modifie une info de l'user, peu importe laquelle
	public function setUserInfos ( $key, $value ) {
		if ($key==Users::USERS_PASSWORD) {
			if ( $this->setPassword($value) == false ) throw new Exception('mot de passe impossible à sauver !');
			return;
		}
		$this->infos->addInfo ( $key, $value ) ;
	}


	// Ajoute / modifie une info de l'user, peu importe laquelle, ET check si c'est un array
	public function setValue( $row, $val){
		if ($row == Users::USERS_PASSWORD)
			$this->setPassword ($val);
		elseif ($row == Users::USERS_MY_PROJECTS) {
			if (empty($val)) $val = array();
			$this->setMyProjects ($val);
		}
		else {
			if (is_array($val))
				$val = json_encode($val);
			$this->infos->addInfo ( $row, $val );
		}
		return Users::USERS_OK;
	}
	public function setCreator( $creator ){
		$this->infos->addInfo ( Users::USERS_CREATOR , $creator );
		return Users::USERS_OK;
	}
	public function setLogin( $login ){
		$this->infos->addInfo ( Users::USERS_LOGIN , $login );
		return Users::USERS_OK;
	}
	public function setPassword ( $password ) {
		if ( strlen ($password) < 4  ) { throw new Exception (Users::USERS_ERROR_PASSSHORT) ; return; }
		$crypt = md5(SALT_PASS.$password);
		$this->infos->addInfo ( Users::USERS_PASSWORD, $crypt );
		return Users::USERS_OK;
	}
	public function setStatus ( $status ){
		$this->infos->addInfo ( Users::USERS_STATUS , $status );
		return Users::USERS_OK;
	}
	public function setPseudo ( $pseudo ){
		$this->infos->addInfo ( Users::USERS_PSEUDO , $pseudo );
		return Users::USERS_OK;
	}
	public function setPrenom ( $prenom ){
		$this->infos->addInfo ( Users::USERS_PRENOM , $prenom );
		return Users::USERS_OK;
	}
	public function setNom ( $nom ){
		$this->infos->addInfo ( Users::USERS_NOM , $nom );
		return Users::USERS_OK;
	}
	public function setmail ( $mail ){
		if ( ! $this->checkEmail ($mail) ) return Users::USERS_ERROR ;
		$this->infos->addInfo ( Users::USERS_MAIL, $mail );
		return Users::USERS_OK;
	}
	public function setCompetences ( $competences ) {
		$this->infos->addInfo ( Users::USERS_COMPETENCES , $competences );
		return Users::USERS_OK;
	}
	public function setLang ( $lang ) {
		$this->infos->addInfo ( Users::USERS_LANG , $lang );
		return Users::USERS_OK;
	}
	public function setTheme ( $theme ) {
		$this->infos->addInfo ( Users::USERS_THEME , $theme );
		return Users::USERS_OK;
	}
	public function setLastCon ( $lastCon ) {
		$this->infos->addInfo ( Users::USERS_DATE_LASTCON , $lastCon );
		return Users::USERS_OK;
	}
	public function setLastAction ( $lastAction ) {
		$this->infos->addInfo ( Users::USERS_DATE_LASTACTION , $lastAction );
		return Users::USERS_OK;
	}

	public function addProjectPath($pID, $path) {
		$EPpaths = json_decode($this->infos->getInfo(Users::USERS_PROJECTS_PATH), true);
		if (is_array($EPpaths)) {
			$EPpaths[$pID] = $path;
			$this->infos->addInfo ( Users::USERS_PROJECTS_PATH , json_encode($EPpaths, JSON_UNESCAPED_SLASHES) );
		}
		else $this->infos->addInfo ( Users::USERS_PROJECTS_PATH, '{"'.$pID.'":"'.$path.'"}');
		return Users::USERS_OK;
	}

	public function addProjectUrl($pID, $url) {
		$EPuri = json_decode($this->infos->getInfo(Users::USERS_PROJECTS_URL), true);
		if (is_array($EPuri)) {
			$EPuri[$pID] = $url;
			$this->infos->addInfo ( Users::USERS_PROJECTS_URL , json_encode($EPuri, JSON_UNESCAPED_SLASHES) );
		}
		else $this->infos->addInfo ( Users::USERS_PROJECTS_URL, '{"'.$pID.'":"'.$url.'"}');
		return Users::USERS_OK;
	}


																				//////////////////////////////////////

	// Set de la liste des my_projects
	public function setMyProjects ( $my_projects ) {
		if (!is_array($my_projects)) { throw new Exception ('Users::setMyProjects() : $my_projects is not an array !'); return Users::USERS_ERROR; }
		$this->infos->addInfo ( Users::USERS_MY_PROJECTS , json_encode($my_projects) );
		return Users::USERS_OK;
	}
	// Set de la liste des my_shots
	public function setMyShots ( $my_shots ) {
		if (!is_array($my_shots)) { throw new Exception ('Users::setMyShots() : $my_shots is not an array !'); return Users::USERS_ERROR; }
		$this->infos->addInfo ( Users::USERS_MY_SHOTS , json_encode($my_shots) );
		return Users::USERS_OK;
	}
	// Set de la liste des my_scenes
	public function setMyScenes ($my_scenes) {
		if (!is_array($my_scenes)) { throw new Exception ('Users::setMyScenes() : $my_scenes is not an array !'); return Users::USERS_ERROR; }
		$this->infos->addInfo ( Users::USERS_MY_SCENES, json_encode($my_scenes) );
		return Users::USERS_OK;
	}
	// Set de la liste des my_assets
	public function setMyAssets ($my_assets) {
		if (!is_array($my_assets)) { throw new Exception ('Users::setMyAssets() : $my_assets is not an array !'); return Users::USERS_ERROR; }
		$this->infos->addInfo ( Users::USERS_MY_ASSETS, json_encode($my_assets) );
		return Users::USERS_OK;
	}
																				//////////////////////////////////////

	// Ajouter un seul projet à la liste des my_projects
	public function addProjectToUser ($idProj) {
		$myProjs = json_decode($this->infos->getInfo(Users::USERS_MY_PROJECTS));
		if (is_array($myProjs)) {
			if (!in_array($idProj, $myProjs))
				array_push($myProjs, $idProj);
			$this->infos->addInfo ( Users::USERS_MY_PROJECTS , json_encode($myProjs) );
		}
		else $this->infos->addInfo ( Users::USERS_MY_PROJECTS, '["'.$idProj.'"]');
		return Users::USERS_OK;
	}
	// Ajouter un seul shot à la liste des my_shots
	public function addShotToUser ($idShot) {
		$myShots = json_decode($this->infos->getInfo(Users::USERS_MY_SHOTS));
		if (is_array($myShots)) {
			if (!in_array($idShot, $myShots))
				array_push($myShots, $idShot);
			$this->infos->addInfo ( Users::USERS_MY_SHOTS , json_encode(array_values($myShots)) );
		}
		else $this->infos->addInfo ( Users::USERS_MY_SHOTS, '["'.$idShot.'"]');
		return Users::USERS_OK;
	}
	// Ajouter une seule scène à la liste des my_scenes
	public function addSceneToUser ($idScene) {
		$myScenes = json_decode($this->infos->getInfo(Users::USERS_MY_SCENES));
		if (is_array($myScenes)) {
			if (!in_array($idScene, $myScenes))
				array_push($myScenes, $idScene);
			$this->infos->addInfo ( Users::USERS_MY_SCENES , json_encode(array_values($myScenes)) );
		}
		else $this->infos->addInfo ( Users::USERS_MY_SCENES, '["'.$idScene.'"]');
		return Users::USERS_OK;
	}
	// Ajouter un seul asset à la liste des my_assets
	public function addAssetToUser ($idAsset) {
		$myAssets = json_decode($this->infos->getInfo(Users::USERS_MY_ASSETS));
		if (is_array($myAssets)) {
			if (!in_array($idAsset, $myAssets))
				array_push($myAssets, $idAsset);
			$this->infos->addInfo ( Users::USERS_MY_ASSETS , json_encode(array_values($myAssets)) );
		}
		else $this->infos->addInfo ( Users::USERS_MY_ASSETS, '["'.$idAsset.'"]');
		return Users::USERS_OK;
	}

																				//////////////////////////////////////

	// effacer un shot de la liste des my_shots
	public function removeShotToUser ($idShot) {
		$myShots = json_decode($this->infos->getInfo(Users::USERS_MY_SHOTS));
		if (is_array($myShots)) {
			if (in_array($idShot, $myShots)) {
				$myShots = array_diff($myShots, array("$idShot"));
				$this->infos->addInfo ( Users::USERS_MY_SHOTS , json_encode(array_values($myShots)) );
			}
		}
		return Users::USERS_OK;
	}
	// effacer une scène de la liste des my_scenes
	public function removeSceneToUser ($idScene) {
		$myScenes = json_decode($this->infos->getInfo(Users::USERS_MY_SCENES));
		if (is_array($myScenes)) {
			if (in_array($idScene, $myScenes)) {
				$myScenes = array_diff($myScenes, array("$idScene"));
				$this->infos->addInfo ( Users::USERS_MY_SCENES , json_encode(array_values($myScenes)) );
			}
		}
		return Users::USERS_OK;
	}
	// effacer un asset de la liste des my_assets
	public function removeAssetToUser ($idAsset) {
		$myAssets = json_decode($this->infos->getInfo(Users::USERS_MY_ASSETS));
		if (is_array($myAssets)) {
			if (in_array($idAsset, $myAssets)) {
				$myAssets = array_diff($myAssets, array("$idAsset"));
				$this->infos->addInfo ( Users::USERS_MY_ASSETS , json_encode(array_values($myAssets)) );
			}
		}
		return Users::USERS_OK;
	}

																				//////////////////////////////////////

	// sauvegarde les données en BDD
	public function save() {
		// nouvel User ? création de la date d'inscription
		if (!$this->infos->getInfo(Users::USERS_DATE_INSCRIPTION))
			$this->infos->addInfo(Users::USERS_DATE_INSCRIPTION, time()) ;
		$this->infos->save();
		return Users::USERS_OK;
	}


	// Supprime l'utilisateur $id de la BDD
	public function delete ( $id ){
		if ( !$this->isSupervisor() ) {	throw new Exception ( Users::NO_HABILITATION ); return; }
		$nb = $this->infos->delete( Users::USERS_ID  , $id ) ;
		return $nb ;
	}

																				//////////////////////////////////////

	// Récupère le nom d'un uer en fonction de son ID
	public static function getUserName ($uid) {
		try {
			$ui = new Infos(TABLE_USERS);
			$ui->loadInfos(Users::USERS_ID, (int)$uid);
			$uname = $ui->getInfo(Users::USERS_PSEUDO);
			return $uname;
		}
		catch (Exception $e) { return false; }
	}

	// récupère TOUS les my_projects de TOUS les USERS
	public static function getAll_UsersProjects () {							// return array(
		$ul = new Liste();														//	'id_user1'
		$uMPlist = array();														//		=>array('id_proj1','id_proj2'...),
		foreach($ul->getListe(TABLE_USERS, 'id,my_projects') as $usr) {			//	'id_user2'
			$uMPlist[$usr['id']] = json_decode($usr['my_projects']);			//		=>array('id_proj2','id_proj34'...),
		}																		//	...
		return $uMPlist;														// )
	}

	// récupère tous les Users qui ont le(s) projet(s) spécifié
	// @param : $projects = (array) IDs de projets
	// @param : $level	  = (int) user status
	public static function getUsers_by_projects ($projects, $level) {
		if (!is_array($projects)) return false;
		if (!is_int((int)$level)) return false;
		$ul = new Liste();
		$customFiltre = "(`".Users::USERS_STATUS."` <= '$level') AND (";
		foreach($projects as $proj) {
			if ($proj == 1) continue;
			$customFiltre .= "(`".Users::USERS_MY_PROJECTS."` LIKE '%$proj%') OR ";
		}
		$customFiltre = trim($customFiltre, ' OR') . ')';
		$ul->setFiltreSQL($customFiltre);
		$ul->getListe(TABLE_USERS, Users::USERS_ID.', '.Users::USERS_PSEUDO.', '.Users::USERS_STATUS);
		$uPlist = $ul->simplifyList();
		return $uPlist;
	}

	// récupère TOUS les my_projects de TOUS les USERS
	public static function getAll_UsersShots () {
		$ul = new Liste();
		$uMSlist = array();
		foreach($ul->getListe(TABLE_USERS, 'id,my_shots') as $usr) {
			$uMSlist[$usr['id']] = json_decode($usr['my_projects']);
		}
		return $uMSlist;
	}

																				//////////////////////////////////////

	// Retourne un array des mails des users qui ont le même projet et/ou le même shot / asset / scene
	public static function getUsersMails ($projectID, $filterBy=false, $filterItem=false, $onlySup=false, $notification=false) {
		if (!$projectID) return Array();
		if ($projectID == '1')
			return explode(', ', ROOTS_MAILS);
		$statusFilter = ($onlySup) ? Users::USERS_STATUS_SUPERVISOR : Users::USERS_STATUS_ARTIST;
		$l = new Liste();
		if ($notification === true)
			$l->addFiltre(Users::USERS_RECEIVE_NOTIFS, '=', 1, 'AND');
		else
			$l->addFiltre(Users::USERS_RECEIVE_MAILS, '=', 1, 'AND');
		$l->addFiltre(Users::USERS_MAIL, '!=', '', 'AND');
		$l->addFiltre(Users::USERS_STATUS, '>=', $statusFilter, 'AND');
		$l->addFiltre(Users::USERS_MY_PROJECTS, 'LIKE', '%"'.$projectID.'"%', 'AND');
		if ($filterBy == 'shot')
			$l->addFiltre(Users::USERS_MY_SHOTS, 'LIKE', '%"'.$filterItem.'"%', 'AND');
		elseif ($filterBy == 'scene')
			$l->addFiltre(Users::USERS_MY_SCENES, 'LIKE', '%"'.$filterItem.'"%', 'AND');
		elseif ($filterBy == 'asset')
			$l->addFiltre(Users::USERS_MY_ASSETS, 'LIKE', '%"'.$filterItem.'"%', 'AND');
		$listeEmails = $l->getListe(TABLE_USERS, 'mail');
		if (!$listeEmails) return Array();
		return $listeEmails;
	}
																	// PURGES	//////////////////////////////////////

	// purge des my_projects
	public static function purge_user_projects ($idUser) {
		$u = new Users((int)$idUser);
		$userProjs	 = $u->getUserProjects();
		$userProjsOK = Array();
		if (is_array($userProjs)) {
			foreach($userProjs as $idProj) {
				$projExists = false; $projGotUser = false;
				try {
					$p = new Projects((int)$idProj);
					if ($p->isArchived()) throw new Exception('Projet inactif!');
					$team = $p->getEquipe('arrIDs');
					$projExists = true;
					if (@in_array($idUser, $team))
						$projGotUser = true;
				}
				catch (Exception $e) { $projExists = false; }
				if ($projExists && $projGotUser) {
					$userProjsOK[] = (string)$idProj;
				}
			}
		}
		else $userProjsOK = Array("1");
		$u->setMyProjects($userProjsOK);
		$u->save();
		return(count(array_diff($userProjs, $userProjsOK)));
	}

	// Purge des my_shots
	public static function purge_user_shots ($idUser) {
		$u = new Users((int)$idUser);
		$userShots	 = $u->getUserShots();
		$userShotsOK = Array();
		if (is_array($userShots)) {
			foreach($userShots as $idShot) {
				$shotExists = false; $shotGotUser = false;
				try {
					$s = new Shots((int)$idShot);
					if ($s->isArchived()) throw new Exception('Plan inactif!');
					$team = $s->getShotTeam('arrayIDs');
					$shotExists = true;
					if (@in_array($idUser, $team))
						$shotGotUser = true;
				}
				catch (Exception $e) { $shotExists = false; }
				if ($shotExists && $shotGotUser) {
					$userShotsOK[] = (string)$idShot;
				}
			}
		}
		else $userShots = Array();
		$u->setMyShots($userShotsOK);
		$u->save();
		return(count(array_diff($userShots, $userShotsOK)));
	}

	// Purge des my_scenes
	public static function purge_user_scenes ($idUser) {
		$u = new Users((int)$idUser);
		$userScenes	  = $u->getUserScenes();
		$userScenesOK = Array();
		if (!is_array($userScenes))
			return 0;
		foreach($userScenes as $idScene) {
			$sceneExists = false; $sceneGotUser = false;
			try {
				$sc = new Scenes((int)$idScene);
				if ($sc->isArchived()) throw new Exception('Scene inactive!');
				$sceneExists = true;
				if (in_array($idUser, $sc->getSceneTeam('arrayIDs')))
					$sceneGotUser = true;
			}
			catch (Exception $e) { $sceneExists = false; }
			if ($sceneExists && $sceneGotUser) {
				$userScenesOK[] = (string)$idScene;
			}
		}
		$u->setMyScenes($userScenesOK);
		$u->save();
		return(count(array_diff($userScenes, $userScenesOK)));
	}

	// Purge des my_assets
	public static function purge_user_assets ($idUser) {
		$u = new Users((int)$idUser);
		$userAssets	  = $u->getUserAssets();
		$userAssetsOK = Array();
		foreach($userAssets as $idAsset) {
			$assetExists = false; $assetGotUser = false;
			try {
				$a = new Assets(false, (int)$idAsset);
				$assetExists = true;
				if (in_array($idUser, $a->getTeamAsset('arrayIDs')))
					$assetGotUser = true;
			}
			catch (Exception $e) { $assetExists = false; }
			if ($assetExists && $assetGotUser) {
				$userAssetsOK[] = (string)$idAsset;
			}
		}
		$u->setMyAssets($userAssetsOK);
		$u->save();
		return(count(array_diff($userAssets, $userAssetsOK)));
	}

	// Iterator
	public function key()		{ return $this->infos->key();		}
	public function current()	{ return $this->infos->current();	}
	public function next()		{ $this->infos->next() ;			}
	public function rewind()	{ $this->infos->rewind() ;			}
	public function valid()		{
		while ($this->infos->valid()) {
			if ( in_array(  $this->infos->key() , $this->hide_datas) )
				$this->infos->next() ;
			else
				return true ;
		}
		return false ;
	}






}
?>
