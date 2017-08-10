<?php

require_once (INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php' );
require_once ('directories.php');

class Scenes implements Iterator {

	const OK		= true;
	const ERROR		= false;

	const ID_SCENE			= 'id' ;
	const LABEL				= 'label' ;
	const TITLE				= 'title' ;
	const VERSION			= 'version' ;
	const ID_CREATOR			= 'ID_creator' ;
	const ID_HANDLER			= 'ID_handler' ;
	const ID_PROJECT		= 'ID_project' ;
	const SEQUENCES			= 'sequences' ;
	const SHOTS				= 'shots' ;
	const MASTER				= 'master' ;
	const DERIVATIVES		= 'derivatives' ;
	const ASSETS			= 'assets' ;
	const NB_FRAMES			= 'nb_frames' ;
	const FPS				= 'fps' ;
	const DESCRIPTION		= 'description' ;
	const SUPERVISOR			= 'supervisor' ;
	const LEAD				= 'lead' ;
	const TEAM				= 'equipe' ;
	const REVIEW			= 'review';
	const DATE				= 'date' ;
	const UPDATE			= 'update' ;
	const DEADLINE			= 'deadline' ;
	const UPDATED_BY		= 'updated_by' ;
	const TAGS				= 'tags' ;
	const PROGRESS			= 'progress' ;
	const HIDE				= 'hide' ;
	const LOCK				= 'lock' ;
	const ARCHIVE			= 'archive' ;


	private $ID_scene;
	private $ID_project;
	private $infos;
	private $dir_Scene;
	private $dirRetakes;
	private $dirDatas;


	public function __construct ($ID_scene = 'new') {
		$this->infos = new Infos( TABLE_SCENES ) ;
		if ( $ID_scene == 'new' ) return ;
		$this->ID_scene = $ID_scene;
		try { $this->loadFromBD( Scenes::ID_SCENE , $this->ID_scene ); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
	}


	// Charge les infos
	public function loadFromBD ( $keyFilter , $value ) {
		try {
			$this->infos->loadInfos( $keyFilter, $value );
			$idProj = $this->infos->getInfo(Scenes::ID_PROJECT);
			$labelScene = $this->infos->getInfo(Scenes::LABEL);
			$this->ID_project = $idProj;
			$p = new Projects($idProj);
			$dirProj = $p->getDirProject();
			$this->dir_Scene = $dirProj.'/scenes/'.$labelScene;
			unset($p);
		}
		catch (Exception $e) { throw new Exception('Erreur: '.$e->getMessage().' pour : '.$keyFilter.' = '.$value); }
	}

																	// GETTERS  //////////////////////////////////////

	// Retourne une valeur de l'objet Infos
	public function getSceneInfos ($what='') {
		if ($what == Scenes::SUPERVISOR)		{ return $this->getSceneSupervisor(); }
		if ($what == Scenes::LEAD)			{ return $this->getSceneLead(); }
		if ($what == Scenes::TEAM)			{ return $this->getSceneTeam(); }
		if ($what == '') {
			try { $info = $this->infos->getInfo(); }							// Récup toutes les infos dans la BDD
			catch (Exception $e) { throw new Exception ($e->getMessage()); }
		}
		else {
			try { $info = $this->infos->getInfo($what); }						// Récup une seule info
			catch (Exception $e) { throw new Exception ($e->getMessage()); }
		}
		return $info;
	}

	public function getSceneSupervisor () {
		try {
			$sup = new Users((int)$this->infos->getInfo(Scenes::SUPERVISOR));
			return $sup->getUserInfos(Users::USERS_PSEUDO);
		} catch (Exception $e) { return L_NOBODY;}
	}

	public function getSceneLead () {
		try {
			$sup = new Users((int)$this->infos->getInfo(Scenes::LEAD));
			return $sup->getUserInfos(Users::USERS_PSEUDO);
		} catch (Exception $e) { return L_NOBODY;}
	}

	// Retourne la liste de l'équipe associée à la scène
	public function getSceneTeam ($type='arrayIDs', $limit=30) {
		$listTeam = json_decode($this->infos->getInfo(Scenes::TEAM));
		$listTeamIds = (is_array($listTeam)) ? $listTeam : Array();
		if ($type == 'arrayIDs')
			return $listTeamIds;
		$listPseudo = Array();
		foreach ($listTeamIds as $idUser) {
			$u = new Users((int)$idUser);
			$listPseudo[] = $u->getUserInfos(Users::USERS_PSEUDO);
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

	public function getSceneAssets($raw=false) {
		$master = $this->getMaster();
		// Si scène MASTER
		if ($master == 0 || $raw == true)
			$sceneAssets = json_decode($this->infos->getInfo(Scenes::ASSETS));
		// Si scène FILLE
		else {
			$scM = new Scenes($master);
			$sceneAssets = json_decode($scM->getSceneInfos(Scenes::ASSETS));
			$excludeAssets = json_decode($this->infos->getInfo(Scenes::ASSETS));
			if (is_array($excludeAssets))
				$sceneAssets = array_diff($sceneAssets, $excludeAssets);
		}
		if (is_array($sceneAssets)) return $sceneAssets;
		else return Array();
	}

	public function getStatus($idDept) {
		$deptInf = $this->getDeptsInfos($idDept);
		if (isset($deptInf['sceneStep'])) {
			return (int)$deptInf['sceneStep'];
		}
		else return -1;
	}
	public function getHandler ($what='id') {
		$this->handler = (int)$this->infos->getInfo(Scenes::ID_HANDLER);
		if ($this->handler == 0)
			return false;
		if ($what == 'id')
			return $this->handler;
		else {
			$u = new Users($this->handler);
			return $u->getUserInfos($what);
		}
	}

	// Retourne l'array des infos du shot en fonction du département
	public function getDeptsInfos ($idDept='all') {
		$retour = array();
		if ($idDept == 'all') {
			$all = array_flip(Liste::getRows(TABLE_SCENES_DEPTS));
			unset($all['id']); unset($all['ID_project']); unset($all['ID_scene']);
			foreach($all as $dept => $void) {
				try {
					$dptInf = new Infos( TABLE_SCENES_DEPTS ) ;
					$dptInf->loadInfos( 'ID_scene', $this->ID_scene);
					$deptInf = $dptInf->getInfo($dept);
				}
				catch (Exception $e) { }
				$deptName = get_label_dept($dept);
				if (strlen(@$deptInf) != 0)
					$retour[$deptName] = json_decode($deptInf, true);
				$retakes = $this->getRetakesList($dept);
				$nRetake = count($retakes);
				if ($nRetake != 0)
					$retour[$deptName]['nRetake'] = $nRetake;
			}
		}
		else {
			try {
				$dptsInf = new Infos( TABLE_SCENES_DEPTS ) ;
				$dptsInf->loadInfos( 'ID_scene', $this->ID_scene);
				$retour = json_decode($dptsInf->getInfo($idDept), true);
				$deptName = get_label_dept($idDept);
				$retakes = $this->getRetakesList($idDept);
				$nRetake = count($retakes);
				if ($nRetake != 0)
					$retour[$deptName]['nRetake'] = $nRetake;
			}
			catch (Exception $e) { $retour = null; }
		}
		return $retour;
	}

	// Retourne le chemin du dossier du shot pour un dept donné
	public function getDirScene () {
		return $this->dir_Scene;
	}

	// Retourne le chemin du dossier de retake en fonction du dept choisi
	public function getDirRetakes ($dept=false) {
		if (!$dept) { throw new Exception ('Scenes::getDirRetakes() : $dept undefined !'); return; }
		$this->dirRetakes = $this->dir_Scene.'/'.$dept.'/retakes';
		return $this->dirRetakes;
	}

	// Retourne le chemin du dossier de retake en fonction du dept choisi
	public function getDirSceneDatas ($dept=false) {
		if (!$dept) { throw new Exception ('Scenes::getDirSceneDatas() : $dept undefined !'); return; }
		$this->dirDatas = $this->dir_Scene.'/'.$dept.'/dataScene';
		return $this->dirDatas;
	}

	// Retourne la dernière retake en fonction du dept choisi
	public function getLastRetake ($dept=false) {
		if (!$dept) { throw new Exception ('Scenes::getLastRetake() : $dept undefined !'); return; }
		$lastRetake =  $this->dir_Scene.'/'.$dept.'/retakes/retake_0';
		if (file_exists(INSTALL_PATH.FOLDER_DATA_PROJ.$lastRetake))
			return $lastRetake;
		return false;
	}

	// Retourne TRUE si la scène est "active" (si pas hide, ni archive)
	public function isActive() {
		if ($this->getSceneInfos(Scenes::HIDE) || $this->getSceneInfos(Scenes::ARCHIVE))
			return false;
		return true;
	}

	// Retourne TRUE si la scène est BLOQUÉE
	public function isLocked() {
		if ($this->getSceneInfos(Scenes::LOCK))
			return true;
		return false;
	}

	// Retourne TRUE si la scène est ARCHIVÉE
	public function isArchived() {
		if ($this->infos->getInfo(Scenes::ARCHIVE) == 1) return true;
		else return false;
	}


	// Retourne TRUE si la dernière retake est enregistrée comme validée en BDD
	public function isValidLastRetake ($dept=false) {
		if (!$dept) { throw new Exception ('Scenes::isValidLastRetake() : $dept undefined !'); return; }
		$deptInfos = $this->getDeptsInfos($dept);
		if ($deptInfos != null) {
			if (isset($deptInfos['retake']) && $deptInfos['retake'] === true)
				return true;
			else return false;
		}
		else return false;
	}

	// Retourne les retakes pour une dept. choisi
	public function getRetakesList ($dept=false) {
		if (!$dept) { throw new Exception ('Scenes::getRetakesList() : $dept undefined !'); return; }
		$retakeList = array();
		$this->getDirRetakes ($dept);
		foreach (glob(INSTALL_PATH.FOLDER_DATA_PROJ.$this->dirRetakes.'/retake_*') as $retakeName)
				$retakeList[] = basename($retakeName);
		return array_reverse($retakeList, true);
	}

	// Retourne la liste des depts dans lesquels la scène est présente
	public function getSceneDepts ($by='label') {
		$listSceneDepts = array();
		$listAllDepts  = get_dpts();
		foreach ($listAllDepts as $idDept => $dept) {
			$dptInf = $this->getDeptsInfos($idDept);
			if ($dptInf != null) {
				if ($by == 'label')
					$listSceneDepts[$idDept] = $dept;
				elseif ($by == 'id')
					$listSceneDepts[$dept] = $idDept;
			}
		}
		return $listSceneDepts;
	}

	// Retourne le type de scène (master = 0, ou bien ID du master pour une dérivée)
	public function getMaster() {
		$master = $this->infos->getInfo(Scenes::MASTER);
		return (int)$master;
	}

	// Retourne la liste des dérivées, ou false si pas de dérivée
	public function getDerivatives($actives=false) {
		$l = new Liste();
		$l->addFiltre(Scenes::MASTER, '=', $this->ID_scene);
		if ($actives)
			$l->addFiltre(Scenes::ARCHIVE, '=', '0');
		$l->getListe(TABLE_SCENES);
		$filles = $l->simplifyList(Scenes::ID_SCENE);
		if ($filles === false) return Array();
		return $filles;
	}

	// Retourne la liste des sequences assignées à une master, ou LA séquence d'une fille (ID)
	public function getSequences () {
		$seqs = $this->infos->getInfo(Scenes::SEQUENCES);
		if ($this->getMaster() == 0)
			$seqsArr = json_decode($seqs, true);
		else
			$seqsArr = json_decode($seqs);
		if (is_array($seqsArr)) return $seqsArr;
		else return false;
	}

	// Retourne la liste des plans assignés à une master, ou LE plan d'une fille (ID)
	public function getShots () {
		$shots = $this->infos->getInfo(Scenes::SHOTS);
		if ($this->getMaster() == 0)
			$shotsArr = json_decode($shots, true);
		else
			$shotsArr = json_decode($shots);
		if (is_array($shotsArr)) return $shotsArr;
		else return false;
	}

	// Retourne la liste des cameras associées à la scène
	public function getCameras () {
		if ($this->getMaster() != 0) {		// FILLE : retourne les cams de la scène
			$l = new Liste();
			$l->addFiltre(Cameras::SCENE, '=', $this->ID_scene);
			$l->getListe(TABLE_CAMERAS);
			return $l->simplifyList(Cameras::ID_CAMERA);
		}
		else {								// MASTER : retourne toutes les cams des scènes filles : Array(fille=>[cams], ...)
			$camList = Array();
			foreach($this->getDerivatives(true) as $derivID=>$deriv) {
				$scF = new Scenes((int)$derivID);
				$camList[$derivID] = $scF->getCameras();
			}
			return $camList;
		}
	}


	// retourne le FPS du shot, ou celui du projet si = 0
	public function getSceneFPS () {
		$fps = $this->infos->getInfo(Scenes::FPS);
		$p = new Projects($this->ID_project);
		$projFPS = $p->getProjectInfos(Projects::PROJECT_FPS);
		if ($fps == 0) return $projFPS;
		return $fps ;
	}

	// Retourne le nombre de frames du shot
	public function getNbFrames () {
		return $this->infos->getInfo(Scenes::NB_FRAMES);
	}

	// Récupère la demande review en cours (si pas de review, retourne '')
	public function getReview() {
		return (string)$this->getInfo(Scenes::REVIEW);
	}

																	// SETTERS  //////////////////////////////////////

	// ajoute / modifie une info
	public function updateInfo ($typeInfo, $newInfo) {
		$this->infos->addInfo( $typeInfo, $newInfo );
		$this->save();
		return true;
	}

	// setter de valeur à déterminer
	public function setValue($champ, $value) {
		if (is_array($value)) $value = json_encode($value);
		if ($champ == Scenes::SUPERVISOR)	{ $this->setSupervisor($value); return; }
		if ($champ == Scenes::LEAD)			{ $this->setLead($value); return; }
		if ($champ == Scenes::TEAM)			{ $this->setTeam($value); return; }
		else $this->infos->addInfo ( $champ, $value ) ;
	}

	// ajoute / modifie des infos
	public function setInfos ($projID=false, $infos=false) {
		try {
			if (!$projID)			throw new Exception ('$projID is missing !');
			if (!is_array($infos))	throw new Exception ('$infos is not an array !');
			foreach($infos as $typeInfo => $newInfo) {
				if (is_array($newInfo))
					$newInfo = json_encode($newInfo);
				$this->infos->addInfo( $typeInfo, $newInfo );
			}
			if ($this->infos->is_loaded() == false) {
				$this->setCreator($_SESSION['user']->getUserInfos(Users::USERS_ID));
				$this->setIDproject($projID);
			}
			$this->save();
			if (isset($infos[Scenes::TEAM]) && is_array($infos[Scenes::TEAM])) {
				$sceneId = Liste::getMax(TABLE_SCENES, Scenes::ID_SCENE);
				foreach($infos[Scenes::TEAM] as $userId) {
					$ut = new Users((int)$userId);
					$ut->addSceneToUser($sceneId);
					$ut->save();
					unset($ut);
				}
			}
			return true;
		}
		catch (Exception $e) { throw new Exception ('Scenes::SetInfos() error: '.$e->getMessage()); }
	}

	// setters
 	public function setCreator($value) {
		$this->infos->addInfo ( Scenes::ID_CREATOR, $value ) ;
	}
 	public function setIDproject($value) {
		$this->infos->addInfo ( Scenes::ID_PROJECT, $value ) ;
	}
 	public function setIDsequence($value) {
		$this->infos->addInfo ( Scenes::ID_SEQUENCE, $value ) ;
	}
 	public function setTitle($value) {
		$this->infos->addInfo ( Scenes::TITLE, $value ) ;
	}
	public function setLabel($value) {
		$this->infos->addInfo ( Scenes::LABEL, $value ) ;
	}
	public function setPosition($value) {
		$this->infos->addInfo ( Scenes::POSITION, $value ) ;
	}
	public function setStatus ($idProj=false, $deptID=false, $idNewStatus=false) {
		if (!$idProj) { throw new Exception ('Scenes::setStatus() : $idProj is missing !'); return; }
		if (!$deptID) { throw new Exception ('Scenes::setStatus() : $deptID is missing !'); return; }
		if (!is_int($idNewStatus)) { throw new Exception ('Scenes::setStatus() : $idNewStatus is not an integer !'); return; }
		$this->setSceneDeptsInfos($deptID, Array('sceneStep'=>$idNewStatus));
	}
	public function setDescription($value) {
		$this->infos->addInfo ( Scenes::DESCRIPTION, $value ) ;
	}
	public function setSupervisor($value) {
		if ((int)$value == 0) {
			$sup = new Users((string)$value, Users::USERS_PSEUDO);
			$value = $sup->getUserInfos(Users::USERS_ID);
		}
		$this->infos->addInfo ( Scenes::SUPERVISOR, (int)$value ) ;
	}
 	public function setLead($value) {
		if ((int)$value == 0) {
			$sup = new Users((string)$value, Users::USERS_PSEUDO);
			$value = $sup->getUserInfos(Users::USERS_ID);
		}
		$this->infos->addInfo ( Scenes::LEAD, (int)$value ) ;
	}
 	public function setTeam($newTeam) {
		$sceneId = $this->getSceneInfos('id');
		$l = new Liste();
		$allUsers = $l->getListe(TABLE_USERS, 'id');
		foreach($allUsers as $idUser) {
			$u = new Users((int)$idUser);
			$u->removeSceneToUser($sceneId);
			$u->save();
			unset($u);
		}
		if (!is_array($newTeam))
			$newTeam = explode(",", $newTeam);
		$newTeamStr = '[';
		foreach($newTeam as $userId) {
			$ut = new Users((int)$userId);
			$ut->addSceneToUser($sceneId);
			$ut->save();
			unset($ut);
			$newTeamStr .= '"'.$userId.'",';
		}
		$newTeamStr = trim($newTeamStr, ',') . ']';
		$this->infos->addInfo ( Scenes::TEAM, $newTeamStr ) ;
	}
	public function setHandler ($idNewHandler) {
		if (!is_int($idNewHandler)) { throw new Exception ('Scenes::setHandler() : $idNewHandler is not an integer!'); return; }
		$this->infos->addInfo(Scenes::ID_HANDLER, $idNewHandler);
		// Ajout du handler à la team automatiquement
		$team = $this->getSceneTeam('arrayIDs');
		if (!in_array($idNewHandler, $team) && $idNewHandler != 0) {
			$team[] = "$idNewHandler";
			$this->setTeam($team);
		}
	}
 	public function setDate($value) {
		$this->infos->addInfo ( Scenes::DATE, $value ) ;
	}
 	public function setUpdate($value) {
		$this->infos->addInfo ( Scenes::UPDATE, $value ) ;
		$this->infos->addInfo ( Scenes::UPDATED_BY, $_SESSION['user']->getUserInfos(Users::USERS_ID) ) ;
	}
	public function setDeadline($value) {
		$this->infos->addInfo ( Scenes::DEADLINE, $value ) ;
	}
	public function setProgress($value) {
		$this->infos->addInfo ( Scenes::PROGRESS, $value ) ;
	}
  	public function setLock($value) {
		$this->infos->addInfo ( Scenes::LOCK, $value ) ;
	}
  	public function setHide($value) {
		$this->infos->addInfo ( Scenes::HIDE, $value ) ;
	}
	// Archive une scène
	public function archiveScene () {
		$this->infos->addInfo ( Scenes::ARCHIVE, '1' ) ;
	}
	// restaure une scène
	public function restoreScene () {
		$this->infos->addInfo ( Scenes::ARCHIVE, '0' ) ;
	}

	// Met à jour la vignette du shot (dans un dept donné) avec l'image de la dernière retake
	public function updateVignette ($labelDept=false) {
		if ($labelDept == false) { throw new Exception ('Scenes::updateVignette(): $labelDept is missing!'); return; }
		$idDept = get_ID_dept($labelDept, 'scenes');
		if ($idDept === false) $idDept = $labelDept;
		$vignette	= INSTALL_PATH.FOLDER_DATA_PROJ . $this->dir_Scene.'/'.$idDept.'/vignette';
		$retake		= INSTALL_PATH.FOLDER_DATA_PROJ . $this->getLastRetake($idDept);
		if ($retake == false) { throw new Exception ('Scenes::updateVignette(): no retake found to update vignette!'); return; }
		$retakeType = check_mime_type($retake);
		if (preg_match('/image/i', $retakeType)) {						// Si fichier de type image
			require_once('GarageImg.php');
			$image = new GarageImg();
			$image->load($retake);
			$image->resizeToHeight(150);
			$image->save($vignette.'.jpg', IMAGETYPE_JPEG);
		}
	}

	public function setSceneDepts ($newDeptsArr) {
		if (count($newDeptsArr) == 0) {
			$infDpt = new Infos(TABLE_SHOTS_DEPTS);
			try { $infDpt->loadInfos('ID_shot', $this->ID_shot); $infDpt->delete(); }
			catch (Exception $e) { }
			return;
		}
		$listAllDepts  = get_dpts();
		foreach($listAllDepts as $idDept => $nameDept) {
			$infDpt = new Infos(TABLE_SHOTS_DEPTS);
			try { $infDpt->loadInfos('ID_shot', $this->ID_shot); }
			catch (Exception $e) { }
			$oldDptInf = $this->getDeptsInfos($idDept);
			if (is_array($oldDptInf)) {
				$oldDptInf['fps'] = $this->getSceneFPS();
				$infDpt->addInfo($idDept, json_encode($oldDptInf));
			}
			else {
				$infDpt->addInfo('ID_project', $this->ID_project);
				$infDpt->addInfo('ID_shot', $this->ID_shot);
				$infDpt->addInfo($idDept, '{"fps":'.$this->getSceneFPS().'}');
			}
			if (!in_array($idDept, $newDeptsArr)) {
				$infDpt->addInfo($idDept, '');
			}
			$infDpt->save();
			unset($infDpt);
		}
	}

	// Définit les infos de département d'une scène
	public function setSceneDeptsInfos ($dept, $newInfos) {
		if (!is_array($newInfos))
			$newInfos = json_decode($newInfos, true);
		try {
			$idpt = new Infos(TABLE_SCENES_DEPTS);
			$idpt->loadInfos('ID_scene', $this->ID_scene);
			$deptInf = json_decode($idpt->getInfo($dept), true);
			if (is_array(@$deptInf))
				$newInfos = array_merge($deptInf, $newInfos);
		}
		catch (Exception $e) {
			$idpt = new Infos(TABLE_SCENES_DEPTS);
			$idpt->addInfo('ID_scene', $this->ID_scene);
			$idpt->addInfo('ID_project', $this->ID_project);
		}
		$idpt->addInfo($dept, json_encode($newInfos));
		$idpt->save();
//		$this->recalc_scene_progress();
		$this->save();
	}

	public function addDerivative ($idDeriv) {
		if (!is_int($idDeriv)) { throw new Exception('Scenes::addDerivative : $idDeriv not an integer!'); return; }
		$derivees = json_decode($this->infos->getInfo(Scenes::DERIVATIVES));
		if (is_array($derivees))
			array_push($derivees, $idDeriv);
		else
			$derivees = Array($idDeriv);
		$this->infos->addInfo ( Scenes::DERIVATIVES, json_encode($derivees) ) ;
		$this->save();
	}

	// définit l'état de la dernière retake (validée ou pas)
	public function setValidRetake ($idDept=false, $valid=null) {
		if (!$idDept) { throw new Exception ('Scenes::setValidRetake() : $idDept undefined !'); return; }
		if (!$valid === null) { throw new Exception ('Scenes::setValidRetake() : $valid undefined !'); return; }

		$oldDptInf = $this->getDeptsInfos($idDept);
		$infDpt = new Infos(TABLE_SCENES_DEPTS);
		try { $infDpt->loadInfos('ID_scene', $this->ID_scene); }
		catch (Exception $e) { }
		if ($oldDptInf == null) {
			$infDpt->addInfo('ID_scene', $this->ID_scene);
			$infDpt->addInfo('ID_project', $this->ID_project);
			$bkp = array();
		}
		else $bkp = json_decode(stripslashes($infDpt->getInfo($idDept)), true);
		$bkp['retake'] = $valid;
		$infDpt->addInfo($idDept, json_encode($bkp));
		$infDpt->save();
		$this->save();
	}

	// Définit une demande de review
	public function setReview($revComment=false) {
		if (!$revComment) { throw new Exception ('Scenes::setReview() : $revComment is missing !'); return; }
		if ($revComment === true)
			$revComment = '';
		$this->infos->addInfo(Scenes::REVIEW, $revComment);
	}

	// sauvegarde les données en BDD
	public function save($forceNoUp = 'avoid') {
		try {
			if ($forceNoUp != 'noUpdate')
				$this->setUpdate(date('Y-m-d H:i:s'));
			$this->infos->save();
		}
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
		return Scenes::OK ;
	}

	// DELETE de scene... Beware
    public function delete() {
		try { $this->infos->delete(); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
		return Scenes::OK ;
	}

																				//////////////////////////////////////

	// Assignation à un plan
	public function assignShot ($seq, $shot) {
		if ($this->getMaster() == 0) { throw new Exception('Scenes::assignShot : This scene is not a derivative!'); return; }
		if (!is_int($seq)) { throw new Exception('Scenes::assignShot : $seq not an integer!'); return; }
		if (!is_int($shot)) { throw new Exception('Scenes::assignShot : $shot not an integer!'); return; }

		$sh = new Shots($shot);
		$shotScene = (int)$sh->getShotScene();
		if ($shotScene != 0) {
			$scX = new Scenes($shotScene);
			$shotSceneTitle = $scX->getSceneInfos(Scenes::TITLE);
			throw new Exception('Warning!! Shot already assigned to: '.$shotSceneTitle);
			return;
		}

		$oldSeqs = $this->getSequences();
		if ($oldSeqs == false)
			$oldSeqs = Array();
		if (!in_array($seq, $oldSeqs))
			$oldSeqs[] = $seq;

		$oldShots = $this->getShots();
		if ($oldShots == false)
			$oldShots = Array();
		if (!in_array($shot, $oldShots))
			$oldShots[] = $shot;

		$this->infos->addInfo ( Scenes::SEQUENCES, json_encode($oldSeqs) ) ;
		$this->infos->addInfo ( Scenes::SHOTS, json_encode($oldShots) ) ;
		$this->save();

		$sh->setShotScene((int)$this->getSceneInfos(Scenes::ID_SCENE));

		Scenes::refreshMasterShots((int)$this->getSceneInfos(Scenes::ID_PROJECT), (int)$this->getMaster());
	}

	// Dé-Assignation d'un plan
	public function removeShot ($seq, $shot) {
		if ($this->getMaster() == 0) { throw new Exception('Scenes::removeShot : This scene is not a derivative!'); return; }
		if (!is_int($seq)) { throw new Exception('Scenes::removeShot : $seq not an integer!'); return; }
		if (!is_int($shot)) { throw new Exception('Scenes::removeShot : $shot not an integer!'); return; }


		$oldShots = $this->getShots();
		if(($key = array_search($shot, $oldShots)) !== false)
			unset($oldShots[$key]);
		$newShots = array_values($oldShots);

		$oldSeqs = $this->getSequences();
		if(($key = array_search($seq, $oldSeqs)) !== false) {
			$removeSeq = true;
			if (count($newShots) > 0) {
				foreach($newShots as $sid) {
					$sh = new Shots($sid);
					if ($sh->getShotInfos(Shots::SHOT_ID_SEQUENCE) == $seq)
						$removeSeq = false;
				}
			}
			if ($removeSeq) unset($oldSeqs[$key]);
		}
		$newSeqs = array_values($oldSeqs);

		$this->infos->addInfo ( Scenes::SEQUENCES, json_encode($newSeqs) ) ;
		$this->infos->addInfo ( Scenes::SHOTS, json_encode($newShots) ) ;
		$this->save();

		$sh = new Shots($shot);
		$sh->setShotScene(0);

		Scenes::refreshMasterShots((int)$this->getSceneInfos(Scenes::ID_PROJECT), (int)$this->getMaster());
	}

	// (ré-)Assignation des shots à la scène MASTER
	private static function refreshMasterShots($projID, $idScene) {
		if (!is_int($idScene)) { throw new Exception('Scenes::assignShotToMaster : $idFille not an integer!'); return; }
		$sc = new Scenes($idScene);
		if ($sc->getMaster() != 0) { throw new Exception('Scenes::assignShotToMaster : This scene is not a master!'); return; }
		$filles = $sc->getDerivatives(true);
		$newSeqs  = Array();
		$newShots = Array();
		foreach($filles as $scFid => $scF) {
			$filleSeqs  = json_decode($scF[Scenes::SEQUENCES]);
			$filleShots = json_decode($scF[Scenes::SHOTS]);
			if ($filleSeqs && $filleShots) {
				$newSeqs[$scFid]  = $filleSeqs;
				$newShots[$scFid] = $filleShots;
			}
		}
		$infos = Array(
			Scenes::SEQUENCES => json_encode($newSeqs),
			Scenes::SHOTS => json_encode($newShots)
		);
		$sc->setInfos($projID, $infos);
		$sc->save();
	}

																				//////////////////////////////////////

	// @TODO : progress des scenes
	public function recalc_scene_progress () {
		$deptsScene = $this->getDeptsInfos();
		if (count($deptsScene) == 0) {
			$this->setProgress(0);
			$this->save('noUpdate');
			return;
		}
		// On cherche toutes les étapes existantes dans chaque depts du projet
		$l = new Liste();
		$l->getListe(TABLE_DEPTS, 'label,etapes', 'position', 'ASC');
		$allDeptsList	= $l->simplifyList('label');
		$p = new Projects($this->ID_project);
		$projDepts = $p->getDeptsProject(false, 'label');
		$allEtapesDepts = Array();
		$nbAllEtapes	= count($deptsScene);
		foreach ($allDeptsList as $deptNom => $deptVals) {
			if (!in_array(strtolower($deptNom), $projDepts)) continue;
			$nbAllEtapesDept = count(json_decode($deptVals['etapes']));
			$nbAllEtapes += $nbAllEtapesDept;
			$allEtapesDepts[strtolower($deptNom)] = $nbAllEtapesDept + 1;	// +1 pour compenser le fait qu'il y a l'étape "validé" en plus
		}
		// On cherche les étapes pour ce shot
		$shotEtapes	  = Array();
		$nbSceneEtapes = 0;
		foreach ($deptsScene as $deptName => $deptInfo) {
			if (!isset($allEtapesDepts[strtolower($deptName)])) continue;
			$nSED = 1;
			if (isset($deptInfo['shotStep'])) {
				$nSED = $deptInfo['shotStep'];
				if ($nSED == 0) {											// step = 0 correspond à l'étape "validé"
					$nSED = $allEtapesDepts[strtolower($deptName)];
				}
			}
			$nbSceneEtapes += $nSED;
			$shotEtapes[strtolower($deptName)] = $nSED;
		}
		// On calcule le résultat
		$percent = (int)(($nbSceneEtapes / $nbAllEtapes) * 100);
		// On enregistre dans le shot
		$this->setProgress($percent);
		$this->save('noUpdate');

		$se = new Sequences($this->getSceneInfos(Scenes::ID_SEQUENCE));
		$se->recalc_sequence_progress();

		$pr = new Projects($this->ID_project);
		$pr->recalc_project_progress();
	}

																				//////////////////////////////////////

	// Retourne une liste de shot et leurs infos en fonction d'un tag
	public static function getScenesByTag ($tagName=false) {			// @TODO : getScenesByTag(), filtrer les shots résultants par ACL (own shot)
		if (!$tagName) return false;
		$l = new Liste();
		$l->getListe(TABLE_SHOTS, '*', Scenes::ID_PROJECT.'`, `'.Scenes::ID_SEQUENCE.'`, `'.Scenes::POSITION, 'ASC', Scenes::TAGS, 'LIKE', '%'.$tagName.'%');
		$listeScenes = $l->simplifyList(Scenes::ID_SHOT);

		return (count($listeScenes) > 0) ? $listeScenes : false;
	}


	public function key()		{ return $this->infos->key(); }
	public function current()	{ return $this->infos->current(); }
	public function next()		{ $this->infos->next() ; }
	public function rewind()	{ $this->infos->rewind() ; }
	public function valid()		{
		while ( $this->infos->valid() ){
			if ( in_array(  $this->infos->key() , $this->hide_datas) )
				$this->infos->next() ;
			else
				return true ;
		}
		return false ;
	}






}
?>
