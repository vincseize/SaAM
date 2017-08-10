<?php
@session_start();
require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
require_once ("users_fcts.php");

extract($_POST);

$retour['error']	= 'error';
$retour['message']	= 'action undefined';

try {
	// Add a task
	if ($action == 'addTask') {
		$taskVals  = json_decode(urldecode($newTaskVals), true);
		$newTaskID = Liste::getAIval(TABLE_TASKS);
		$t = new Tasks('new', $taskVals);
		$t->saveTask();
		$t->sendMail_assignee();
		$retour['newTask'] = Array(
			'id'	=> $newTaskID,
			'title' => $taskVals[Tasks::TITLE_TASK],
			'team'	=> formatTeam($taskVals[Tasks::ASSIGNEE_TASK]),
			'from'	=> $_SESSION['user']->getUserInfos(Users::USERS_PSEUDO),
			'type'	=> $taskVals['type'],
			'start' => (SQLdateConvert($taskVals[Tasks::START_TASK]) == '?') ? date(DATE_FORMAT) : SQLdateConvert($taskVals[Tasks::START_TASK]),
			'end'	=> SQLdateConvert($taskVals[Tasks::END_TASK])
		);
		$retour['error'] = 'OK';
		$retour['message'] = 'OK, task added.';
	}

	// Modify task status
	if ($action == 'changeTaskStatus') {
		$t = new Tasks((int)$taskID);
		$t->setStatus((int)$newStatus);
		$t->saveTask();
		$retour['error']	 = 'OK';
		$retour['moveTask']  = $taskID;
		$retour['newStatus'] = $newStatus;
		$retour['message']	 = 'OK, task status modified.';
	}

	// Modify task
	if ($action == 'modifyTask') {
		$modVals = json_decode($modTaskVals, true);
		$taskID  = (int)$modVals[Tasks::ID_TASK];
		$t = new Tasks($taskID);
		$t->modTask($modVals);
		$t->saveTask();
		$t->sendMail_assignee();
		$retour['error']		= 'OK';
		$retour['changeTask']	= $taskID;
		$modVals['from']		= $t->getTaskInfos(Tasks::CREATOR_TASK);
		$modVals['type']		= $modVals[Tasks::TYPE_TASK];
		$modVals['team']		= formatTeam($modVals[Tasks::ASSIGNEE_TASK]);
		$modVals['start']		= SQLdateConvert($modVals[Tasks::START_TASK]);
		$modVals['end']			= SQLdateConvert($modVals[Tasks::END_TASK]);
		$retour['modTaskVals']	= $modVals;
		$retour['message']		= 'OK, task modified.';
	}

	// Delete a task
	if ($action == 'deleteTask') {
		$t = new Tasks((int)$taskID);
		$t->deleteTask();
		$retour['error']	 = 'OK';
		$retour['delTask']  = $taskID;
		$retour['message']	 = 'OK, task deleted.';
	}

	// Add a message to a task
	if ($action == 'addMessage') {
		$c = new Comments('task');
		$c->initNewComm_task($idProj, $idTask);
		$c->addText($texte);
		$c->save();
		$retour['error']	 = 'OK';
		$retour['message']	 = 'OK, message added.';
	}

	// Delete a message from a task
	if ($action == 'deleteMessage') {
		$c = new Comments('task', (int)$idComm);
		$c->delete();
		$retour['error']	 = 'OK';
		$retour['message']	 = 'OK, message deleted.';
	}

}
catch (Exception $e) { $retour['message'] = $e->getMessage(); }

echo json_encode($retour);