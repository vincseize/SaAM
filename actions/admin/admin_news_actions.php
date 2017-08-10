<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC'] . "/checkConnect.php" );
	
	if (!$_SESSION['user']->isSupervisor()) die('{"error":"error", "message":"actions news : Access denied."}');
	
extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';

$userActifID	 = $_SESSION['user']->getUserInfos('id');	  global $userActifID;
$userActifStatus = $_SESSION['user']->getUserInfos('status'); global $userActifStatus;


include ('admin_news_fcts.php');

// Traitement de l'action 'Add News'
if ($action == 'addNew') {
	try {
		add_news(stripslashes($title), stripslashes($text), $visible, $userActifID);
		$retour['error']   = $retour['message'] = 'OK';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}
// Traitement de l'action 'Modif Title and Text'
if ($action == 'modNews') {
	try {
		$retour['dateNew'] = mod_news_title_text($idNews, stripslashes($newTitle), stripslashes($newText));
		$retour['error']   = $retour['message'] = 'OK';
		$retour['idNew']   = $idNews;
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}
// Traitement de l'action 'Modif Visibility'
if ($action == 'modNewsVis') {
	try {
		$retour['newState'] = mod_news_visibility($idNews, $visibility);
		$retour['error']    = $retour['message'] = 'OK';
		$retour['idNew']    = $idNews;
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}
// Traitement de l'action 'Delete News'
if ($action == 'delNew') {
	try {
		delete_news($idNews);
		$retour['error'] = $retour['message'] = 'OK';
		$retour['idNew'] = $idNews;
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}


echo json_encode($retour);

//////////////////////////////////////////////////////////////////////////////// FONCTIONS


	
?>