<?php
require_once (INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php' );
require_once (INSTALL_PATH . FOLDER_FCT . 'directories.php');
require_once (INSTALL_PATH . FOLDER_FCT . 'xml_fcts.php');

class Assets {
	const OK		= true;
	const ERROR		= false;

	const ASSET_ID				= 'id' ;
	const ASSET_ID_PROJECTS		= 'ID_projects' ;
	const ASSET_CATEGORY		= 'category' ;
	const ASSET_NAME			= 'filename' ;
	const ASSET_PATH_REL			= 'path_relative' ;
	const ASSET_VERSION			= 'version' ;
	const ASSET_ID_SHOTS		= 'ID_shots' ;
	const ASSET_ID_CREATOR		= 'ID_creator' ;
	const ASSET_ID_HANDLER		= 'ID_handler' ;
	const ASSET_DESCRIPTION		= 'description' ;
	const ASSET_STEP			= 'step' ;
	const ASSET_REVIEW			= 'review' ;
	const ASSET_TEAM			= 'team' ;
	const ASSET_CUSTOM_ATTR		= 'custom_attr' ;
	const ASSET_DEADLINE		= 'deadline' ;
	const ASSET_DATE			= 'date' ;
	const ASSET_UPDATE			= 'update' ;
	const ASSET_UPDATED_BY		= 'updated_by' ;
	const ASSET_HIDE			= 'hide' ;
	const ASSET_RETAKE			= 'checkretake';
	const ASSET_PROGRESS		= 'progress' ;
	const ASSET_RELATIONS		= 'relations_assets' ;
	const ASSET_ARCHIVE			= 'archive' ;

	private $infos;					// Infos de l'asset chargé
	private $name_asset;			// Nom du fichier de l'asset
	private $ID_asset;				// ID de l'asset
	private $version_asset;			// N° de version de l'asset
	private $ID_projects;			// Liste des projets de l'asset
	private $ID_shots;				// Liste des shots de l'asset
	private $handler;				// ID de l'user qui a la main sur le fichier (hung by)
	private $path_asset;			// Chemin relatif (universel) de l'asset
	private $current_projectID;		// ID du projet actuel (sur lequel on travaille)
	private $assetDir	   = false;	// Répertoire de l'asset sur le SaAM
	private $assetDataDir = false;	// Chemin des datas de l'asset
	private $dirRetakes   = false;	// Chemin des retakes de l'asset


	public function __construct ($idProject=false, $name=false, $path=false) {
		if (!$name || $name == '') { throw new Exception ('Assets::construct() : asset\'s fileName or ID is missing.'); }
		$asset_exists = true;
		$getAssetByID = false;
		if (is_int($name)) {
			$this->ID_asset = $name;
			$getAssetByID = true;
		}
		else $this->name_asset = $name;

		$this->infos = new Infos( TABLE_ASSETS ) ;
		try {
			if ($getAssetByID) $this->infos->loadInfos( Assets::ASSET_ID, $this->ID_asset );
			else $this->infos->loadInfos( Assets::ASSET_NAME, $this->name_asset );
			$this->ID_asset			= $this->infos->getInfo(Assets::ASSET_ID);
			$this->version_asset	= $this->infos->getInfo(Assets::ASSET_VERSION);
			$this->ID_projects		= $this->infos->getInfo(Assets::ASSET_ID_PROJECTS);
			$this->ID_shots			= $this->infos->getInfo(Assets::ASSET_ID_SHOTS);
			$this->name_asset		= $this->infos->getInfo(Assets::ASSET_NAME);
			$this->path_asset		= $this->infos->getInfo(Assets::ASSET_PATH_REL);
			$this->current_projectID = $idProject;
			if (!$idProject) {
				$idP = json_decode($this->ID_projects);
				$this->current_projectID = $idP[0];
			}
			if ($idProject && $this->ID_projects != '["'.$idProject.'"]')
				throw new Exception ('asset <b>'.$this->name_asset.'</b> already exists in another project. Please <b>rename it</b>, or make a <b>derivative</b>.', 33);
		}
		catch (Exception $e) {
			if ($e->getCode() == 33) {
				throw new Exception($e->getMessage().' -> '.$name);
			}
			else $asset_exists = false;
		}
		if (!$asset_exists) {
			try {
				if (is_int($this->name_asset))
					{ throw new Exception ('Assets::construct(new asset) : if you wanted to create a new asset, its name can\'t be a number.'); }
				if (!$path || $path == '')
					{ throw new Exception ('Assets::construct(new asset) : if you wanted to create a new asset, $path is missing.'); }
				$this->path_asset = $path;
				$this->infos->addInfo(Assets::ASSET_NAME, $name);
				$this->infos->addInfo(Assets::ASSET_PATH_REL, $path);
				$this->infos->addInfo(Assets::ASSET_ID_CREATOR, $_SESSION['user']->getUserInfos(Users::USERS_ID));
				$this->infos->addInfo(Assets::ASSET_UPDATED_BY, $_SESSION['user']->getUserInfos(Users::USERS_ID));
				$this->infos->addInfo(Assets::ASSET_DATE, date('Y-m-d H:i:s'));
				if ($idProject)
					$this->infos->addInfo(Assets::ASSET_ID_PROJECTS, '["'.$idProject.'"]');
				$this->current_projectID = $idProject;
				$this->infos->save();
			}
			catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
		}
	}

																	// GETTERS  //////////////////////////////////////

	public function getInfo ($info) {
		return $this->infos->getInfo($info);
	}
	public function getAssetInfos () {
		return $this->infos->getInfo();
	}
	public function getIDasset() {
		return $this->ID_asset;
	}
	public function getVersion() {
		return $this->version_asset;
	}
	public function getName () {
		return $this->name_asset;
	}
	public function getCategory () {
		$categID = (int)$this->infos->getInfo(Assets::ASSET_CATEGORY);
		if ($categID == 0)
			return 'Uncategorized';
		$inf = new Infos(TABLE_CONFIG);
		$inf->loadInfos('version', SAAM_VERSION);
		$categs = json_decode($inf->getInfo('assets_categories'), true);
		return $categs[$categID];
	}
	public function getPath () {
		return $this->path_asset;
	}
	public function getProjects () {
		return json_decode($this->ID_projects);
	}
	public function getStatus($idProj, $idDept) {
		$deptInf = $this->getDeptsInfos($idProj, $idDept);
		if (isset($deptInf['assetStep'])) {
			return (int)$deptInf['assetStep'];
		}
		else return -1;
	}
	public function getReview() {
		return (string)$this->getInfo(Assets::ASSET_REVIEW);
	}
	public function getDependencies ($from='XML') {
		if ($from == 'XML') {
			$depArr = get_asset_libs_fromXML($this->current_projectID, $this->name_asset);
			if ($depArr)
				$this->updateDependencies($depArr);
		}
		elseif ($from == 'BDD')
			$depArr = json_decode($this->infos->getInfo(Assets::ASSET_RELATIONS));
		if (!is_array($depArr)) return Array();
		return $depArr;
	}
	public function getShots () {
		return json_decode($this->ID_shots);
	}
	public function getNbShots () {
		return count($this->getShots());
	}
	public function getScenes () {
		$l = new Liste();
		$l->addFiltre(Scenes::MASTER, '=', '0');
		$l->addFiltre(Scenes::ASSETS, 'LIKE', '%"'.$this->ID_asset.'"%');
		$scenes = $l->getListe(TABLE_SCENES, 'id');
		return (is_array($scenes)) ? $scenes : Array();
	}
	public function getNbScenes () {
		return count($this->getScenes());
	}
	public function getLastModifDate ($mode='format') {
		return SQLdateConvert($this->infos->getInfo(Assets::ASSET_UPDATE), $mode);
	}
	public function getHandler ($what='id') {
		$this->handler = (int)$this->infos->getInfo(Assets::ASSET_ID_HANDLER);
		if ($this->handler == 0)
			return false;
		if ($what == 'id')
			return $this->handler;
		else {
			$u = new Users($this->handler);
			return $u->getUserInfos($what);
		}
	}

	public function isActive() {
		if ($this->infos->getInfo(Assets::ASSET_HIDE) == '1')
			return false;
		if ($this->infos->getInfo(Assets::ASSET_ARCHIVE) == '1')
			return false;
		return true;
	}

	public function getDirAsset ($IDproj=false, $deptID=false) {
		if ($IDproj == false) { throw new Exception('Assets::getDirAsset() : $proj (current project ID) is missing !'); }
		$p = new Projects($IDproj);
		$this->assetDir = $p->getDirProject(). '/assets/' . preg_replace('#\./#', '', $this->path_asset);
		unset($p);
		if ($deptID !== false)
			$this->assetDir = $this->assetDir . $deptID.'/';
		return $this->assetDir;
	}
	public function getDirAssetDatas ($proj=false, $deptID=false) {
		if ($this->assetDir == false) {
			if ($proj == false) { throw new Exception('Assets::getDirAssetDatas() : $proj (current project ID) is missing !'); }
			$this->getDirAsset($proj, $deptID);
		}
		$this->assetDataDir = $this->assetDir. $this->name_asset.'_datas/';
		return $this->assetDataDir;
	}
	public function getDirRetakes($proj=false, $deptID=false) {
		if ($proj == false) { throw new Exception('Assets::getDirRetakes() : $proj (current project ID) is missing !'); }
		$this->getDirAsset($proj, $deptID);
		$this->dirRetakes = $this->assetDir. $this->name_asset.'_retakes/';
		return $this->dirRetakes;
	}
	public function getLastRetake () {
		if ($this->dirRetakes == false) { throw new Exception('Assets::getLastRetake() : unknown path... Please call getDirRetakes(id_project) first !'); }
		$lastR = $this->dirRetakes.'retake_0';
		if (file_exists(INSTALL_PATH.FOLDER_DATA_PROJ.$lastR))
			return $lastR;
		return false;
	}
	public function getRetakesList($projID=false, $deptID=false) {
		if (!$this->dirRetakes) {
			if (!$projID) { throw new Exception('Assets::getRetakesList() : $projID undefined !'); }
			if (!$deptID) { throw new Exception('Assets::getRetakesList() : $deptID undefined !'); }
			$this->getDirRetakes ($projID, $deptID);
		}
		$retakeList = array();
		foreach (glob(INSTALL_PATH.FOLDER_DATA_PROJ.$this->dirRetakes.'retake_*') as $retakeName)
				$retakeList[] = basename($retakeName);
		return array_reverse($retakeList, true);
	}

	public function getTeamAsset ($type='arrayIDs', $limit=30) {
		$listTeam = json_decode($this->infos->getInfo(Assets::ASSET_TEAM));
		$listTeamIds = (is_array($listTeam)) ? $listTeam : Array();
		if ($type == 'arrayIDs')
			return $listTeamIds;
		$listPseudo = Array();
		foreach ($listTeamIds as $idUser) {
			try {
				$u = new Users((int)$idUser);
				$listPseudo[] = $u->getUserInfos(Users::USERS_PSEUDO);
			}
			catch(Exception $e) {							// Si user pas trouvé,
				$k = array_search($idUser, $listTeamIds);	// on le vire de la liste
				array_splice($listTeamIds, $k, 1);			// et on save la team
				$this->setTeam($listTeamIds);
				$this->save();
			}
		}
		if ($type == 'arrayPseudos')
			return $listPseudo;
		if ($type == 'str')	{
			if (!is_array($listTeam)) return '';
			$pseudoTeam = implode(', ', $listPseudo);
			$retourTeam = substr($pseudoTeam, 0, $limit);
			if (strlen($retourTeam) < strlen($pseudoTeam)-2)
				$retourTeam .= '...';
		}
		return $retourTeam;
	}

	// Retourne l'array des infos de l'asset en fonction du département
	public function getDeptsInfos ($projID=false, $idDept='all') {
		if (!$projID) { throw new Exception ('Assets::getDeptsInfos() : $projID is missing!'); }
		$retour = array();
		if ($idDept == 'all') {
			$all = array_flip(Liste::getRows(TABLE_ASSETS_DEPTS));
			unset($all['id']); unset($all['ID_project']); unset($all['ID_asset']);
			foreach($all as $dept => $void) {
				try {
					$dptInf = new Infos( TABLE_ASSETS_DEPTS ) ;
					$dptInf->loadInfos( 'ID_asset', $this->ID_asset);
					$deptInf = $dptInf->getInfo($dept);
				}
				catch (Exception $e) { $retour = null; }
				$deptName = get_label_dept($dept);
				if (strlen(@$deptInf) != 0)
					$retour[$deptName] = json_decode($deptInf, true);
				$retakes = $this->getRetakesList($projID, $dept);
				$nRetake = count($retakes);
				if ($nRetake != 0)
					$retour[$deptName]['nRetake'] = $nRetake;
			}
		}
		else {
			try {
				$dptsInf = new Infos( TABLE_ASSETS_DEPTS ) ;
				$dptsInf->loadInfos( 'ID_asset', $this->ID_asset);
				$retour = json_decode($dptsInf->getInfo($idDept), true);
			}
			catch (Exception $e) { $retour = null; }
		}
		return $retour;
	}

	// Retourne TRUE si la dernière retake d'un dept est validée
	public function isValidLastRetake($projID=false, $deptID=false) {
		if (!$projID) { throw new Exception ('Assets::isValidLastRetake() : $projID is missing!'); }
		if (!$deptID) { throw new Exception ('Assets::isValidLastRetake() : $deptID undefined !'); }
		$deptInfos = $this->getDeptsInfos($projID, $deptID);
		if ($deptInfos != null) {
			if (isset($deptInfos['retake']) && $deptInfos['retake'] === true)
				return true;
			else return false;
		}
		else return false;
	}

																	// SETTERS  //////////////////////////////////////

	// ajoute / modifie une info
	public function setInfos ($infos) {
		try {
			if (!is_array($infos))
				throw new Exception ('$infos is not an array !');
			if (!$this->infos->is_loaded())
				$this->infos->loadInfos(Assets::ASSET_NAME, $infos[Assets::ASSET_NAME]);
			unset($infos[Assets::ASSET_NAME], $infos[Assets::ASSET_PATH_REL]);
			foreach($infos as $typeInfo => $newInfo) {
				if (is_array($newInfo))
					$newInfo = json_encode($newInfo);
				$this->infos->addInfo( $typeInfo, $newInfo );
			}
			$this->save();
			if (isset($infos[Assets::ASSET_TEAM]) && is_array($infos[Assets::ASSET_TEAM])) {
				$assetID = Liste::getMax(TABLE_ASSETS, Assets::ASSET_ID);
				foreach($infos[Assets::ASSET_TEAM] as $idUt) {
					$ut = new Users((int)$idUt);
					$ut->addAssetToUser($assetID);
					$ut->save();
					unset($ut);
				}
			}
		}
		catch (Exception $e) { throw new Exception ('Assets::SetInfos() error: '.$e->getMessage());}
	}

	// Renomme un asset en BDD
	public function renameAsset($newName=false) {
		if ($newName == false || $newName == '')
			throw new Exception ('Assets::renameAsset() : missing new name!');
		if (!$this->infos->is_loaded())
			throw new Exception ('Assets::renameAsset() : asset not loaded!');
		$this->infos->addInfo ( Assets::ASSET_NAME, $newName ) ;
	}

	// Change le path d'un asset en BDD
	public function changePath($newPath=false) {
		if ($newPath == false || $newPath == '')
			throw new Exception ('Assets::changePath() : missing new path!');
		if (!$this->infos->is_loaded())
			throw new Exception ('Assets::changePath() : asset not loaded!');
		$path = preg_replace('#(^[\.]+[/]+)|(^[\.]+)|(^[/]+)#', '', $newPath);
		$pathOK = (!preg_match('#/$#', $path)) ? './'.$path.'/' : './'.$path ;
		$this->infos->addInfo ( Assets::ASSET_PATH_REL, $pathOK ) ;
	}

	// Archive un asset
	public function archiveAsset () {
		$this->infos->addInfo ( Assets::ASSET_ARCHIVE, '1' ) ;
	}
	// Restaure un asset
	public function restoreAsset () {
		$this->infos->addInfo ( Assets::ASSET_ARCHIVE, '0' ) ;
	}
	// Hide un asset
  	public function setHide($value) {
		$this->infos->addInfo ( Assets::ASSET_HIDE, $value ) ;
	}
	// Valide un published
	public function setValidRetake($projID=false, $deptID=false, $state=null) {
		if (!$projID) { throw new Exception ('Assets::setValidRetake() : $projID is missing!'); }
		if (!$deptID) { throw new Exception ('Assets::setValidRetake() : $deptID is missing!'); }
		if (!is_bool($state)) { throw new Exception ('Assets::setValidRetake() : $state is not a boolean !'); }

		$oldDptInf = $this->getDeptsInfos($projID, $deptID);
		$infDpt = new Infos(TABLE_ASSETS_DEPTS);
		try { $infDpt->loadInfos('ID_asset', $this->ID_asset); }
		catch (Exception $e) { }
		if ($oldDptInf == null) {
			$infDpt->addInfo('ID_asset', $this->ID_asset);
			$infDpt->addInfo('ID_project', $projID);
			$bkp = array();
		}
		else $bkp = json_decode(stripslashes($infDpt->getInfo($deptID)), true);
		$bkp['retake'] = $state;
		$infDpt->addInfo($deptID, json_encode($bkp));
		$infDpt->save();
		$this->save();
	}
	public function setDescription ($newDescr) {
		$this->infos->addInfo(Assets::ASSET_DESCRIPTION, $newDescr);
	}
	public function setCategory ($idNewCateg) {
		if (!is_int($idNewCateg) && $idNewCateg == 0) { throw new Exception ('Assets::setCategory() : $idNewCateg is not an integer !'); }
		$this->infos->addInfo(Assets::ASSET_CATEGORY, $idNewCateg);
	}
	public function setStatus ($idProj=false, $deptID=false, $idNewStatus=false) {
		if (!$idProj) { throw new Exception ('Assets::setStatus() : $idProj is missing !'); }
		if (!$deptID) { throw new Exception ('Assets::setStatus() : $deptID is missing !'); }
		if (!is_int($idNewStatus)) { throw new Exception ('Assets::setStatus() : $idNewStatus is not an integer !'); }
		$this->setAssetDeptsInfos($idProj, $deptID, Array('assetStep'=>$idNewStatus));
	}
	public function setReview ($revComment=false) {
		if (!$revComment) { throw new Exception ('Assets::setReview() : $revComment is missing !'); }
		if ($revComment === true)
			$revComment = '';
		$this->infos->addInfo(Assets::ASSET_REVIEW, $revComment);
	}
	public function setHandler ($idNewHandler) {
		if (!is_int($idNewHandler)) { throw new Exception ('Assets::setHandler() : $idNewHandler is not an integer !'); }
		$this->infos->addInfo(Assets::ASSET_ID_HANDLER, $idNewHandler);
		// Ajout du handler à la team automatiquement
		$team = $this->getTeamAsset();
		if (!in_array($idNewHandler, $team) && $idNewHandler != 0) {
			$team[] = "$idNewHandler";
			$this->setTeam($team);
		}
	}
	public function setTeam ($newTeam) {
		if (!is_array($newTeam)) { throw new Exception ('Assets::setTeam(): $newTeam is not an array !'); }
		$l = new Liste();
		$l->addFiltre(Users::USERS_MY_PROJECTS, 'LIKE', '%"'.$this->current_projectID.'"%');
		$allUsers = $l->getListe(TABLE_USERS, 'id');
		if (!is_array($allUsers)) { throw new Exception ('Assets::setTeam(): $allUsers is not an array! p# '.$this->current_projectID); }
		foreach($allUsers as $idUser) {
			$u = new Users((int)$idUser);
			$u->removeAssetToUser($this->ID_asset);
			$u->save();
			unset($u);
		}
		foreach($newTeam as $idUt) {
			$ut = new Users((int)$idUt);
			$myAssets = $ut->getUserAssets();
			if (!in_array($this->ID_asset, $myAssets)) {
				$myAssets[] = (string)$this->ID_asset;
				$ut->setMyAssets($myAssets);
				$ut->save();
			}
			unset($ut);
		}
		$this->infos->addInfo(Assets::ASSET_TEAM, json_encode($newTeam));
	}

	// Met à jour les dépendances de l'asset
	public function updateDependencies ($depArr) {
		if (!is_array($depArr)) { throw new Exception ('Assets::updateDependencies() : $depArr is not an array !'); }
		$this->infos->addInfo(Assets::ASSET_RELATIONS, json_encode($depArr));
		$this->save(false);
		return true;
	}

	// Définit les infos de département d'un asset
	public function setAssetDeptsInfos ($idProj, $deptID, $newInfos) {
		if (!is_array($newInfos))
			$newInfos = json_decode($newInfos, true);

		try {
			$idpt = new Infos(TABLE_ASSETS_DEPTS);
			$idpt->loadInfos('ID_asset', $this->ID_asset);
			$deptInf = json_decode($idpt->getInfo($deptID), true);		// récup old infos
			if (is_array(@$deptInf))
				$newInfos = array_merge($deptInf, $newInfos);
		}
		catch (Exception $e) {
			$idpt = new Infos(TABLE_ASSETS_DEPTS);
			$idpt->addInfo('ID_asset', $this->ID_asset);
			$idpt->addInfo('ID_project', $idProj);
		}
		$idpt->addInfo($deptID, json_encode($newInfos));
		$idpt->save();
		$this->save();
	}

	// Met à jour la vignette de l'asset avec l'image de la dernière retake
	public function updateVignette ($IDproj=false) {
		if ($IDproj == false) { throw new Exception ('Shots::updateVignette(): $IDproj is missing!'); }
//		if ($deptID == false) { throw new Exception ('Shots::updateVignette(): $deptID is missing!'); }
		$this->getDirRetakes($IDproj);
		$vignette	= INSTALL_PATH.FOLDER_DATA_PROJ . $this->getDirAsset($IDproj).'vignette_'.$this->name_asset;
		$retake		= INSTALL_PATH.FOLDER_DATA_PROJ . $this->getLastRetake();
		if ($retake == false) { throw new Exception ('Shots::updateVignette(): no retake found to update vignette!'); }
		$retakeType = check_mime_type($retake);
		if (preg_match('/image/i', $retakeType)) {						// Si fichier de type image
			require_once('GarageImg.php');
			$image = new GarageImg();
			$image->load($retake);
			$image->resizeToHeight(150);
			$image->save($vignette.'.jpg', IMAGETYPE_JPEG);
		}
	}

	// SAUVEGARDE
	public function save($withDate=True) {
		if ($withDate) {
			$this->infos->addInfo(Assets::ASSET_UPDATE, date('Y-m-d H:i:s'));
			$this->infos->addInfo(Assets::ASSET_UPDATED_BY, $_SESSION['user']->getUserInfos(Users::USERS_ID));
		}
		$this->infos->save();
	}

	public static function getProjectAssets($idProj=false, $onlyActifs=false) {
		if ($idProj == false) { return array(); }
		$l = new Liste();
		$l->addFiltre(Assets::ASSET_ID_PROJECTS, 'LIKE', '%'.$idProj.'%');
		if ($onlyActifs) {
			$l->addFiltre(Assets::ASSET_ARCHIVE, '=', '0');
			$l->addFiltre(Assets::ASSET_HIDE, '=', '0');
		}
		$l->getListe(TABLE_ASSETS, '*', Assets::ASSET_UPDATE, 'DESC');
		return $l->simplifyList(Assets::ASSET_NAME);
	}

	public function delete() {
		try { $this->infos->delete(); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
		return Assets::OK;
	}

}

?>
