<?php

	// vérification de la validité de la connexion active pour destruction du fichier ssid

session_start();

$dontTouchSSID = true;

if (isset($_SESSION['INSTALL_PATH']))
	require_once ($_SESSION['INSTALL_PATH'].'inc/checkConnect.php' );
elseif (file_exists('inc/checkConnect.php'))
	require_once ('inc/checkConnect.php' );
elseif (file_exists('../inc/checkConnect.php'))
	require_once ('../inc/checkConnect.php' );
elseif (file_exists('../../inc/checkConnect.php'))
	require_once ('../../inc/checkConnect.php' );

$retour['status']	= 'no_user_connected';
$retour['login']	= 'nobody';
$retour['remain']	= 0;

if (isset($_SESSION['user'])) {

	$retour['login']  = $_SESSION['user']->getUserInfos('login');

	$my_id		= $_SESSION['user']->getUserInfos('id');
	$log_file	= INSTALL_PATH . FOLDER_SESSIONS . $my_id . ".ssid";

	if (file_exists($log_file)) {
		$now  = time();
		$sess = filemtime($log_file);
		if (compare_dates($now, $sess, 'connected_session')) {			// Si fichier de session trop vieux :
			unlink($log_file);											// on detruit le fichier de session
			$retour['status'] = 'deconnexion_auto';						// et on renvoie la réponse pour déconnexion auto
		}
		else {
			$retour['status'] = 'still_valid_connexion';				// Sinon, la session est toujours valide :
			$remaining = (DECONNEXION_AUTO_TIME - ($now - $sess)) / 60;	// on calcule le temps restant avant la déconnexion auto
			$retour['remain'] = (int)$remaining;						// et on renvoie le résultat arrondi pour affichage
		}
	}
	else $retour['status'] = 'deconnexion_auto';
}


echo json_encode($retour);

?>
