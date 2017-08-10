<?php
require_once (INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php' );

/**
 * Gestion des Tasks
 */

class Tasks {

	const ID_TASK				= 'id';
	const ID_PROJECT_TASK		= 'ID_project';
	const TITLE_TASK			= 'title';
	const DESCRIPTION_TASK		= 'description';
	const TYPE_TASK				= 'type';
	const SECTION_TASK			= 'section';
	const HOOKED_ENTITY_TASK	= 'hooked_entity';
	const CREATOR_TASK			= 'ID_creator';
	const ASSIGNEE_TASK			= 'assigned_to';
	const STANDBY_TASK			= 'standby';
	const STATUS_TASK			= 'status';
	const STATUS_TASK_READABLE	= 'status_readable';
	const START_TASK			= 'start';
	const END_TASK				= 'end';
	const HIDE_TASK				= 'hide';

	const SECTION_ROOT		= 'root';
	const SECTION_ASSETS	= 'asset';
	const SECTION_SCENES	= 'scene';
	const SECTION_SHOTS		= 'shot';

	private $taskInfos;
	private $taskRaw;
	private $ID_project;
	private $ID_task;
	private $task;
	private $creator;
	private $assignee;
	private $type;
	private $status;
	private $hookedEntity;
	private $standby;



	/**
	 * Task instance for get / update / delete one task
	 * @param INT $taskID Wanted task's ID, or 'new' to create one
	 * @param ARRAY $newTaskInfos An array of all informations of the task to create
	 */
	public function __construct($taskID='new', $newTaskInfos=false) {
		if ($taskID == 'new')
			$this->createTask($newTaskInfos);
		else
			$this->loadTask($taskID);
	}

	/**
	 * Load task informations from DB with an ID
	 * @param INT $taskID Wanted task's ID
	 */
	private function loadTask($taskID) {
		if (!is_int($taskID))
			throw new Exception('Task::loadTask() : $taskID must be an integer!');
		$this->taskInfos = new Infos(TABLE_TASKS);
		$this->taskInfos->loadInfos(Tasks::ID_TASK, $taskID);
		$this->ID_task		= (int)$taskID;
		$this->taskRaw		= $this->taskInfos->getInfo();
		$this->ID_project	= (int)$this->taskRaw[Tasks::ID_PROJECT_TASK];
		$this->type			= $this->taskRaw[Tasks::TYPE_TASK];
		$this->task			= $this->formatTaskInfos();
	}

	/**
	 * Create a task with its informations
	 * @param ARRAY $newTaskInfos An array of all informations of the task to create
	 */
	private function createTask($newTaskInfos) {
		if (!is_array($newTaskInfos))
			throw new Exception('Task::createTask() : $newTaskInfos must be an array!');
		if (!isset($newTaskInfos[Tasks::ID_PROJECT_TASK]) || empty($newTaskInfos[Tasks::ID_PROJECT_TASK]))
			throw new Exception('Task::createTask() : missing ID project!');
		if (!isset($newTaskInfos[Tasks::TITLE_TASK]) || empty($newTaskInfos[Tasks::TITLE_TASK]))
			throw new Exception('Task::createTask() : missing task title!');
		if (!isset($newTaskInfos[Tasks::SECTION_TASK]) || empty($newTaskInfos[Tasks::SECTION_TASK]))
			throw new Exception('Task::createTask() : missing task section!');
		if (strlen($newTaskInfos[Tasks::TITLE_TASK]) < 3)
			throw new Exception('Task::createTask() : task title too short! (3 char. min.)');
		if (isset($newTaskInfos[Tasks::ASSIGNEE_TASK])) {
			if (!is_array($newTaskInfos[Tasks::ASSIGNEE_TASK]))
				throw new Exception('Task::createTask() : task user assignation present, but not an array!');
			foreach ($newTaskInfos[Tasks::ASSIGNEE_TASK] as $i => $usr)
				$newTaskInfos[Tasks::ASSIGNEE_TASK][$i] = (int)$usr;
			$newTaskInfos[Tasks::ASSIGNEE_TASK] = json_encode($newTaskInfos[Tasks::ASSIGNEE_TASK]);
		}
		if (!( $newTaskInfos[Tasks::SECTION_TASK] == Tasks::SECTION_ROOT
			|| $newTaskInfos[Tasks::SECTION_TASK] == Tasks::SECTION_ASSETS
			|| $newTaskInfos[Tasks::SECTION_TASK] == Tasks::SECTION_SCENES
			|| $newTaskInfos[Tasks::SECTION_TASK] == Tasks::SECTION_SHOTS))
			throw new Exception('Task::createTask() : task section unknown!');
		if ($newTaskInfos[Tasks::SECTION_TASK] != Tasks::SECTION_ROOT && (!isset($newTaskInfos[Tasks::HOOKED_ENTITY_TASK]) || empty($newTaskInfos[Tasks::HOOKED_ENTITY_TASK])))
			throw new Exception('Task::createTask() : missing task hooked entity '.$newTaskInfos[Tasks::SECTION_TASK].'ID!');
		$newTaskInfos[Tasks::CREATOR_TASK]	= $_SESSION['user']->getUserInfos(Users::USERS_ID);
		$newTaskInfos[Tasks::HIDE_TASK]		= 0;
		if (!isset($newTaskInfos[Tasks::START_TASK]) || empty($newTaskInfos[Tasks::START_TASK]))
			$newTaskInfos[Tasks::START_TASK] = date('Y-m-d H:i:s');
		$newTaskInfos[Tasks::START_TASK] = preg_replace('/T/i', ' ', $newTaskInfos[Tasks::START_TASK]);
		$newTaskInfos[Tasks::END_TASK]   = preg_replace('/T/i', ' ', $newTaskInfos[Tasks::END_TASK]);
		if (!isset($newTaskInfos[Tasks::STATUS_TASK]))
			$newTaskInfos[Tasks::STATUS_TASK] = 0;
		$this->taskInfos = new Infos(TABLE_TASKS);
		$this->taskRaw = $newTaskInfos;
		$this->ID_project = (int)$this->taskRaw[Tasks::ID_PROJECT_TASK];
		$this->type		  = $this->taskRaw[Tasks::TYPE_TASK];
	}

	/**
	 * Populate $this->task with linked infos
	 */
	private function formatTaskInfos() {
		if (!is_array($this->taskRaw))
			return Array();
		$formattedTask  = $this->taskRaw;
		$formattedTask[Tasks::STATUS_TASK_READABLE]	 = $this->status	= ((int)$this->taskRaw[Tasks::STATUS_TASK] == 0) ? 'TODO' : $_SESSION['CONFIG']['DEFAULT_STATUS'][(int)$this->taskRaw[Tasks::STATUS_TASK]];
		$uC = new Users((int)$this->taskRaw[Tasks::CREATOR_TASK]);
		$formattedTask[Tasks::CREATOR_TASK]	 = $this->creator	= $uC->getUserInfos(Users::USERS_PSEUDO);
		$formattedTask[Tasks::ASSIGNEE_TASK] = Array();
		$assignedUsers = json_decode($this->taskRaw[Tasks::ASSIGNEE_TASK]);
		if (is_array($assignedUsers)) {
			foreach($assignedUsers as $uID) {
				$uA = new Users((int)$uID);
				$formattedTask[Tasks::ASSIGNEE_TASK][$uID] = $this->assignee	= $uA->getUserInfos(Users::USERS_PSEUDO);
			}
		}
		switch($this->taskRaw[Tasks::SECTION_TASK]) {
			case Tasks::SECTION_ASSETS:
				$a = new Assets($this->ID_project, (int)$this->taskRaw[Tasks::HOOKED_ENTITY_TASK]);
				$formattedTask[Tasks::HOOKED_ENTITY_TASK] = $a->getAssetInfos();
				break;
			case Tasks::SECTION_SCENES:
				$c = new Scenes((int)$this->taskRaw[Tasks::HOOKED_ENTITY_TASK]);
				$formattedTask[Tasks::HOOKED_ENTITY_TASK] = $c->getSceneInfos();
				break;
			case Tasks::SECTION_SHOTS:
				$s = new Shots($this->taskRaw[Tasks::HOOKED_ENTITY_TASK]);
				$formattedTask[Tasks::HOOKED_ENTITY_TASK] = $s->getShotInfos();
				break;
			case Tasks::SECTION_ROOT:
				$this->hookedEntity = false;
				break;
			default:
				throw new Exception("Tasks::formatTaskInfos() : Section unknown. Can't get the hooked entity.");
		}
		$standbyRel = json_decode($this->taskRaw[Tasks::STANDBY_TASK]);
		$formattedTask[Tasks::STANDBY_TASK] = $this->standby = Array();
		if (is_array($standbyRel))
			$formattedTask[Tasks::STANDBY_TASK] = $this->standby = $standbyRel;
		return $formattedTask;
	}

	/**
	 * Get task all informations
	 * @return ARRAY Assoc array of the task's informations
	 */
	public function getTaskInfos() {
		return $this->task;
	}

	/**
	 * Set the status of the task
	 * @param INT $newStatus The new status ID for the task
	 */
	public function setStatus ($newStatus=0) {
		if (!is_int($newStatus))
			throw new Exception('Tasks::setStatus() : $newStatus not a number!');
		$this->taskRaw[Tasks::STATUS_TASK] = $newStatus;
		$this->task[Tasks::STATUS_TASK_READABLE] = ($newStatus == 0) ? 'TODO' : $_SESSION['CONFIG']['DEFAULT_STATUS'][$newStatus];
	}

	/**
	 * Get messages of the task
	 * @return ARRAY List of task's messages
	 */
	public function getMessages () {
		$CL = new CommentsList('task', $this->ID_task, $this->ID_project);
		return $CL->getComments();
	}

	/**
	 * Modify task informations
	 * @param ARRAY $modVals An array of new values for the task
	 */
	public function modTask ($modVals) {
		if (!is_array($modVals))
			throw new Exception('Tasks::modTask() : $modVals not an array!');
		unset($modVals[Tasks::ID_TASK]);
		foreach ($modVals as $key => $val) {
			if ($key == Tasks::ASSIGNEE_TASK && is_array($val)) {
				$assignVals = Array();
				foreach($val as $v)
					$assignVals[] = (int)$v;
				$val = $assignVals;
			}
			if (is_array($val))
				$val = json_encode($val);
			if ($key == Tasks::ID_PROJECT_TASK || $key == Tasks::STATUS_TASK)
				$val = (int)$val;
			$this->taskRaw[$key] = $val;
		}
	}

	/**
	 * Save the created / modified task to DB
	 */
	public function saveTask() {
		if (!is_array($this->taskRaw))
			throw new Exception('Tasks::saveTask() : task informations not an array!');
		foreach($this->taskRaw as $tiK => $tiV) {
			$this->taskInfos->addInfo($tiK, $tiV);
		}
		$this->taskInfos->save();
	}

	/**
	 * Hide or delete the created / modified task from DB
	 * @param BOOLEAN $hideOnly TRUE to only hide tasks (no real delete), FALSE to really delete from DB (defautl TRUE)
	 */
	public function deleteTask ($hideOnly=true) {
		if ($hideOnly) {
			$this->taskRaw[Tasks::HIDE_TASK] = $this->task[Tasks::HIDE_TASK] = 1;
			$this->saveTask();
		}
		else
			$this->taskInfos->delete();
	}

	/**
	 * Send a mail to every users assigned to the task
	 */
	public function sendMail_assignee () {
		$local = '';
		if (IS_LOCAL === true)
			$local = ' (local)';
		if (DONT_SENDMAIL_LOCAL === true && IS_LOCAL === true)
			return;
		if (!ALERT_TASKS)
			return;
		require_once('url_fcts.php');
		require_once('vignettes_fcts.php');

		$hostNameArr = explode('.', $_SERVER['HTTP_HOST']);
		$whichSaAM = strtoupper($hostNameArr[0]);
		$saamURL = getSaamURL().'/';

		$p = new Projects($this->ID_project);
		$project = $p->getProjectInfos(Projects::PROJECT_TITLE);

		$assignedUsers = json_decode($this->taskRaw[Tasks::ASSIGNEE_TASK]);
		$mails = Array();
		if (is_array($assignedUsers)) {
			foreach($assignedUsers as $uID) {
				$uA = new Users((int)$uID);
				$mails[] = $uA->getUserInfos(USERS::USERS_MAIL);
			}
		}

		switch($this->taskRaw[Tasks::SECTION_TASK]) {
			case Tasks::SECTION_ASSETS:
				$section  = L_ASSETS;
				$a		  = new Assets($this->ID_project, (int)$this->taskRaw[Tasks::HOOKED_ENTITY_TASK]);
				$vignette = check_asset_vignette($a->getPath(), $a->getName(), $this->ID_project);
				$entity	  = $a->getName();
				break;
			case Tasks::SECTION_SHOTS:
				$section  = L_SHOTS;
				$sh		  = new Shots((int)$this->taskRaw[Tasks::HOOKED_ENTITY_TASK]);
				$vignette = check_shot_vignette_ext($this->ID_project, $sh->getShotSequenceInfo(), $sh->getShotInfos(Shots::SHOT_LABEL));
				$entity	  = $sh->getShotSequenceInfo(Sequences::SEQUENCE_TITLE).' / '.$sh->getShotInfos(Shots::SHOT_TITLE);
				break;
			case Tasks::SECTION_SCENES:
				$section  = L_SCENES;
				$sc		  = new Scenes((int)$this->taskRaw[Tasks::HOOKED_ENTITY_TASK]);
				$vignette = check_scene_vignette((int)$this->taskRaw[Tasks::HOOKED_ENTITY_TASK]);
				$entity	  = $sc->getSceneInfos(Scenes::TITLE);
				break;
			default:
				$section  = L_ROOT;
				$vignette = check_proj_vignette_ext($this->ID_project, $project);
				$entity	  = $project;
		}

		$subject = 'SaAM INFO: New Task!';
		$body = '
		<body style="background-color: #333; color: #ccc; padding: 5px;">
			<h4>
				<a style="color:#0090CF; text-decoration:none; outline: none;" href="'.getSaamURL().'">
				SaAM '.$whichSaAM.'</a> | '.mb_convert_case($project, MB_CASE_UPPER).' | '.mb_convert_case($section, MB_CASE_UPPER).' | '.$entity.' | Type "'.$this->type.'"
			</h4>
			<div style="background-color: #555; margin: 5px 0px;">
				<div style="float: left; margin: 5px 10px 5px 5px;">
					<img src="'. preg_replace('/ /', '%20', $saamURL.$vignette) .'" height="75" width="133" />
				</div>
				<div style="padding: 3px;">
					<p style="background-color: #333; margin: 2px 0px; padding: 2px 0px;">
						<span style="color: #999;">'. date(DATE_FORMAT) .'</span>
					</p>
					<p style="margin: 3px 0px;">'.$this->taskRaw[Tasks::TITLE_TASK].'</p>
					<p style="background-color:#4b4b4b; color:#fff; padding: 2px 5px; margin: 3px 0px;">'.nl2br($this->taskRaw[Tasks::DESCRIPTION_TASK]).'</p>
					<p style="margin: 3px 0px; color: #FECF5B;">Task deadline : '.SQLdateConvert($this->taskRaw[Tasks::END_TASK]).'</p>
				</div>
				<div style="clear: both;"></div>
			</div>
			<div style="font-size: 0.8em; color: #666;">SaAM instant information message: you can disable those notifications in your SaAM\'s User Preferences.</div>
		</body>';

		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "From: ".SAAM_MAILBOT."\r\n";
		$headers .= "Content-type: text/html; charset=utf-8";

		$nbMailSent = 0;
		foreach($mails as $toSolo) {
			if (mail($toSolo, $subject, $body, $headers))
				$nbMailSent += 1;
		}
		$to = implode(', ', $mails);

		file_put_contents(INSTALL_PATH.'temp/lastMailSent.html',"<html>\n"
			."<head><meta charset='utf-8' /></head>\n"
			."$subject<br /><br />\n"
			."$body<br /><br />\n"
			."$to<br /><br />\n"
			."DONE! $nbMailSent emails sent.<br />\n"
		."</html>");
	}

	/**
	 * Get a list of Tasks associated to an entity
	 * @param INT $projectID ID of the project
	 * @param STRING $section Section name ('root', 'asset', 'scene', 'shot')
	 * @param INT $idEntity ID of the entity associated
	 * @param INT $status ID of the status to filter
	 * @param BOOL $filterOwners TRUE to get only tasks owned by current user (or nobody), FALSE to skip ownership check (default true)
	 * @return ARRAY List of tasks associated to an entity (asset, scene, shot)
	 */
	public static function getEntityTasks($projectID=false, $section='root', $idEntity=false, $status=0, $filterOwners=true) {
		if(!is_int($projectID))
			throw new Exception('Tasks::getEntityTask() : Project ID is missing (or not a number).');
		$myID = $_SESSION['user']->getUserInfos(Users::USERS_ID);
		$l = new Liste();
		$filtre  = '`'.Tasks::ID_PROJECT_TASK.'` = '.$projectID.' AND ';
		$filtre .= '`'.Tasks::SECTION_TASK.'` = "'.$section.'" AND ';
		$filtre .= '`'.Tasks::HIDE_TASK.'` = 0 AND ';
		if ($filterOwners) {
			$filtre .= '(`'.Tasks::CREATOR_TASK.'` = '.$myID.' OR ';
			$filtre .= '`'.Tasks::ASSIGNEE_TASK.'` REGEXP "(^$)|('.$myID.'(,|\]))" ) AND ';
		}
		if ($idEntity)
			$filtre .= '`'.Tasks::HOOKED_ENTITY_TASK.'` = '.$idEntity.' AND ';
		$filtre .= '`'.Tasks::STATUS_TASK.'` = '.$status.' ';
		$l->setFiltreSQL($filtre);
		$l->getListe(TABLE_TASKS, '*', Tasks::START_TASK, 'ASC');
		$result = $l->simplifyList();
		return ($result === false) ? Array() : $result;
	}


	/**
	 * Get a list of all Tasks of a project
	 * @param INT $projectID ID of the project
	 * @param BOOL $filterOwners TRUE to get only tasks owned by current user (or nobody), FALSE to skip ownership check (default true)
	 * @param STRING $section (optionnal) Section name to filter ('root', 'asset', 'scene', 'shot')
	 * @param INT $status (optionnal) ID of the status to filter
	 * @return ARRAY List of tasks of the project
	 */
	public static function getProjectTasks($projectID=false, $filterOwners=true, $section=false, $status=false) {
		if(!is_int($projectID))
			throw new Exception('Tasks::getEntityTask() : Project ID is missing (or not a number).');
		$myID = $_SESSION['user']->getUserInfos(Users::USERS_ID);
		$l = new Liste();
		$filtre  = '`'.Tasks::ID_PROJECT_TASK.'` = '.$projectID.' AND ';
		if ($filterOwners) {
			$filtre .= '(`'.Tasks::CREATOR_TASK.'` = '.$myID.' OR ';
			$filtre .= '`'.Tasks::ASSIGNEE_TASK.'` REGEXP "(^$)|('.$myID.'(,|\]))" ) AND ';
		}
		if ($section)
			$filtre .= '`'.Tasks::SECTION_TASK.'` = "'.$section.'" AND ';
		if ($status !== false)
			$filtre .= '`'.Tasks::STATUS_TASK.'` = '.$status.' AND ';
		$filtre .= '`'.Tasks::HIDE_TASK.'` = 0';
		$l->setFiltreSQL($filtre);
		$l->getListe(TABLE_TASKS, '*', Tasks::START_TASK, 'ASC');
		$result = $l->simplifyList();
		return ($result === false) ? Array() : $result;
	}

}
