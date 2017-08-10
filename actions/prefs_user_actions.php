<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once ('admin_users_fcts.php');

extract($_POST);

$retour['error'] = 'error, no action selected !';

try {
	$userID = $_SESSION['user']->getUserInfos(Users::USERS_ID);

	if (@$action == 'modUserVal') {
		$u = new Users();
		$u->loadFromBD(Users::USERS_ID, $userID);
		if ($row == Users::USERS_LOGIN) {
			$oldDir = INSTALL_PATH.FOLDER_DATA_USER . $userID.'_'.$u->getUserInfos(Users::USERS_LOGIN);
			$newDir = INSTALL_PATH.FOLDER_DATA_USER . $userID.'_'.$val;
		}
		$u->setUserInfos($row, $val);
		$u->save();
		if ($row == Users::USERS_LOGIN)
			rename($oldDir, $newDir);
		$retour['error'] = 'OK';
		$retour['message'] = ''.$row.' modified.';
	}

	if (@$action == 'validAvatar') {
		move_uploaded_vignette ($dirUser, $vignetteName);
		$retour['error'] = 'OK';
		$retour['message'] = 'Avatar modified.';
	}

	if (@$action == 'modUserPpath') {
		$u = new Users();
		$u->loadFromBD(Users::USERS_ID, $userID);
		$u->addProjectPath($pid, $uPath);
		$u->save();
		$retour['error'] = 'OK';
		$retour['message'] = 'Project PATH modified.';
	}

	if (@$action == 'modUserPurl') {
		$u = new Users();
		$u->loadFromBD(Users::USERS_ID, $userID);
		$u->addProjectUrl($pid, $uUrl);
		$u->save();
		$retour['error'] = 'OK';
		$retour['message'] = 'Project URL modified.';
	}
}
catch(Exception $e) {
	$retour['error'] = $e->getMessage();
}

echo json_encode($retour);


?>
