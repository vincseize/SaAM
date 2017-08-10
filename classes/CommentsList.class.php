<?php
require_once (INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php' );

/**
 *
 */

class CommentsList {

	private $type;
	private $ID_proj;
	private $ID_shot;
	private $ID_asset;
	private $ID_scene;
	private $ID_task;
	private $ID_dept;
	private $dept;
	private $list;
	private $messages;

	public function __construct($type='retake', $ID_item=false, $deptOrProj=false, $deptID=false) {
		if ( !$ID_item ) { throw new Exception ('Comments::construct() : missing $ID_item !') ; }
		$this->type = $type;
		if ($type == 'retake') {
			if ( !$deptOrProj ) { throw new Exception ('Comments::construct() : missing $dept !') ; }
			$this->ID_shot = $ID_item;
			$this->dept = $deptOrProj;
		}
		elseif ($type == 'retake_scene') {
			if ( !$deptOrProj ) { throw new Exception ('Comments::construct() : missing $dept !') ; }
			$this->ID_scene = $ID_item;
			$this->dept = $deptOrProj;
		}
		elseif ($type == 'retake_asset') {
			if ( !$deptOrProj ) { throw new Exception ('Comments::construct() : missing $IDproj !') ; }
			if ( !$deptID )		{ throw new Exception ('Comments::construct() : missing $deptID !') ; }
			$this->ID_proj  = $deptOrProj;
			$this->ID_asset = (int)$ID_item;
			$this->ID_dept	= (int)$deptID;
		}
		elseif ($type == 'task') {
			if ( !$deptOrProj ) { throw new Exception ('Comments::construct() : missing $dept !') ; }
			$this->ID_proj  = $deptOrProj;
			$this->ID_task = $ID_item;
		}
		else {
			$this->ID_proj = $ID_item;
		}
		try { $this->loadList(); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
	}

	// Charge les infos
	public function loadList () {
		try {
			$this->list = new Liste() ;
			$this->list->resetFiltre();
			if ($this->type == 'retake') {
				$this->list->addFiltre(Comments::COMM_ID_SHOT, '=', $this->ID_shot, 'AND');
				$this->list->addFiltre(Comments::COMM_DEPT, '=', $this->dept, 'AND');
				$this->list->getListe(TABLE_COMM_SHOT, '*', 'date', 'DESC');
			}
			elseif ($this->type == 'retake_scene') {
				$this->list->addFiltre(Comments::COMM_ID_SCENE, '=', $this->ID_scene, 'AND');
				$this->list->addFiltre(Comments::COMM_DEPT, '=', $this->dept, 'AND');
				$this->list->getListe(TABLE_COMM_SCENES, '*', 'date', 'DESC');
			}
			elseif ($this->type == 'retake_asset') {
				$this->list->addFiltre(Comments::COMM_ID_ASSET, '=', $this->ID_asset, 'AND');
				$this->list->addFiltre(Comments::COMM_ID_PROJECT, '=', $this->ID_proj, 'AND');
				$this->list->addFiltre(Comments::COMM_DEPT, '=', $this->ID_dept, 'AND');
				$this->list->getListe(TABLE_COMM_ASSET, '*', 'date', 'DESC');
			}
			elseif ($this->type == 'task') {
				$this->list->addFiltre(Comments::COMM_ID_PROJECT, '=', $this->ID_proj, 'AND');
				$this->list->addFiltre(Comments::COMM_ID_TASK, '=', $this->ID_task, 'AND');
				$this->list->getListe(TABLE_COMM_TASKS, '*', 'date', 'DESC');
			}
			else {
				$this->list->addFiltre(Comments::COMM_ID_PROJECT, '=', $this->ID_proj, 'AND');
				$this->list->getListe(TABLE_COMM_FINAL, '*', 'date', 'DESC');
			}
			$this->messages = $this->list->simplifyList(Comments::COMM_ID);
		}
		catch (Exception $e) { throw new Exception('CommentsList::loadList() : '.$e->getMessage()); }
	}

	// retourne la liste des messages ou false si pas de message
	public function getComments ($retakeNumber=false) {
		if ($this->messages == false) return false;
		$commList = array();
		$RN = $retakeNumber;
		if (!$retakeNumber && $this->type == 'retake') {		// Si on passe pas de retakeNumber, on renvoie les messages de la derniere retake
			$sh = new Shots($this->ID_shot);
			$RN = count($sh->getRetakesList($this->dept));
			unset($sh);
		}
		if (!$retakeNumber && $this->type == 'retake_scene') {	// Si on passe pas de retakeNumber, on renvoie les messages de la derniere retake
			$sc = new Scenes($this->ID_scene);
			$RN = count($sc->getRetakesList($this->dept));
			unset($sc);
		}
		if (!$retakeNumber && $this->type == 'retake_asset') {	// Si on passe pas de retakeNumber, on renvoie les messages de la derniere retake
			$a = new Assets($this->ID_proj, $this->ID_asset);
			$RN = count($a->getRetakesList($this->ID_proj, $this->ID_dept));
			unset($a);
		}
		// crée l'array des message de base
		foreach ($this->messages as $comm) {
			if (@$comm[Comments::COMM_N_RETAKE] == $RN || ($this->type != 'retake' && $this->type != 'retake_scene' && $this->type != 'retake_asset')) {
				if (@$comm[Comments::COMM_RESPONSE] == 0)
					$commList[$comm[Comments::COMM_ID]] = $comm;
			}
		}
		// crée l'array des message de réponse
//		$this->messages = array_reverse($this->messages);		// inverse l'ordre pour affichage des réponses chronologique
		foreach ($this->messages as $comm) {
			if (@$comm[Comments::COMM_N_RETAKE] == $RN || ($this->type != 'retake' && $this->type != 'retake_scene' && $this->type != 'retake_asset')) {
				if (@$comm[Comments::COMM_RESPONSE] != 0)
					$commList[$comm[Comments::COMM_RESPONSE]]['reponses'][$comm[Comments::COMM_ID]] = $comm;
			}
		}
		return $commList;
	}



}


?>