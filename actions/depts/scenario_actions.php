<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	if (!$_SESSION['user']->isSupervisor()) die('{"error":"error","message":"access denied."}');

	include('directories.php');

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';


if ($action == 'saveScenario') {
	if ($newScenarioTxt != '') {
		if (file_put_contents($scenarPath, stripslashes(urldecode($newScenarioTxt)))) {
			Dailies::add_dailies_entry($idProj, Dailies::GROUP_ROOT, Dailies::TYPE_SCENARIO_UPDATE, '{"idProject":"'.$idProj.'"}');
			$retour['error'] = 'OK';
			$retour['message'] = 'Scenario saved.';
		}
		else $retour['message'] = 'Unable to save scenario file!.';
	}
	else $retour['message'] = 'Empty scenario!';
}

if ($action == 'delScenario') {
	if (@unlink($scenarPath)) {
		$retour['error'] = 'OK';
		$retour['message'] = 'Scenario deleted.';
	}
	else $retour['message'] = 'No scenario file.';
}

echo json_encode($retour);

?>