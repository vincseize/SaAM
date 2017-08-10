<?php

require_once (INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php' );

class ACL {


	private $grp;
	private $user;
	private $userStatus;
	private $auth;


	/**
	* function __construct ( User object )
	*
	* Construction et récupération des ACL de l'user (passer un objet Users:: !)
	*
	*  <b>methode</b> : new ACL($_SESSION['user']);
	*/
	public function __construct($User) {
		if (!is_object($User)) { throw new Exception('ACL::construct - $User not an object !'); return; }
		$this->user = $User;
		$this->userStatus = $this->user->getUserInfos(Users::USERS_STATUS);
	}

	/**
	* function check ( ACL group name [, elementType_elementID] )
	*
	* Retourne True ou False pour un user, dans le groupe demandé
	*
	*  <b>methode</b> : $ACL->check('groupe1', 'proj_34');
	*/
	public function check ($group, $element=false) {
		if ($this->getGrp($group) == false) { throw new Exception('ACL::check - Can\'t get the group "'.$group.'" !'); return; }
		$this->getACL();

		switch ($this->auth) {
			case 'N':
				return false;
				break;
			case 'O':
				if ($element == false) { throw new Exception('ACL::check - $element is missing !'); return; }
				return $this->checkOwnership($element);
				break;
			case 'A':
				return true;
				break;
		}
	}



	///////////////////////////////////////////////////////// Fonctions privées //////////////////////////////////////////////////

	// Récupère le bon groupe d'ACL en BDD
	private function getGrp ($grp=false) {
		if ($grp == false) { throw new Exception('ACL::getGrp - $group is missing !'); return false; }
		try {
			$info = new Infos(TABLE_ACL);
			$info->loadInfos('grp_name', $grp);
			$grpACL = $info->getInfo();
			unset($grpACL['id']);
			$this->grp = $grpACL;
			return true;
		}
		catch (Exception $e) { return false; }
	}


	// Récupère les droits d'un user selon son status et le groupe défini
	private function getACL () {
		if (!is_array($this->grp)) { throw new Exception ('ACL:: $grp not an array -- you must call getGrp() before getACL() !'); return; }
		$this->auth = $this->grp[$this->userStatus];
	}


	// Vérifie si un élément appartient à l'user
	private function checkOwnership ($element) {
		if (!is_object($this->user)) { throw new Exception('ACL::$user not valid !'); return; }
		$el = explode(':', $element);
		$elemType = $el[0];
		$elemID   = $el[1];

		switch ($elemType) {
			case 'proj':
				$userProjs   = $this->user->getUserProjects();
				if (in_array($elemID, $userProjs))   return true;
				break;
			case 'projcreated':
				$p = new Projects((int)$elemID);
				$created = ($p->getProjectInfos(Projects::PROJECT_ID_CREATOR) ==  $this->user->getUserInfos(Users::USERS_ID));
				if ($this->user->getUserInfos(Users::USERS_STATUS) == Users::USERS_STATUS_SUPERVISOR && !$created) return false;
				$userProjs   = $this->user->getUserProjects();
				if (in_array($elemID, $userProjs))   return true;
				break;
			case 'user':
				return ($elemID == $this->user->getUserInfos(Users::USERS_ID));
			case 'seq':
				$userSeqs    = $this->user->getUserSequences();
				if (in_array($elemID, $userSeqs))    return true;
				break;
			case 'shot':
				$userShots   = $this->user->getUserShots();
				if (in_array($elemID, $userShots))   return true;
				break;
			case 'task':
				$userTasks   = $this->user->getUserTasks();
				if (in_array($elemID, $userTasks))   return true;
				break;
			case 'scene':
				$userScenes   = $this->user->getUserScenes();
				if (in_array($elemID, $userScenes))   return true;
				break;
			case 'asset':
				if ((int)$elemID != 0)
					$userAssets  = $this->user->getUserAssets();
				else $userAssets = $this->user->getUserAssets('name');
				if (in_array($elemID, $userAssets)) return true;
				break;
			default:
				throw new Exception ('ACL:: unknown type of element "'.$elemType.'" !');
		}

		return false;
	}


}

?>