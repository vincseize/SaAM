<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	if (!($_SESSION['user']->isSupervisor() || $_SESSION['user']->isDemo())) die('{"error":"error", "message":"actions dailies : Access denied."}');

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';


// Ajout d'un commmentaire de dailies (summary)
if ($action == 'addSummary') {
	try {
		if ($_SESSION['user']->isSupervisor() || $_SESSION['user']->isDemo()) {
			Dailies::add_dailies_summary($projID, $comment);
			$retour['error'] = 'OK';
			$retour['message'] = 'Comment added to this week\'s summary.';
		}
		else $retour['message'] = 'Access denied.';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Modif d'un commmentaire de dailies (summary)
if ($action == 'modSummary') {
	try {
		if ($_SESSION['user']->isSupervisor() || $_SESSION['user']->isDemo()) {
			Dailies::mod_dailies_summary($projID, $commID, $comment);
			$retour['error'] = 'OK';
			$retour['message'] = 'Comment modified.';
		}
		else $retour['message'] = 'Access denied.';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Suppression d'un commentaire
if ($action == 'deleteComment') {
	try {
		Dailies::delete_dailies_comment($idComm);
		$retour['error'] = 'OK';
		$retour['message'] = 'Comment deleted from this week\'s summary.';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}


echo json_encode($retour);

?>