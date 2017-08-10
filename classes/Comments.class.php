<?php
require_once (INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php' );

/**
 *
 */

class Comments implements Iterator {
	const COMM_ID				= 'id' ;
	const COMM_ID_PROJECT		= 'ID_project' ;
	const COMM_ID_SHOT			= 'ID_shot' ;
	const COMM_ID_ASSET			= 'ID_asset' ;
	const COMM_ID_SCENE			= 'ID_scene' ;
	const COMM_ID_TASK			= 'ID_task' ;
	const COMM_DEPT				= 'dept' ;
	const COMM_RESPONSE			= 'response_to' ;
	const COMM_COMMENT			= 'comment' ;
	const COMM_SENDER_ID		= 'senderId' ;
	const COMM_SENDER_LOGIN		= 'senderLogin' ;
	const COMM_SENDER_STATUS	= 'senderStatus' ;
	const COMM_SENDER			= 'sender' ;
	const COMM_N_RETAKE			= 'num_retake' ;
	const COMM_READ_BY			= 'read_by' ;
	const COMM_DATE				= 'date' ;

	private $type;
	private $ID_comment;
	private $ID_proj;
	private $ID_shot;
	private $ID_asset;
	private $ID_scene;
	private $ID_task;
	private $infos;
	private $dept;

	public function __construct($type=false, $ID_comment='new') {
		if ($type === false) { throw new Exception('Comments::__construct : $type is missing !'); return; }
		switch ($type) {
			case 'retake':
				$this->type = 'retake';
				$this->infos = new Infos( TABLE_COMM_SHOT ) ;
				break;
			case 'retake_asset':
				$this->type = 'retake_asset';
				$this->infos = new Infos( TABLE_COMM_ASSET ) ;
				break;
			case 'retake_scene':
				$this->type = 'retake_scene';
				$this->infos = new Infos( TABLE_COMM_SCENES ) ;
				break;
			case 'final':
				$this->type = 'final';
				$this->infos = new Infos( TABLE_COMM_FINAL ) ;
				break;
			case 'task':
				$this->type = 'task';
				$this->infos = new Infos( TABLE_COMM_TASKS ) ;
				break;
			default:
				throw new Exception('Comments::__construct : $type ('.$type.') is unknown!'); return;
				break;
		}
		if ( $ID_comment == 'new' ) return ;
		$this->ID_comment = (int)$ID_comment;
		try { $this->loadFromBD( Comments::COMM_ID, $this->ID_comment ); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
	}

	// Charge les infos
	public function loadFromBD ( $keyFilter , $value ) {
		try {
			$this->infos->loadInfos( $keyFilter, $value );
			$this->ID_shot  = $this->infos->getInfo(Comments::COMM_ID_SHOT);
			$this->dept		= $this->infos->getInfo(Comments::COMM_DEPT);
		}
		catch (Exception $e) { throw new Exception('Comments::loadFromBD() : '.$e->getMessage().' pour : '.$keyFilter.' = '.$value); }
	}

	// Initialisation des infos de base d'un message de RETAKE
	public function initNewCommRetake ($idShot=false, $dept=false, $reponse=false) {
		if ($this->type == 'retake_asset') { throw new Exception('Comments::initNewCommRetake() : You wanted ASSET comment !'); return; }
		if (!$idShot) { throw new Exception('Comments::initNewCommRetake() : $idShot undefined !'); return; }
		if (!$dept) { throw new Exception('Comments::initNewCommRetake() : $dept undefined !'); return; }
		$this->ID_shot = $idShot;
		$this->dept = $dept;
		$this->infos->addInfo(Comments::COMM_ID_SHOT, $this->ID_shot);
		$this->infos->addInfo(Comments::COMM_DEPT, $this->dept);
		$this->infos->addInfo(Comments::COMM_SENDER_ID, $_SESSION['user']->getUserInfos(Users::USERS_ID));
		$this->infos->addInfo(Comments::COMM_SENDER_LOGIN, $_SESSION['user']->getUserInfos(Users::USERS_LOGIN));
		$this->infos->addInfo(Comments::COMM_SENDER_STATUS, $_SESSION['user']->getUserInfos(Users::USERS_STATUS));
		$this->infos->addInfo(Comments::COMM_SENDER, $_SESSION['user']->getUserInfos(Users::USERS_PSEUDO));
		$sh = new Shots($this->ID_shot);
		$idProj = $sh->getShotInfos(Shots::SHOT_ID_PROJECT);
		$numRetake = count($sh->getRetakesList($this->dept));
		unset($sh);
		$this->infos->addInfo(Comments::COMM_ID_PROJECT, $idProj);
		$this->infos->addInfo(Comments::COMM_N_RETAKE, $numRetake);
		if (is_int((int)$reponse)) {
			$this->infos->addInfo(Comments::COMM_RESPONSE, $reponse);
		}
	}

	// Initialisation des infos de base d'un message de RETAKE ASSET
	public function initNewCommRetake_scene ($idScene=false, $idProj=false, $deptID=false, $reponse=false) {
		if ($this->type == 'retake') { throw new Exception('Comments::initNewCommRetake_asset() : You wanted SHOT comment !'); return; }
		if (!$idScene) { throw new Exception('Comments::initNewCommRetake() : $idAsset undefined !'); return; }
		if (!$idProj) { throw new Exception('Comments::initNewCommRetake() : $idProj undefined !'); return; }
		if (!$deptID) { throw new Exception('Comments::initNewCommRetake() : $deptID undefined !'); return; }
		$this->ID_scene = (int)$idScene;
		$this->infos->addInfo(Comments::COMM_ID_SCENE, $this->ID_scene);
		$this->infos->addInfo(Comments::COMM_ID_PROJECT, $idProj);
		$this->infos->addInfo(Comments::COMM_DEPT, $deptID);
		$this->infos->addInfo(Comments::COMM_SENDER_ID, $_SESSION['user']->getUserInfos(Users::USERS_ID));
		$this->infos->addInfo(Comments::COMM_SENDER_LOGIN, $_SESSION['user']->getUserInfos(Users::USERS_LOGIN));
		$this->infos->addInfo(Comments::COMM_SENDER_STATUS, $_SESSION['user']->getUserInfos(Users::USERS_STATUS));
		$this->infos->addInfo(Comments::COMM_SENDER, $_SESSION['user']->getUserInfos(Users::USERS_PSEUDO));
		$sc = new Scenes($this->ID_scene);
		$sc->getDirRetakes($deptID);
		$numRetake = count($sc->getRetakesList($deptID));
		unset($sc);
		$this->infos->addInfo(Comments::COMM_N_RETAKE, $numRetake);
		if (is_int((int)$reponse)) {
			$this->infos->addInfo(Comments::COMM_RESPONSE, $reponse);
		}
	}

	// Initialisation des infos de base d'un message de RETAKE ASSET
	public function initNewCommRetake_asset ($idAsset=false, $idProj=false, $deptID=false, $reponse=false) {
		if ($this->type == 'retake') { throw new Exception('Comments::initNewCommRetake_asset() : You wanted SHOT comment !'); return; }
		if (!$idAsset) { throw new Exception('Comments::initNewCommRetake() : $idAsset undefined !'); return; }
		if (!$idProj) { throw new Exception('Comments::initNewCommRetake() : $idProj undefined !'); return; }
		if (!$deptID) { throw new Exception('Comments::initNewCommRetake() : $deptID undefined !'); return; }
		$this->ID_asset = (int)$idAsset;
		$this->infos->addInfo(Comments::COMM_ID_ASSET, $this->ID_asset);
		$this->infos->addInfo(Comments::COMM_ID_PROJECT, $idProj);
		$this->infos->addInfo(Comments::COMM_DEPT, $deptID);
		$this->infos->addInfo(Comments::COMM_SENDER_ID, $_SESSION['user']->getUserInfos(Users::USERS_ID));
		$this->infos->addInfo(Comments::COMM_SENDER_LOGIN, $_SESSION['user']->getUserInfos(Users::USERS_LOGIN));
		$this->infos->addInfo(Comments::COMM_SENDER_STATUS, $_SESSION['user']->getUserInfos(Users::USERS_STATUS));
		$this->infos->addInfo(Comments::COMM_SENDER, $_SESSION['user']->getUserInfos(Users::USERS_PSEUDO));
		$a = new Assets($idProj, $this->ID_asset);
		$a->getDirRetakes($idProj, $deptID);
		$numRetake = count($a->getRetakesList());
		unset($a);
		$this->infos->addInfo(Comments::COMM_N_RETAKE, $numRetake);
		if (is_int((int)$reponse)) {
			$this->infos->addInfo(Comments::COMM_RESPONSE, $reponse);
		}
	}

	// Initialisation des infos de base d'un message
	public function initNewComm_task ($idProj=false, $idTask=false) {
		if (!$idProj) throw new Exception('Comments::initNewComm_task() : $idProj undefined !');
		if (!$idTask) throw new Exception('Comments::initNewComm_task() : $idTask undefined !');
		$this->ID_proj = (int)$idProj;
		$this->ID_task = (int)$idTask;
		$this->infos->addInfo(Comments::COMM_ID_PROJECT, $this->ID_proj);
		$this->infos->addInfo(Comments::COMM_ID_TASK, $this->ID_task);
		$this->infos->addInfo(Comments::COMM_SENDER_ID, $_SESSION['user']->getUserInfos(Users::USERS_ID));
		$this->infos->addInfo(Comments::COMM_SENDER_LOGIN, $_SESSION['user']->getUserInfos(Users::USERS_LOGIN));
		$this->infos->addInfo(Comments::COMM_SENDER_STATUS, $_SESSION['user']->getUserInfos(Users::USERS_STATUS));
		$this->infos->addInfo(Comments::COMM_SENDER, $_SESSION['user']->getUserInfos(Users::USERS_PSEUDO));
	}

	// Initialisation des infos de base d'un message
	public function initNewComm ($idProj=false, $reponse=false) {
		if (!$idProj) throw new Exception('Comments::initNewComm() : $idProj undefined !');
		$this->ID_proj = $idProj;
		$this->infos->addInfo(Comments::COMM_ID_PROJECT, $this->ID_proj);
		$this->infos->addInfo(Comments::COMM_SENDER_ID, $_SESSION['user']->getUserInfos(Users::USERS_ID));
		$this->infos->addInfo(Comments::COMM_SENDER_LOGIN, $_SESSION['user']->getUserInfos(Users::USERS_LOGIN));
		$this->infos->addInfo(Comments::COMM_SENDER_STATUS, $_SESSION['user']->getUserInfos(Users::USERS_STATUS));
		$this->infos->addInfo(Comments::COMM_SENDER, $_SESSION['user']->getUserInfos(Users::USERS_PSEUDO));
		if (is_int((int)$reponse)) {
			$this->infos->addInfo(Comments::COMM_RESPONSE, $reponse);
		}
	}

	// Ajout du texte
	public function addText ($text=false) {
		if (!$text) throw new Exception('Comments::addText() : $text undefined !');
		$this->infos->addInfo(Comments::COMM_COMMENT, stripslashes($text));
	}

	// suppression de message
	public function delete() {
		try {
			// Si il y a des réponses, on les supprime avec
			if ($this->checkHasResponses() && is_array($this->reponses)) {
				foreach($this->reponses as $repID=>$rep) {
					$ir = new Infos(TABLE_COMM_SHOT);
					if ($this->type == 'retake_asset')
						$ir = new Infos(TABLE_COMM_ASSET);
					if ($this->type == 'final')
						$ir = new Infos(TABLE_COMM_FINAL);
					$ir->loadInfos(Comments::COMM_ID, $repID);
					$ir->delete();
				}
			}
			$this->infos->delete();
		}
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
		return true;
	}

	// Défini comme lu pour un user
	public function setReadBy ($idUser) {
		$oldRead = json_decode($this->infos->getInfo(Comments::COMM_READ_BY));
		$readBy = Array((int)$idUser);
		if (is_array($oldRead))
			$readBy = array_merge($oldRead, $readBy);
		$this->infos->addInfo(Comments::COMM_READ_BY, json_encode($readBy));
	}

	// sauvegarde les données en BDD
	public function save() {
		try { $this->infos->save(); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
		return true;
	}

	// vérifie si le message a des réponses en BDD
	private function checkHasResponses () {
		$lr = new Liste();
		$lr->resetFiltre();
		$lr->addFiltre(Comments::COMM_RESPONSE, '=', $this->ID_comment, 'AND');
		if ($this->type == 'retake') {
			$lr->addFiltre(Comments::COMM_ID_SHOT, '=', $this->ID_shot, 'AND');
			$lr->addFiltre(Comments::COMM_DEPT, '=', $this->dept, 'AND');
			$lr->getListe(TABLE_COMM_SHOT);
		}
		else {
			$lr->getListe(TABLE_COMM_FINAL, '*', 'id', 'ASC', Comments::COMM_ID_PROJECT, '=', $this->ID_proj);
		}
		$this->reponses = $lr->simplifyList('id');
		if (count($this->reponses) > 0) return true;
		return false;
	}


	// Iterator
	public function key()		{ return $this->infos->key(); }
	public function current()	{ return $this->infos->current(); }
	public function next()		{ $this->infos->next() ; }
	public function rewind()	{ $this->infos->rewind() ; }
	public function valid()		{
		while ($this->infos->valid()) {
			$this->infos->next() ;
		}
		return false ;
	}
}
?>