<?php

require_once (INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php' );
require_once (INSTALL_PATH . FOLDER_FCT . 'directories.php');


//$CLASSNAME = 'Projects';
//define ('CLASSNAME','Projects');

class Projects implements Iterator {

	const UPDATE_ERROR_DATA	= 'donnee invalide' ;						// erreur si pseudo ou autre donnée ne correspond pas
	const UPDATE_OK			= 'donnee modifiee, OK !' ;					// message si une modif BDD a réussi
	const INFO_ERREUR		= 'Projects:: Impossible de lire les infos en BDD';    // erreur de la methode Infos::loadInfos()
	const INFO_DONT_EXIST	= 'donnee inexistante' ;					// erreur si champs inexistant dans BDD lors de récup d'info
	const INFO_FORBIDDEN	= 'donnee interdite' ;						// erreur si info est une donnée sensible (ici, password)
	const SAVE_LOSS			= 'Champs manquants';						// erreur si il manques des données essentielles Ã  sauvegarder dans la BDD

	const PROJECT_OK		= true ;					// retour general, si la fonction a marché
	const PROJECT_ERROR		= false ;					// retour general, si la fonction n'a pas marché

	const PROJECT_ID_PROJECT	= 'id' ;
	const PROJECT_ID_CREATOR	= 'ID_creator' ;
	const PROJECT_FPS			= 'fps' ;
	const PROJECT_TYPE			= 'project_type' ;
	const PROJECT_NOMENCLATURE	= 'nomenclature' ;
	const PROJECT_DPTS			= 'dpts' ;
	const PROJECT_POSITION		= 'position' ;
	const PROJECT_TITLE			= 'title' ;
	const PROJECT_DESCRIPTION	= 'description' ;
	const PROJECT_DIRECTOR		= 'director' ;
	const PROJECT_EQUIPE		= 'equipe' ;
	const PROJECT_COMPANY		= 'company' ;
	const PROJECT_SUPERVISOR	= 'supervisor' ;
	const PROJECT_DATE			= 'date' ;
	const PROJECT_UPDATE		= 'update' ;
	const PROJECT_UPDATED_BY	= 'updated_by' ;
	const PROJECT_DEADLINE		= 'deadline' ;
	const PROJECT_PROGRESS		= 'progress' ;
	const PROJECT_DEMO			= 'demo';
	const PROJECT_HIDE			= 'hide' ;
	const PROJECT_LOCK			= 'lock' ;
	const PROJECT_ARCHIVE		= 'archive' ;
	const PROJECT_DELETED		= 'deleted' ;
	const PROJECT_REFERENCE		= 'reference' ;
	const PROJECT_SOFTWARES		= 'softwares' ;

	private $ID_project;
	private $title_project;
	private $dir_project;
	private $depts_project;
	private $infos;
	private $nbSeqs;
	private $nbShotsTotal;
	private $nbAssetsTotal;

	public function __construct ($ID_project = 'new') {
		$this->infos = new Infos( TABLE_PROJECTS ) ;
		if ( $ID_project == 'new' ) return ;
		$this->ID_project = $ID_project;
		try { $this->loadFromBD( Projects::PROJECT_ID_PROJECT , $this->ID_project ); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
	}

	// Charge les infos
	public function loadFromBD ( $keyFilter , $value ) {
		try {
			$this->infos->loadInfos( $keyFilter, $value );
			$this->ID_project    = $this->infos->getInfo(Projects::PROJECT_ID_PROJECT);
			$this->title_project = $this->infos->getInfo(Projects::PROJECT_TITLE);
			$this->dir_project   = $this->ID_project.'_'.$this->title_project;
		}
		catch (Exception $e) { throw new Exception(Projects::INFO_ERREUR.' : '.$e->getMessage().' pour : '.$keyFilter.' = '.$value); }
	}

																	// GETTERS  //////////////////////////////////////

	// Retourne une valeur de l'objet Infos
	public function getProjectInfos ($what='') {
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

	// Récupère l'ID du projet
	public function getIDproject () {
		return $this->ID_project;
	}
	// Récupère le titre du projet
	public function getTitleProject () {
		return $this->title_project;
	}
	// Récupère le nom du dossier du projet
	public function getDirProject () {
		return $this->dir_project;
	}
	// Récupère l'array du nom des depts (par "label" ou par "id")
	public function getDeptsProject ($withHidden=true, $get="label", $type=false) {
		$this->depts_project = json_decode($this->infos->getInfo(Projects::PROJECT_DPTS));
		$projDeptsNames = array();
		if (is_array($this->depts_project)) {
			$dl = new Liste();
			$dl->getListe(TABLE_DEPTS);
			$listeDepts = $dl->simplifyList('id');
			foreach ($this->depts_project as $dept) {
				if ($type && $listeDepts[$dept]['type'] != $type) continue;
				if ($listeDepts[$dept]['hide'] == 1 && $withHidden === false) continue;
				if ($get == "label")
					$projDeptsNames[$dept] = strtolower($listeDepts[$dept]['label']);
				elseif ($get == "id")
					$projDeptsNames[$dept] = strtolower($listeDepts[$dept]['id']);
			}
		}
		return $projDeptsNames;
	}
	// Récupère l'array de toute les infos des depts
	public function getDeptsProjectWithInfos ($type="shots") {
		$this->depts_project = json_decode($this->infos->getInfo(Projects::PROJECT_DPTS));
		$projDeptsInfos = array();
		if (is_array($this->depts_project)) {
			foreach ($this->depts_project as $dept) {
				try {
					$di = new Infos(TABLE_DEPTS);
					$di->loadInfos('id', $dept);
					$pos = $di->getInfo('position');
					if ($di->getInfo('type') != $type) continue;
					if ($di->getInfo('hide') != 0) continue;
					$projDeptsInfos[$pos]['id']			= $di->getInfo('id');
					$projDeptsInfos[$pos]['label']		= $di->getInfo('label');
					$projDeptsInfos[$pos]['template']	= $di->getInfo('template_name');
					$projDeptsInfos[$pos]['dict']		= $di->getInfo('dict');
				}
				catch(Exception $e) { continue; }
			}
			ksort($projDeptsInfos);
		}
		return $projDeptsInfos;
	}
	// récupère la deadline
	public function getDeadline () {
		return $this->infos->getInfo(Projects::PROJECT_DEADLINE);
	}
	// Récupère le premier dept
	public function getFirstDept () {
		$depts = json_decode($this->infos->getInfo(Projects::PROJECT_DPTS));
		return $depts[0];
	}
	// Récupère la nomenclature Seq / shot
	public function getNomenclature ($what='all') {
		$nomenc    = $this->infos->getInfo(Projects::PROJECT_NOMENCLATURE);
		if ($nomenc == '') return NOMENCLATURE_SHOT.'###'.NOMENCLATURE_SEPARATOR.NOMENCLATURE_SHOT.'###';
		$nomencArr = explode('_', $nomenc);
		if ($what == 'seq')  return $nomencArr[0];
		if ($what == 'shot') return $nomencArr[1];
		else return $nomenc;
	}

	// Récupère les users de l'équipe au choix :
	// sous forme d'array d'IDs, ou bien d'array de pseudos ($teamArr[id]='pseudo'), ou bien sous forme de chaine ('nom1, nom2, nom3, '...)
	public function getEquipe ($mode = 'arr', $what = 'pseudo') {
		$teamIds = json_decode($this->infos->getInfo(Projects::PROJECT_EQUIPE));
		if ($mode == 'arrIDs') return $teamIds;
		if (empty($teamIds) || !is_array($teamIds)) return false;
		$l = new Liste();
		$usersArr  = $l->getListe(TABLE_USERS, 'id, '.$what);
		$usersPseudos = Liste::resortById($usersArr, $what);
		if ($mode == 'arr') {
			$teamArr = array();
			foreach($teamIds as $idUser) {
				if (isset($usersPseudos[$idUser]))
					$teamArr[$idUser] = $usersPseudos[$idUser];
			}
			return $teamArr;
		}
		if ($mode == 'str') {
			$teamStr = '';
			foreach($teamIds as $idUser) {
				if (isset($usersPseudos[$idUser]))
					$teamStr .= $usersPseudos[$idUser].', ';
			}
			$teamStr = substr($teamStr, 0, -2);
			return $teamStr;
		}
	}

	// Retourne un array des séquences associées au projet
	public function getSequences($onlyActives=false) {
		$l = new Liste();
		$l->addFiltre(Sequences::SEQUENCE_ID_PROJECT, '=', $this->ID_project);
		if ($onlyActives) {
			$l->addFiltre(Sequences::SEQUENCE_ARCHIVE, '=', '0', 'AND');
			$l->addFiltre(Sequences::SEQUENCE_HIDE, '=', '0', 'AND');
		}
		$l->getListe(TABLE_SEQUENCES, '*', Sequences::SEQUENCE_POSITION, 'ASC');
		$seqList = $l->simplifyList(Sequences::SEQUENCE_ID_SEQUENCE);
		if (is_array($seqList)) {
			$this->nbSeqs = count($seqList);
			return $seqList;
		}
		else {
			$this->nbSeqs = 0;
			return false;
		}
	}

	// Retourne un array des shots associés au projet
	public function getShots ($idSeq = 'all', $status='all', $by=Shots::SHOT_POSITION, $order='ASC') {
		$l = new Liste();
		if ($status=="actifs") {
			$l->addFiltre(Shots::SHOT_HIDE, '=', 0, 'AND');
			$l->addFiltre(Shots::SHOT_ARCHIVE, '=', 0, 'AND');
		}
		if ($idSeq == 'all')
			$l->addFiltre(Shots::SHOT_ID_PROJECT, '=', $this->ID_project, 'AND');
		else
			$l->addFiltre(Shots::SHOT_ID_SEQUENCE, '=', $idSeq, 'AND');

		$shotsList = $l->getListe(TABLE_SHOTS, '*', $by, $order);
		if (is_array($shotsList)) {
			$this->nbShotsTotal = count($shotsList);
			return $shotsList;
		}
		else return false;
	}

	// Retourne un array des shots associés au projet
	public function getScenes ($idSeq = 'all', $status='all', $by=Scenes::LABEL, $order='ASC') {
		$l = new Liste();
		$l->addFiltre(Scenes::ID_PROJECT, '=', $this->ID_project, 'AND');
		if ($status=="actifs") {
			$l->addFiltre(Scenes::HIDE, '=', 0, 'AND');
			$l->addFiltre(Scenes::ARCHIVE, '=', 0, 'AND');
		}
		if ($idSeq != 'all')
			$l->addFiltre(Scenes::SEQUENCES, 'REGEXP', $idSeq.'(,|\])', 'AND');
		$l->addFiltre(Scenes::MASTER, '=', "0", 'AND');

		$scenesList = $l->getListe(TABLE_SCENES, '*', $by, $order);
		if (is_array($scenesList))
			return $scenesList;
		else return false;
	}

	// Retourne un array des assets associés au projet
	public function getAssets ($status='all', $multi='pure_list') {
		$l = new Liste();
		if ($status=="actifs") {
			$l->addFiltre(Assets::ASSET_HIDE, '=', 0, 'AND');
		}
		$l->addFiltre(Assets::ASSET_ID_PROJECTS, 'LIKE', '%'.$this->ID_project.'%', 'AND');

		$assetsList = $l->getListe(TABLE_ASSETS, '*', Assets::ASSET_PATH_REL, 'ASC');
		if (is_array($assetsList)) {
			$this->nbAssetsTotal = count($assetsList);
			if ($multi == "pure_list")
				return $assetsList;
			elseif ($multi == "by_path") {
				$assetsListMulti = Array();
				foreach ($assetsList as $asset) {
					$path = explode("/", $asset[Assets::ASSET_PATH_REL]);
					$assetsListMulti[@$path[1]][] = $asset;

				}
				return $assetsListMulti;
			}
		}
		else return false;

	}

	// Retourne le nombre de séquences associées au projet
	public function getNbSequences () {
		$this->getSequences();
		return ($this->nbSeqs) ? $this->nbSeqs : 0;
	}

	// Retourne le nombre de shots associés au projet
	public function getNbShots ($idSeq='all', $what='all') {
		$shots = $this->getShots($idSeq);
		if (!$shots)
			return 0;
		if ($what=='all')
			return ($this->nbShotsTotal) ? $this->nbShotsTotal : 0;
		elseif ($what=='actifs') {
			$count = 0;
			foreach($shots as $shot) {
				if (!$shot[Shots::SHOT_HIDE] && !$shot[Shots::SHOT_LOCK] && !$shot[Shots::SHOT_ARCHIVE])
					$count++;
			}
			return $count;
		}
	}
	// Retourne le nombre d'assets associés au projet
	public function getNbAssets () {
		$this->getAssets();
		return ($this->nbAssetsTotal) ? $this->nbAssetsTotal : 0;
	}
	// Renvoie TRUE si le projet est de type demo
	public function isDemo() {
		if ($this->infos->getInfo(Projects::PROJECT_DEMO) == 1) return true;
		else return false;
	}
	// Renvoie TRUE si le projet est visible
	public function isVisible() {
		if ($this->infos->getInfo(Projects::PROJECT_HIDE) == 0 && $this->infos->getInfo(Projects::PROJECT_ARCHIVE) == 0) return true;
		else return false;
	}
	// Renvoie TRUE si le projet est archivé
	public function isArchived() {
		if ($this->infos->getInfo(Projects::PROJECT_ARCHIVE) == 1) return true;
		else return false;
	}


																	// SETTERS  //////////////////////////////////////

	// setters
 	public function setCreator($value) {
		$this->infos->addInfo ( Projects::PROJECT_ID_CREATOR, $value ) ;
	}
 	public function setFps($value) {
		$this->infos->addInfo ( Projects::PROJECT_FPS, $value ) ;
	}
 	public function setNomenclature($value) {
		$this->infos->addInfo ( Projects::PROJECT_NOMENCLATURE, $value ) ;
	}
 	public function setType($value) {
		$this->infos->addInfo ( Projects::PROJECT_TYPE, $value ) ;
	}
	public function setDpts($value) {
		$this->infos->addInfo ( Projects::PROJECT_DPTS, $value ) ;
	}
	public function setTitle($value) {
		$this->infos->addInfo ( Projects::PROJECT_TITLE, $value ) ;
	}
	public function setPosition($value) {
		$this->infos->addInfo ( Projects::PROJECT_POSITION, $value ) ;
	}
	public function setDescription($value) {
		$this->infos->addInfo ( Projects::PROJECT_DESCRIPTION, $value ) ;
	}
 	public function setDirector($value) {
		$this->infos->addInfo ( Projects::PROJECT_DIRECTOR, $value ) ;
	}
 	public function setEquipe($value) {
		$this->infos->addInfo ( Projects::PROJECT_EQUIPE, $value ) ;
	}
	public function setCompany($value) {
		$this->infos->addInfo ( Projects::PROJECT_COMPANY, $value ) ;
	}
	public function setSupervisor($value) {
		$this->infos->addInfo ( Projects::PROJECT_SUPERVISOR, $value ) ;
	}
 	public function setDate($value) {
		$this->infos->addInfo ( Projects::PROJECT_DATE, $value ) ;
	}
 	public function setUpdate($value) {
		$this->infos->addInfo ( Projects::PROJECT_UPDATE, $value ) ;
		$this->infos->addInfo ( Projects::PROJECT_UPDATED_BY, $_SESSION['user']->getUserInfos('id') ) ;
	}
	public function setDeadline($value) {
		$this->infos->addInfo ( Projects::PROJECT_DEADLINE, $value ) ;
	}
	public function setProgress($value) {
		$this->infos->addInfo ( Projects::PROJECT_PROGRESS, $value ) ;
	}
  	public function setDemo($value) {
		$l = new Liste();
		$l->getListe(TABLE_PROJECTS, 'id,project_type', 'id', 'ASC', Projects::PROJECT_DEMO, '=', 1);
		$listDemos = $l->simplifyList('id');
		if (count($listDemos) >= MAX_DEMO_PROJECTS) { throw new Exception('MAXIMUM demo projects reached (max: '.MAX_DEMO_PROJECTS.' type demo) !'); return; }
		$this->infos->addInfo ( Projects::PROJECT_DEMO, $value ) ;
	}
  	public function setLock($value) {
		$this->infos->addInfo ( Projects::PROJECT_LOCK, $value ) ;
	}
  	public function setHide($value) {
		$this->infos->addInfo ( Projects::PROJECT_HIDE, $value ) ;
	}
	public function setArchive($value) {
		$this->infos->addInfo ( Projects::PROJECT_ARCHIVE, $value ) ;
	}
	public function setDeleted($value) {
		$this->infos->addInfo ( Projects::PROJECT_DELETED, $value ) ;
	}
	public function setReference($value) {
		$this->infos->addInfo ( Projects::PROJECT_REFERENCE, $value ) ;
	}
	public function setSoftwares($value) {
		$this->infos->addInfo ( Projects::PROJECT_SOFTWARES, $value ) ;
	}
	// setter de valeur à déterminer
	public function setValue($champ, $value) {
		if (is_array($value)) $value = json_encode($value);
		$this->infos->addInfo ( $champ, $value ) ;
	}

	// modifie la liste des départements
	public function modDpts ($newDeptList) {
		if (!is_array($newDeptList)) { throw new Exception('Projects::modDpts() : $newDeptList not an array !'); return; }
		$oldDeptsList = json_decode($this->infos->getInfo(Projects::PROJECT_DPTS));
		if ($oldDeptsList == null) $oldDeptsList = Array();
		$diffDeptList = array_diff($newDeptList,$oldDeptsList);
		if ($diffDeptList) {
			foreach($diffDeptList as $dept) {
				$di = new Infos(TABLE_DEPTS);
				$di->loadInfos('id', $dept);
				$deptName = $di->getInfo('label');
				$dirProj  = $this->getDirProject();
				$seqList  = $this->getSequences();
				if (!$seqList) continue;
				foreach ($seqList as $seq) {
					$se = new Sequences($seq['id']);
					$labelSeq = $se->getSequenceInfos(Sequences::SEQUENCE_LABEL);
					$shotList = $this->getShots($seq['id']);
					if (!is_array($shotList)) continue;
					foreach($shotList as $shot) {
						$sh = new Shots($shot['id']);
						$labelShot = $sh->getShotInfos(Shots::SHOT_LABEL);
						$dirShotDept = $dirProj.'/sequences/'.$labelSeq.'/'.$labelShot.'/'.$deptName;
						if (!is_dir($dirShotDept)) {
							makeDataDir($dirShotDept);
							makeDataDir($dirShotDept.'/datashot');
							makeDataDir($dirShotDept.'/retakes');
						}
					}
				}
			}
			$this->setDpts(json_encode($newDeptList));
			$this->save();
		}
		else { throw new Exception('No difference !'); return; }
	}

	// purge la liste des users de l'équipe du projet en fonction de tous les users my_projects
	public function purgeEquipe () {
		try {
			$lU = new Liste();
			$usersList = $lU->getListe(TABLE_USERS, 'id,my_projects');
			$newEquipe = array();
			foreach($usersList as $user) {
				$userProjs = json_decode($user[Users::USERS_MY_PROJECTS]);
				if (!is_array($userProjs)) continue;
				if (in_array($this->ID_project, $userProjs))
					$newEquipe[] = (string)$user[Users::USERS_ID];
			}
			$this->setEquipe(json_encode($newEquipe));
		}
		catch (Exception $e) { throw new Exception('Projects::purgeEquipe() : '.$e->getMessage()); return; }
	}

	// ajoute un user à l'équipe
	public function addUserEquipe ($IDuser=false) {
		if (!$IDuser) { throw new Exception('Projects::addUserEquipe() : $IDuser undefined !'); return; }
		$team = $this->getEquipe('arrIDs');
		array_push($team, (string)$IDuser);
		$this->setEquipe(json_encode($team));
	}

	// ajoute un user à l'équipe
	public function removeUserEquipe ($IDuser=false) {
		if (!$IDuser) { throw new Exception('Projects::removeUserEquipe() : $IDuser undefined !'); return; }
		$oldTeam = $this->getEquipe('arrIDs');
		$newTeam = array();
		foreach ($oldTeam as $Uid) {
			if ( $Uid != $IDuser )
				array_push($newTeam,$Uid);
		}
		$this->setEquipe(json_encode($newTeam));
	}

	// sauvegarde les données en BDD
	public function save() {
		try {
			$this->setUpdate(date('Y-m-d 00:00:00'));
			$this->infos->save();
		}
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
		return Projects::PROJECT_OK ;
	}

    public function delete() {
		try { $this->infos->delete(); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
		return Projects::PROJECT_OK ;
	}


	public function recalc_project_progress () {
		$seqs = $this->getSequences(true);
		if (!is_array($seqs)) return;
		$totalProgress = $this->nbSeqs * 100;
		$progressCount = 0;
		foreach ($seqs as $seq) {
			$progressCount += $seq[Sequences::SEQUENCE_PROGRESS];
		}
		$projProgress = (int)($progressCount / $totalProgress * 100);
		$this->setValue(Projects::PROJECT_PROGRESS, $projProgress);
		$this->save();
	}

	public function key()		{ return $this->infos->key(); }
	public function current()	{ return $this->infos->current(); }
	public function next()		{ $this->infos->next() ; }
	public function rewind()	{ $this->infos->rewind() ; }
	public function valid()		{
		while ( $this->infos->valid() ){
			if (in_array($this->infos->key(), $this->hide_datas)) $this->infos->next() ;
			else return true ;
		}
		return false ;
	}






}
?>
