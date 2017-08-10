<?php

require_once (INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php' );
require_once ('directories.php');

class Shots implements Iterator {

	const UPDATE_ERROR_DATA = 'donnee invalide' ;				// erreur si pseudo ou autre donnee ne correspond pas
	const UPDATE_OK			= 'donnee modifiee, OK !' ;			// message si une modif BDD a reussi
	const INFO_ERREUR		= 'Shots:: Impossible de lire les infos en BDD';        // erreur de la methode Infos::loadInfos()
	const INFO_DONT_EXIST   = 'donnee inexistante' ;			// erreur si champs inexistant dans BDD lors de rÃ©cup d'info
	const INFO_FORBIDDEN    = 'donnee interdite' ;				// erreur si info est une donnee sensible (ici, password)
	const SAVE_LOSS			= 'Champs manquants';				// erreur si il manques des donnees essentielles Ã  sauvegarder dans la BDD

	const SHOT_OK			= true ;					// retour general, si la fonction a marche
	const SHOT_ERROR			= false ;					// retour general, si la fonction n'a pas marche

	const SHOT_ID_SHOT		= 'id' ;
	const SHOT_ID_CREATOR	= 'ID_creator' ;
	const SHOT_ID_PROJECT	= 'ID_project' ;
	const SHOT_ID_SEQUENCE	= 'ID_sequence' ;
	const SHOT_ID_SCENE		= 'ID_scene' ;
	const SHOT_ID_CAMERA		= 'ID_camera' ;
	const SHOT_POSITION		= 'position' ;
	const SHOT_NB_FRAMES	= 'nbframes' ;
	const SHOT_TITLE		= 'title' ;
	const SHOT_LABEL			= 'label' ;
	const SHOT_DESCRIPTION	= 'description' ;
	const SHOT_SUPERVISOR	= 'supervisor' ;
	const SHOT_LEAD         = 'lead' ;
	const SHOT_TEAM         = 'equipe' ;
	const SHOT_DATE			= 'date' ;
	const SHOT_UPDATE		= 'update' ;
	const SHOT_UPDATED_BY	= 'updated_by' ;
	const SHOT_DEADLINE		= 'deadline' ;
	const SHOT_PROGRESS		= 'progress' ;
	const SHOT_HIDE			= 'hide' ;
	const SHOT_LOCK			= 'lock' ;
	const SHOT_ARCHIVE		= 'archive' ;
	const SHOT_REFERENCE	= 'reference' ;
	const SHOT_TAGS			= 'tags' ;
	const SHOT_FPS			= 'fps' ;


	private $ID_shot;
	private $ID_project;
	private $infos;
	private $dir_Shot;
	private $dirRetakes;


	public function __construct ($ID_shot = 'new') {
		$this->infos = new Infos( TABLE_SHOTS ) ;
		if ( $ID_shot == 'new' ) return ;
		$this->ID_shot = $ID_shot;
		try { $this->loadFromBD( Shots::SHOT_ID_SHOT , $this->ID_shot ); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
	}


	// Charge les infos
	public function loadFromBD ( $keyFilter , $value ) {
		try {
			$this->infos->loadInfos( $keyFilter, $value );
			$idProj    = $this->infos->getInfo(Shots::SHOT_ID_PROJECT);
			$this->ID_project = $idProj;
			$p = new Projects($idProj);
			$dirProj   = $p->getDirProject();
			$idSeq	   = $this->infos->getInfo(Shots::SHOT_ID_SEQUENCE);
			$s = new Sequences($idSeq);
			$labelSeq  = $s->getSequenceInfos(Sequences::SEQUENCE_LABEL);
			$labelShot = $this->infos->getInfo(Shots::SHOT_LABEL);
			$this->dir_Shot = $dirProj.'/sequences/'.$labelSeq.'/'.$labelShot;
			unset($p); unset($s);
		}
		catch (Exception $e) { throw new Exception(Shots::INFO_ERREUR.' : '.$e->getMessage().' pour : '.$keyFilter.' = '.$value); }
	}

																	// GETTERS  //////////////////////////////////////

	// Retourne une valeur de l'objet Infos
	public function getShotInfos ($what='') {
		if ($what == Shots::SHOT_SUPERVISOR)	{ return $this->getShotSupervisor(); }
		if ($what == Shots::SHOT_LEAD)			{ return $this->getShotLead(); }
		if ($what == Shots::SHOT_TEAM)			{ return $this->getShotTeam(); }
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

	public function getShotSupervisor () {
		try {
			$sup = new Users((int)$this->infos->getInfo(Shots::SHOT_SUPERVISOR));
			return $sup->getUserInfos(Users::USERS_PSEUDO);
		} catch (Exception $e) { return '';}
	}

	public function getShotLead () {
		try {
			$sup = new Users((int)$this->infos->getInfo(Shots::SHOT_LEAD));
			return $sup->getUserInfos(Users::USERS_PSEUDO);
		} catch (Exception $e) { return '';}
	}

	public function getShotSequenceInfo ($typeInfo=Sequences::SEQUENCE_LABEL) {
		$idSeq		= $this->infos[Shots::SHOT_ID_SEQUENCE];
		$se			= new Sequences((int)$idSeq);
		return $se->getSequenceInfos($typeInfo);
	}

	// Retourne l'array des infos du shot en fonction du département
	public function getDeptsInfos ($idDept='all') {
		$retour = array();
		if ($idDept == 'all') {
			$all = array_flip(Liste::getRows(TABLE_SHOTS_DEPTS));
			unset($all['id']); unset($all['ID_project']); unset($all['ID_shot']);
			foreach($all as $dept => $void) {
				try {
					$dptInf = new Infos( TABLE_SHOTS_DEPTS ) ;
					$dptInf->loadInfos( 'ID_shot', $this->ID_shot);
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
				$dptsInf = new Infos( TABLE_SHOTS_DEPTS ) ;
				$dptsInf->loadInfos( 'ID_shot', $this->ID_shot);
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

	// Retourne les infos de la camera associée au shot
	public function getCamera ($what = '') {
		$camID = (int)$this->infos->getInfo(Shots::SHOT_ID_CAMERA);
		if ($camID === 0) return false;
		$cam = new Cameras($camID);
		return $cam->getCameraInfos($what);
	}

	// Retourne le chemin du dossier du shot pour un dept donné
	public function getDirShot () {
		return $this->dir_Shot;
	}

	// Retourne le chemin du dossier de retake en fonction du dept choisi
	public function getDirRetakes ($dept=false) {
		if (!$dept) { throw new Exception ('Shots::getDirRetakes() : $dept undefined !'); return; }
		$this->dirRetakes = $this->dir_Shot.'/'.$dept.'/retakes';
		return $this->dirRetakes;
	}

	// Retourne la dernière retake en fonction du dept choisi
	public function getLastRetake ($dept=false) {
		if (!$dept) { throw new Exception ('Shots::getLastRetake() : $dept undefined !'); return; }
		$lastRetake =  $this->dir_Shot.'/'.$dept.'/retakes/retake_0';
		if (file_exists(INSTALL_PATH.FOLDER_DATA_PROJ.$lastRetake))
			return $lastRetake;
		return false;
	}

	// Retourne TRUE si le shot est "actif" (si pas hide, ni archive)
	public function isActive() {
		if ($this->getShotInfos(Shots::SHOT_HIDE) || $this->getShotInfos(Shots::SHOT_ARCHIVE))
			return false;
		return true;
	}

	// Retourne TRUE si le shot est BLOQUÉ
	public function isLocked() {
		if ($this->getShotInfos(Shots::SHOT_LOCK))
			return true;
		return false;
	}

	// Retourne TRUE si le shot est ARCHIVÉ
	public function isArchived() {
		if ($this->infos->getInfo(Shots::SHOT_ARCHIVE) == 1) return true;
		else return false;
	}

	// Retourne TRUE si la dernière retake est enregistrée comme validée en BDD
	public function isValidLastRetake ($dept=false) {
		if (!$dept) { throw new Exception ('Shots::isValidLastRetake() : $dept undefined !'); return; }
		$deptInfos = $this->getDeptsInfos($dept);
		if ($deptInfos != null) {
			if (isset($deptInfos['retake']) && $deptInfos['retake'] === true)
				return true;
			else return false;
		}
		else return false;
	}

	// Retourne la dernière retake en fonction du dept choisi
	public function getRetakesList ($dept=false) {
		if (!$dept) { throw new Exception ('Shots::getRetakesList() : $dept undefined !'); return; }
		$retakeList = array();
		$this->getDirRetakes ($dept);
		foreach (glob(INSTALL_PATH.FOLDER_DATA_PROJ.$this->dirRetakes.'/retake_*') as $retakeName)
				$retakeList[] = basename($retakeName);
		return array_reverse($retakeList, true);
	}

	// Retourne la liste de l'équipe associée au shot sous forme de chaine
	public function getShotTeam ($type='str', $limit=30) {
		$listTeam = json_decode($this->infos->getInfo(Shots::SHOT_TEAM));
		$listTeamIds = (is_array($listTeam)) ? $listTeam : Array();
		if ($type == 'arrayIDs')
			return $listTeamIds;
		$listPseudo = Array();
		foreach ($listTeamIds as $idUser) {
			try {
				$u = new Users((int)$idUser);
				$listPseudo[] = $u->getUserInfos(Users::USERS_PSEUDO);
			}
			catch(Exception $e) { continue; };
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

	// Retourne la liste des depts dans lesquels le shot est présent
	public function getShotDepts ($by='label') {
		$listShotDepts = array();
		$listAllDepts  = get_dpts();
		foreach ($listAllDepts as $idDept => $dept) {
			$dptInf = $this->getDeptsInfos($idDept);
			if ($dptInf != null) {
				if ($by == 'label')
					$listShotDepts[$idDept] = $dept;
				elseif ($by == 'id')
					$listShotDepts[$dept] = $idDept;
			}
		}
		return $listShotDepts;
	}

	// retourne le FPS du shot, ou celui du projet si = 0
	public function getShotFPS () {
		$fps = $this->infos->getInfo(Shots::SHOT_FPS);
		$p = new Projects($this->ID_project);
		$projFPS = $p->getProjectInfos(Projects::PROJECT_FPS);
		if ($fps == 0) return $projFPS;
		return $fps ;
	}

	// Retourne le nombre de frames du shot
	public function getNbFrames () {
		return $this->infos->getInfo(Shots::SHOT_NB_FRAMES);
	}


	// Récup l'ID de la scène associée
	public function getShotScene() {
		$scID = $this->infos->getInfo(Shots::SHOT_ID_SCENE);
		if ($scID == '0') return false;
		return (int)$scID;
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
		$val = $value;
		if (is_array($value)) $val = json_encode($value);
		if ($champ == Shots::SHOT_SUPERVISOR)	{ $this->setSupervisor($val); return; }
		if ($champ == Shots::SHOT_LEAD)			{ $this->setLead($val); return; }
		if ($champ == Shots::SHOT_TEAM)			{ $this->setTeam($value); return; }
		$this->infos->addInfo ( $champ, $val ) ;
	}


	// ajoute / modifie des infos
	public function setInfos ($projID=false, $infos=false) {
		try {
			if (!$projID) {
				$projID = $this->ID_project;
				if (!$projID)
					throw new Exception ('$projID is missing!');
			}
			if (!is_array($infos))
				throw new Exception ('$infos is not an array !');
			foreach($infos as $typeInfo => $newInfo) {
				$this->infos->addInfo( $typeInfo, $newInfo );
			}
			if ($this->infos->is_loaded() == false) {
				$this->infos->addInfo(Shots::SHOT_ID_CREATOR, $_SESSION['user']->getUserInfos(Users::USERS_ID));
				$this->infos->addInfo(Shots::SHOT_ID_PROJECT, $projID);
				$pMax = Liste::getMax(TABLE_SHOTS, Shots::SHOT_POSITION);
				$this->infos->addInfo(Shots::SHOT_POSITION, $pMax);
			}
			$this->save();
			return true;
		}
		catch (Exception $e) { throw new Exception ('Shots::SetInfos() error: '.$e->getMessage()); return false; }
	}

	// setters
 	public function setCreator($value) {
		$this->infos->addInfo ( Shots::SHOT_ID_CREATOR, $value ) ;
	}
 	public function setIDproject($value) {
		$this->infos->addInfo ( Shots::SHOT_ID_PROJECT, $value ) ;
	}
 	public function setIDsequence($value) {
		$this->infos->addInfo ( Shots::SHOT_ID_SEQUENCE, $value ) ;
	}
 	public function setTitle($value) {
		$this->infos->addInfo ( Shots::SHOT_TITLE, $value ) ;
	}
	public function setLabel($value) {
		$this->infos->addInfo ( Shots::SHOT_LABEL, $value ) ;
	}
	public function setPosition($value) {
		$this->infos->addInfo ( Shots::SHOT_POSITION, $value ) ;
	}
	public function setDescription($value) {
		$this->infos->addInfo ( Shots::SHOT_DESCRIPTION, $value ) ;
	}
	public function setSupervisor($value) {
		if ((int)$value == 0) {
			$sup = new Users((string)$value, Users::USERS_PSEUDO);
			$value = $sup->getUserInfos(Users::USERS_ID);
		}
		$this->infos->addInfo ( Shots::SHOT_SUPERVISOR, (int)$value ) ;
	}
 	public function setLead($value) {
		if ((int)$value == 0) {
			$sup = new Users((string)$value, Users::USERS_PSEUDO);
			$value = $sup->getUserInfos(Users::USERS_ID);
		}
		$this->infos->addInfo ( Shots::SHOT_LEAD, (int)$value ) ;
	}
 	public function setTeam($newTeam) {
		if (!is_array($newTeam))
			$newTeam = Array();
		$shotId = $this->getShotInfos('id');
		$l = new Liste();
		$l->addFiltre(Users::USERS_MY_PROJECTS, 'LIKE', '%"'.$this->ID_project.'"%');
		$allUsers = $l->getListe(TABLE_USERS, 'id');
		foreach($allUsers as $idUser) {
			$u = new Users((int)$idUser);
			$u->removeShotToUser($shotId);
			$u->save();
			unset($u);
		}
		foreach($newTeam as $idUt) {
			$ut = new Users((int)$idUt);
			$ut->addShotToUser($shotId);
			$ut->save();
			unset($ut);
		}
		$this->infos->addInfo ( Shots::SHOT_TEAM, json_encode($newTeam) ) ;
	}
 	public function setDate($value) {
		$this->infos->addInfo ( Shots::SHOT_DATE, $value ) ;
	}
 	public function setUpdate($value) {
		$this->infos->addInfo ( Shots::SHOT_UPDATE, $value ) ;
		$this->infos->addInfo ( Shots::SHOT_UPDATED_BY, $_SESSION['user']->getUserInfos(Users::USERS_ID) ) ;
	}
	public function setDeadline($value) {
		$this->infos->addInfo ( Shots::SHOT_DEADLINE, $value ) ;
	}
	public function setProgress($value) {
		$this->infos->addInfo ( Shots::SHOT_PROGRESS, $value ) ;
	}
  	public function setLock($value) {
		$this->infos->addInfo ( Shots::SHOT_LOCK, $value ) ;
	}
  	public function setHide($value) {
		$this->infos->addInfo ( Shots::SHOT_HIDE, $value ) ;
	}
	public function setReference($value) {
		$this->infos->addInfo ( Shots::SHOT_REFERENCE, $value ) ;
	}

	// Archive un shot
	public function archiveShot () {
		$this->infos->addInfo ( Shots::SHOT_ARCHIVE, '1' ) ;
	}
	// restaure un shot
	public function restoreShot () {
		$this->infos->addInfo ( Shots::SHOT_ARCHIVE, '0' ) ;
	}

	// Met à jour la vignette du shot (dans un dept donné) avec l'image de la dernière retake
	public function updateVignette ($labelDept=false) {
		if ($labelDept == false) { throw new Exception ('Shots::updateVignette(): $labelDept is missing!'); return; }
		$idDept = get_ID_dept($labelDept, 'shots');
		if ($idDept === false) $idDept = $labelDept;
		$vignette	= INSTALL_PATH.FOLDER_DATA_PROJ . $this->dir_Shot.'/'.$idDept.'/vignette';
		$retake		= INSTALL_PATH.FOLDER_DATA_PROJ . $this->getLastRetake($idDept);
		if ($retake == false) { throw new Exception ('Shots::updateVignette(): no retake found to update vignette!'); return; }
		$retakeType = check_mime_type($retake);
		if (preg_match('/image/i', $retakeType)) {						// Si fichier de type image
			require_once('GarageImg.php');
			$image = new GarageImg();
			$image->load($retake);
			$image->resizeToHeight(150);
			$image->save($vignette.'.jpg', IMAGETYPE_JPEG);
		}
	}

	public function setShotDepts ($newDeptsArr) {
		if (count($newDeptsArr) == 0) {
			$infDpt = new Infos(TABLE_SHOTS_DEPTS);
			try { $infDpt->loadInfos('ID_shot', $this->ID_shot); $infDpt->delete(); }
			catch (Exception $e) { }
			return;
		}
//		$oldDeptsArr = $this->getShotDepts('id');
		$listAllDepts  = get_dpts();
		foreach($listAllDepts as $idDept => $nameDept) {
			$infDpt = new Infos(TABLE_SHOTS_DEPTS);
			try { $infDpt->loadInfos('ID_shot', $this->ID_shot); }
			catch (Exception $e) { }
			$oldDptInf = $this->getDeptsInfos($idDept);
			if (is_array($oldDptInf)) {
				$oldDptInf['fps'] = $this->getShotFPS();
				$infDpt->addInfo($idDept, json_encode($oldDptInf));
			}
			else {
				$infDpt->addInfo('ID_project', $this->ID_project);
				$infDpt->addInfo('ID_shot', $this->ID_shot);
				$infDpt->addInfo($idDept, '{"fps":'.$this->getShotFPS().'}');
			}
			if (!in_array($idDept, $newDeptsArr)) {
				$infDpt->addInfo($idDept, '');
			}
			$infDpt->save();
			unset($infDpt);
		}
	}

	// Définit les infos de département d'un shot
	public function setShotDeptsInfos ($dept, $newInfos) {
		if (!is_array($newInfos))
			$newInfos = json_decode($newInfos, true);

		try {
			$idpt = new Infos(TABLE_SHOTS_DEPTS);
			$idpt->loadInfos('ID_shot', $this->ID_shot);
			$deptInf = json_decode($idpt->getInfo($dept), true);		// récup old infos
			if (is_array(@$deptInf))
				$newInfos = array_merge($deptInf, $newInfos);
		}
		catch (Exception $e) {
			$idpt = new Infos(TABLE_SHOTS_DEPTS);
			$idpt->addInfo('ID_shot', $this->ID_shot);
			$idpt->addInfo('ID_project', $this->ID_project);
		}
		$idpt->addInfo($dept, json_encode($newInfos));
		$idpt->save();
		$this->recalc_shot_progress();
		$this->save();
	}

	// définit l'état de la dernière retake (validée ou pas)
	public function setValidRetake ($labelDept=false,$valid=null) {
		if (!$labelDept) { throw new Exception ('Shots::setValidRetake() : $labelDept undefined !'); return; }
		if (!$valid === null) { throw new Exception ('Shots::setValidRetake() : $valid undefined !'); return; }

		$idDept = get_ID_dept($labelDept, 'shots');
		if ($idDept === false) $idDept = $labelDept;

		$oldDptInf = $this->getDeptsInfos($idDept);
		$infDpt = new Infos(TABLE_SHOTS_DEPTS);
		try { $infDpt->loadInfos('ID_shot', $this->ID_shot); }
		catch (Exception $e) { }
		if ($oldDptInf == null) {
			$infDpt->addInfo('ID_shot', $this->ID_shot);
			$infDpt->addInfo('ID_project', $this->ID_project);
			$bkp = array();
		}
		else $bkp = json_decode(stripslashes($infDpt->getInfo($idDept)), true);
		$bkp['retake'] = $valid;
		$infDpt->addInfo($idDept, json_encode($bkp));
		$infDpt->save();
		$this->save();
	}

	// SET l'ID de la scène associée
	public function setShotScene($scID) {
		$this->updateInfo(Shots::SHOT_ID_SCENE, $scID);
		return true;
	}

	// Assignation de camera à un shot de la scène fille
	public function setCamera ($camID) {
		if (!is_int($camID)) { throw new Exception('Shots::setCamera: $camID not an integer!'); return; }
		$oldCam = $this->getCamera('id');
		if ($camID == 0) {								// UNASSIGN: Remove shot infos from camera
			$cam = new Cameras((int)$oldCam);
			$seqID = 0; $shotID = 0;
		}
		else {											// ASSIGN: shot infos to camera
			$cam = new Cameras($camID);
			if ($cam->getShot('id') != '0') {			// Si camera déjà assignée à un autre shot, envoi de warning
				$shx = new Shots((int)$cam->getShot('id'));
				$oldShotTitle = $shx->getShotInfos(Shots::SHOT_TITLE);
				throw new Exception('Warning: This camera is already assigned to shot "'.$oldShotTitle.'"!');
				return;
			}
			if ($oldCam) {								// Si shot a déjà une caméra assignée, on la change
				$cleanCam = new Cameras((int)$oldCam);
				$cleanCam->setSequence(0);
				$cleanCam->setShot(0);
				$cleanCam->save();
			}
			$seqID  = (int)$this->getShotInfos(Shots::SHOT_ID_SEQUENCE);
			$shotID = (int)$this->getShotInfos(Shots::SHOT_ID_SHOT);
		}
		$cam->setSequence($seqID);
		$cam->setShot($shotID);
		$cam->save();
		$this->updateInfo(Shots::SHOT_ID_CAMERA, $camID);
	}

																				//////////////////////////////////////

	// sauvegarde les données en BDD
	public function save($forceNoUp = 'avoid') {
		try {
			if ($forceNoUp != 'noUpdate')
				$this->setUpdate(date('Y-m-d H:i:s'));
			$this->infos->save();
		}
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
		return Shots::SHOT_OK ;
	}

	// DELETE de shot... Beware
    public function delete() {
		try { $this->infos->delete(); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
		return Shots::SHOT_OK ;
	}


	public function recalc_shot_progress () {
		$deptsShot = $this->getDeptsInfos();
		if (count($deptsShot) == 0) {
			$this->setProgress(0);
			$this->save('noUpdate');
			return;
		}
		$l = new Liste();
		$l->getListe(TABLE_DEPTS, 'id,type,label,etapes', 'position', 'ASC', 'type', '=', 'shots');
		$allDeptsList = $l->simplifyList('label');
		$totalEtapes = 0; $totalEtapesShot = 0;
		foreach ($deptsShot as $deptNom => $deptVals) {
			if ($deptNom == 'dectech' || $deptNom == 'storyboard') continue;
			$deptId = @$allDeptsList[strtolower($deptNom)]['id'];
			if (!$deptId) continue;
			$nbEtapesDept = count(json_decode($allDeptsList[strtolower($deptNom)]['etapes'])) + 1;	// +1 pour compenser le fait qu'il y a l'étape "validé" en plus
			$totalEtapes += $nbEtapesDept;
			$etapeShot = 1;
			if (isset($deptVals['shotStep']))			// Si le dept a une étape définie pour ce shot
				$etapeShot = $deptVals['shotStep'];
			if ($etapeShot != 0 && count($this->getRetakesList($deptId)) == 0)
				continue;								// Si cette étape n'est pas la "validé", et qu'il n'y a aucun publish, on ne la compte pas
			if ($etapeShot == 0)						// Si c'est la validée, on compte comme le chiffre le plus grand
				$etapeShot = $nbEtapesDept;
			$totalEtapesShot += $etapeShot;
		}
		// On calcule le résultat
		$percent = (int)(($totalEtapesShot / $totalEtapes) * 100);
//		echo $totalEtapesShot.' / '.$totalEtapes.' x 100 = '.$percent.'%.';
		// On enregistre dans le shot
		$this->setProgress($percent);
		$this->save('noUpdate');
		$se = new Sequences($this->getShotInfos(Shots::SHOT_ID_SEQUENCE));
		$se->recalc_sequence_progress();
		$pr = new Projects($this->ID_project);
		$pr->recalc_project_progress();
	}

																			//////////////////////////////////////

	// Retourne une liste de shot et leurs infos en fonction d'un tag
	public static function getShotsByTag ($tagName=false) {			// @TODO : getShotsByTag(), filtrer les shots résultants par ACL (own shot)
		if (!$tagName) return false;
		$l = new Liste();
		$l->getListe(TABLE_SHOTS, '*', Shots::SHOT_ID_PROJECT.'`, `'.Shots::SHOT_ID_SEQUENCE.'`, `'.Shots::SHOT_POSITION, 'ASC', Shots::SHOT_TAGS, 'LIKE', '%'.$tagName.'%');
		$listeShots = $l->simplifyList(Shots::SHOT_ID_SHOT);

		return (count($listeShots) > 0) ? $listeShots : false;
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
