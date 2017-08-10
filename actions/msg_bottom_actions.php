<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';

try {
	if ($action == 'setMessageRead') {
		$comm = new Comments($typeComm, $idComm);
		$comm->setReadBy($userID);
		$comm->save();
		$retour['error'] = 'OK';
		$retour['message'] = 'Comment set to read.';
	}
}
catch(Exception $e) { $retour['message'] = "Action error : ".$e->getMessage(); }

echo json_encode($retour);

?>