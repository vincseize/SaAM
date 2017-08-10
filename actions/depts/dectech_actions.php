<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	include('xml_fcts.php');

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';

if ($action == 'modifDecTechValue') {
	try {
		set_dectech_data ($dirShot, $categ, $type, $pos, $txt);
		$retour['error'] = 'OK';
		$retour['message'] = 'decTech modified.';
	}
	catch(Exception $e) { $retour['message'] = $e->getMessage(); }
}

echo json_encode($retour);

?>