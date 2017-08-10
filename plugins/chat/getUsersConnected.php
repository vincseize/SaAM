<?php
session_start();
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé

define("INSTALL_PATH", substr( dirname(__FILE__), 0, -12) );
$configForUserConx = false;

require_once (INSTALL_PATH.'inc/initInclude.php');
require_once ('common.inc');
require_once ('autoload.php');
require_once ('PDOinit.php');
require_once ('config.inc');
require_once ('dates.php');

$retour['error'] = 'error';
$retour['message'] = 'WTF?';

/******************** RÉCUPÉRATION DES USERS connectés, en fonction des ACL, et des projets / shots communs ************************/
try {

	$usersConnected = array();
	$listSessFiles  = glob(INSTALL_PATH.FOLDER_SESSIONS.'*.ssid');
	foreach($listSessFiles as $sess) {
		$fileName = explode(".", basename($sess));
		$userID	  = $fileName[0];
		try { $usrCx = new Users((int)$userID); }
		catch (Exception $e) { rmSessionFile($userID); continue; }
		$UClogin	  = $usrCx->getUserInfos(Users::USERS_LOGIN);
		$UCpseudo	  = $usrCx->getUserInfos(Users::USERS_PSEUDO);
		$UCstatus	  = $usrCx->getUserInfos(Users::USERS_STATUS);
		$UClastAction = $usrCx->getUserInfos('date_last_action');
		$UCreadableStatus = $_SESSION['STATUS_LIST'][$UCstatus];
		$myProjects	  = $usrCx->getUserProjects();
		$myShots	  = $usrCx->getUserShots();

		$UCavatar     = 'gfx/novignette/novignette_user.png';
		$vignetteTest = FOLDER_DATA_USER . $userID.'_'.$UClogin.'/vignette';
		if (is_file(INSTALL_PATH.$vignetteTest.'.png')) $UCavatar = $vignetteTest.'.png';
		if (is_file(INSTALL_PATH.$vignetteTest.'.jpg')) $UCavatar = $vignetteTest.'.jpg';
		if (is_file(INSTALL_PATH.$vignetteTest.'.gif')) $UCavatar = $vignetteTest.'.gif';

		if (compare_dates(time(), $UClastAction, 'connected_users')) {
			$usersConnected[] = Array(
				'id'		=> $userID,
				'login'		=> $UClogin,
				'pseudo'	=> $UCpseudo,
				'status'	=> (int)$UCstatus,
				'statusR'	=> $UCreadableStatus,
				'avatar'	=> $UCavatar,
				'projects'	=> $myProjects,
				'shots'		=> $myShots
			);
		}
	}
	$retour['error']	= 'OK';
	$retour['message']	= count($usersConnected).' users connected.';
	$retour['users']	= $usersConnected;
}
catch(Exception $e){
	$retour['message'] = $e->getMessage();
}

echo json_encode($retour);


?>


