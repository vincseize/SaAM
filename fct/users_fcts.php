<?php

/**
 * Get a list of userInfos of currently connected users
 * @return ARRAY List of all users informations which are connected to SaAM
 */
function getUsersConnected ($getUsersBy='projects') {
	try {
		$users_connected = array();
		if ( isset($_SESSION['INSTALL_PATH'])) { $dir=$_SESSION['INSTALL_PATH'].'sessions'; }
		else { $dir ='sessions'; }
		$listSessFiles = glob($dir.'/*.ssid');
		foreach($listSessFiles as $sess) {
			$fileName = explode(".", basename($sess));
			$idU = $fileName[0];
			$usr = new Users((int)$idU);
			$users_connected[$idU] = $usr->getUserInfos();
		}
		return $users_connected;
	}
	catch (Exception $e) { echo $e->getMessage(); return false; }
}


/**
 * Get the number of users currently connected to SaAM
 * @return INT Number of user connected to SaAM
 */
function countUsersConnected() {
	if ( isset($_SESSION['INSTALL_PATH'])) { $dir=$_SESSION['INSTALL_PATH'].'sessions'; }
	else { $dir ='sessions';}
	return count(glob($dir.'/*.ssid'));
}


/**
 * Format an array of user IDs to an HTML string (list of 'span' tags)
 * @param ARRAY $team An array of user's IDs
 * @return STRING An HTML formatted list of users
 */
function formatTeam($team) {
	$teamF = '';
	if (!is_array($team))
		$team = json_decode($team);
	if (is_array($team)) {
		foreach ($team as $idUsr) {
			$u = new Users((int)$idUsr);
			$usr = $u->getUserInfos(Users::USERS_PSEUDO);
			$teamF .= '<span class="fondPage pad3 padV5 ui-corner-all">'.$usr.'</span> ';
		}
	}
	return $teamF;
}